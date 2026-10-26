<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Ebook;
use App\Models\Order;
use App\Models\Review;
use App\Services\EbookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EbookReaderController extends Controller
{
    public function __construct(
        protected EbookService $ebookService
    ) {}

    public function read(Request $request, string $slug): View|RedirectResponse
    {
        $ebook = $this->ebookService->getEbookBySlug($slug);
        $user = auth()->user();

        if (!$ebook->isUnlockedBy($user)) {
            if ($user) {
                CartItem::firstOrCreate([
                    'user_id' => $user->id,
                    'ebook_id' => $ebook->id,
                ]);
                return redirect()
                    ->route('cart.index')
                    ->with('success', 'Đã thêm "' . $ebook->title . '" vào giỏ hàng. Vui lòng hoàn tất thanh toán để đọc trọn vẹn tác phẩm.');
            }
            return redirect()
                ->route('ebooks.show', $ebook->slug)
                ->with('error', 'Vui lòng đăng nhập và thêm sách vào giỏ hàng để mở khóa nội dung đọc.');
        }

        $ebook->increment('view_count');
        $history = null;

        if ($user) {
            $history = $this->ebookService->getOrCreateReadingHistory($user, $ebook);
        }

        $canReadFull = true;
        $maxAllowedPages = 0;
        $requestedPage = $request->query('page') ? (int) $request->query('page') : ($history->last_page ?? 1);
        $initialPage = max(1, $requestedPage);

        return view('reader.read', compact(
            'ebook',
            'history',
            'canReadFull',
            'maxAllowedPages',
            'initialPage'
        ));
    }

    public function unlock(Request $request, int $ebookId): JsonResponse|RedirectResponse
    {
        $ebook = Ebook::findOrFail($ebookId);
        $user = $request->user();

        $order = Order::firstOrCreate(
            [
                'user_id' => $user->id,
                'ebook_id' => $ebook->id,
                'status' => 'completed',
            ],
            [
                'order_code' => 'RV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'amount' => $ebook->price,
                'payment_method' => $request->input('payment_method', 'qr_transfer'),
            ]
        );

        $user->unlockedEbooks()->syncWithoutDetaching([$ebook->id]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thanh toán thành công! Sách đã được mở khóa trong tài khoản của bạn.',
                'order_code' => $order->order_code,
                'read_url' => route('ebooks.read', $ebook->slug),
            ]);
        }

        return redirect()
            ->route('ebooks.show', $ebook->slug)
            ->with('success', 'Thanh toán thành công! Bạn có thể bấm Đọc ngay để thưởng thức cuốn sách.');
    }

    public function storeReview(Request $request, int $ebookId): JsonResponse|RedirectResponse
    {
        $ebook = Ebook::findOrFail($ebookId);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'comment.required' => 'Vui lòng nhập nội dung nhận xét.',
            'comment.min' => 'Nội dung nhận xét cần tối thiểu 3 ký tự.',
        ]);

        $user = $request->user();

        $review = Review::updateOrCreate(
            [
                'user_id' => $user->id,
                'ebook_id' => $ebook->id,
            ],
            [
                'rating' => (int) $validated['rating'],
                'comment' => trim($validated['comment']),
            ]
        );

        $review->load('user');

        $avgRating = (float) Review::where('ebook_id', $ebook->id)->avg('rating');
        $reviewsCount = (int) Review::where('ebook_id', $ebook->id)->count();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã gửi đánh giá thành công!',
                'review' => [
                    'id' => $review->id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'rating' => (int) $review->rating,
                    'comment' => $review->comment,
                    'updated_at' => $review->updated_at->diffForHumans(),
                    'delete_url' => route('reviews.destroy', $review->id),
                ],
                'stats' => [
                    'avg_rating' => number_format($avgRating, 1),
                    'reviews_count' => $reviewsCount,
                ],
            ]);
        }

        return back()->with('success', 'Đã lưu đánh giá của bạn.');
    }

    public function toggleBookmark(Request $request, ?int $ebookId = null): JsonResponse
    {
        $resolvedEbookId = $ebookId ?? (int) $request->input('ebook_id');
        abort_if($resolvedEbookId <= 0, 422, 'ID sách không hợp lệ.');

        $user = $request->user();
        $bookmarked = $this->ebookService->toggleBookmark($user, $resolvedEbookId);
        $favoriteCount = $user->bookmarks()->where('status', 'published')->count();

        return response()->json([
            'success' => true,
            'ebook_id' => $resolvedEbookId,
            'bookmarked' => $bookmarked,
            'favorite_count' => $favoriteCount,
        ]);
    }

    public function updateProgress(Request $request, int $ebookId): JsonResponse
    {
        $validated = $request->validate([
            'last_page' => ['required', 'integer', 'min:1'],
            'progress_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $this->ebookService->updateReadingProgress(
            $request->user(),
            $ebookId,
            $validated['last_page'],
            $validated['progress_percentage']
        );

        return response()->json(['success' => true]);
    }

    public function updateReview(Request $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $review->update($validated);

        return back()->with('success', 'Đã cập nhật đánh giá của bạn.');
    }

    public function destroyReview(Request $request, Review $review): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $review);
        $ebookId = $review->ebook_id;
        $review->delete();

        if ($request->expectsJson()) {
            $avgRating = (float) Review::where('ebook_id', $ebookId)->avg('rating');
            $reviewsCount = (int) Review::where('ebook_id', $ebookId)->count();

            return response()->json([
                'success' => true,
                'stats' => [
                    'avg_rating' => number_format($avgRating, 1),
                    'reviews_count' => $reviewsCount,
                ],
            ]);
        }

        return back()->with('success', 'Đã xóa đánh giá.');
    }
}
