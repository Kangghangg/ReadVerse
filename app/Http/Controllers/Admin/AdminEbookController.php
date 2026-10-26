<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Category;
use App\Models\Ebook;
use App\Models\User;
use App\Services\EbookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEbookController extends Controller
{
    public function __construct(
        protected EbookService $ebookService
    ) {}

    public function index(Request $request): View
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $search = $request->query('search');
        $status = $request->query('status');
        $categoryId = $request->query('category') ? (int) $request->query('category') : null;

        if ($status === 'trashed') {
            $query = Ebook::onlyTrashed()->with(['category', 'author', 'uploader'])->withCount('reviews');
        } else {
            $query = Ebook::with(['category', 'author', 'uploader'])->withCount('reviews');
            if ($status && in_array($status, ['published', 'pending', 'draft'], true)) {
                $query->where('status', $status);
            }
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search !== null && trim($search) !== '') {
            $keyword = trim($search);
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhereHas('author', fn ($aq) => $aq->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('uploader', fn ($uq) => $uq->where('name', 'like', "%{$keyword}%"));
            });
        }

        $ebooks = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        $stats = [
            'total_ebooks' => Ebook::count(),
            'published_ebooks' => Ebook::where('status', 'published')->count(),
            'pending_ebooks' => Ebook::where('status', 'pending')->count(),
            'draft_ebooks' => Ebook::where('status', 'draft')->count(),
            'trashed_ebooks' => Ebook::onlyTrashed()->count(),
            'total_users' => User::count(),
            'total_categories' => Category::count(),
            'total_authors' => Author::count(),
        ];

        return view('admin.ebooks', compact('ebooks', 'categories', 'search', 'status', 'categoryId', 'stats'));
    }

    public function pending(): View
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $ebooks = $this->ebookService->getPendingEbooks();
        $publishedEbooks = Ebook::with(['category', 'author'])
            ->where('status', 'published')
            ->latest()
            ->limit(15)
            ->get();

        $stats = [
            'total_ebooks' => Ebook::where('status', 'published')->count(),
            'pending_ebooks' => $ebooks->count(),
            'trashed_ebooks' => Ebook::onlyTrashed()->count(),
            'total_users' => User::count(),
            'total_categories' => Category::count(),
        ];

        return view('admin.pending', compact('ebooks', 'publishedEbooks', 'stats'));
    }

    public function approve(int $id): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $ebook = $this->ebookService->approveEbook($id);

        return redirect()->back()->with('success', "Đã phê duyệt và xuất bản tác phẩm \"{$ebook->title}\" lên thư viện.");
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
            'rejection_note' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'rejection_reason.required' => 'Vui lòng chọn hoặc nhập lý do từ chối chính.',
            'rejection_note.required' => 'Vui lòng nhập nội dung góp ý chi tiết để gửi lại cho tác giả.',
            'rejection_note.min' => 'Nội dung phản hồi cần ít nhất 5 ký tự.',
        ]);

        $ebook = $this->ebookService->rejectEbook(
            $id,
            trim($validated['rejection_reason']),
            trim($validated['rejection_note'])
        );

        return redirect()->back()->with('success', "Đã từ chối bản thảo \"{$ebook->title}\" và gửi lý do phản hồi về cho tác giả.");
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $this->ebookService->deleteEbook($id);

        return redirect()->back()->with('success', 'Đã chuyển tác phẩm vào thùng rác.');
    }

    public function restore(int $id): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $ebook = $this->ebookService->restoreEbook($id);

        return redirect()->back()->with('success', "Đã khôi phục tác phẩm \"{$ebook->title}\" từ Thùng rác thành công.");
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $this->ebookService->forceDeleteEbook($id);

        return redirect()->back()->with('success', 'Đã xóa vĩnh viễn tác phẩm và các tệp đính kèm khỏi hệ thống.');
    }
}
