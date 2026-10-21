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

        $query = Ebook::with(['category', 'author', 'uploader'])->withCount('reviews');

        if ($status && in_array($status, ['published', 'pending', 'draft'], true)) {
            $query->where('status', $status);
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
            'total_users' => User::count(),
            'total_categories' => Category::count(),
        ];

        return view('admin.pending', compact('ebooks', 'publishedEbooks', 'stats'));
    }

    public function approve(int $id): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $this->ebookService->approveEbook($id);

        return redirect()->back()->with('success', 'Đã phê duyệt và xuất bản sách lên thư viện.');
    }

    public function reject(int $id): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $this->ebookService->rejectEbook($id);

        return redirect()->back()->with('success', 'Đã từ chối phát hành tác phẩm.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $this->ebookService->deleteEbook($id);

        return redirect()->back()->with('success', 'Đã xóa sách điện tử khỏi hệ thống.');
    }
}
