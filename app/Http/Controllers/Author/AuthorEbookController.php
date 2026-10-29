<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEbookRequest;
use App\Http\Requests\UpdateEbookRequest;
use App\Models\Author;
use App\Models\Ebook;
use App\Services\EbookService;
use App\Services\ExternalBookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthorEbookController extends Controller
{
    public function __construct(
        protected EbookService $ebookService,
        protected ExternalBookService $externalBookService
    ) {}

    public function index(): View
    {
        $this->authorize('create', Ebook::class);
        $ebooks = $this->ebookService->getAuthorEbooks(auth()->id());
        return view('author.dashboard', compact('ebooks'));
    }

    public function create(): View
    {
        $this->authorize('create', Ebook::class);
        $categories = $this->ebookService->getCategories();
        $authors = $this->ebookService->getAuthors();
        return view('author.create', compact('categories', 'authors'));
    }

    public function lookupExternalApi(Request $request): JsonResponse
    {
        $this->authorize('create', Ebook::class);
        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập tên sách hoặc mã ISBN để tìm kiếm qua API.',
            ], 422);
        }

        if (mb_strlen($query) > 200) {
            return response()->json([
                'success' => false,
                'message' => 'Từ khóa tìm kiếm quá dài (tối đa 200 ký tự).',
            ], 422);
        }

        $result = $this->externalBookService->search($query);

        if ($result['offline']) {
            return response()->json([
                'success' => true,
                'offline' => true,
                'message' => 'Không kết nối được Google Books và OpenLibrary, đang hiển thị bản ghi mẫu để bạn tự chỉnh sửa.',
                'results' => $result['items'],
            ]);
        }

        if (empty($result['items'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sách phù hợp. Hãy thử tên sách đầy đủ hoặc mã ISBN.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'results' => $result['items'],
        ]);
    }

    public function prepareApiSelection(Request $request): JsonResponse
    {
        $this->authorize('create', Ebook::class);

        $validated = $request->validate([
            'author' => ['nullable', 'string', 'max:255'],
            'cover_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $authorName = trim((string) ($validated['author'] ?? ''));
        $author = null;

        if ($authorName !== '') {
            $author = Author::firstOrCreate(
                ['name' => $authorName],
                [
                    'slug' => Str::slug($authorName) . '-' . Str::lower(Str::random(4)),
                    'bio' => 'Tác giả được đồng bộ từ cổng dữ liệu OpenLibrary / Google Books API.',
                ]
            );
        }

        $coverPath = null;
        $coverError = null;
        $coverUrl = trim((string) ($validated['cover_url'] ?? ''));

        if ($coverUrl !== '') {
            $cover = $this->externalBookService->downloadCover($coverUrl);
            $coverPath = $cover['path'];
            $coverError = $cover['error'];
        }

        return response()->json([
            'success' => true,
            'author_id' => $author?->id,
            'author_name' => $author?->name,
            'pre_uploaded_cover' => $coverPath,
            'cover_preview_url' => $coverPath ? asset('storage/' . $coverPath) : null,
            'cover_error' => $coverError,
        ]);
    }

    public function uploadChunk(Request $request): JsonResponse
    {
        $this->authorize('create', Ebook::class);

        $validated = $request->validate([
            'upload_id' => ['required', 'alpha_dash', 'max:100'],
            'chunk_index' => ['required', 'integer', 'min:0'],
            'total_chunks' => ['required', 'integer', 'min:1'],
            'ext' => ['required', 'string', 'in:pdf,epub,jpg,jpeg,png,webp,svg'],
            'target_type' => ['nullable', 'string', 'in:ebook,cover'],
            'chunk_base64' => ['required', 'string'],
        ]);

        $folder = ($validated['target_type'] ?? 'ebook') === 'cover' ? 'ebooks/covers' : 'ebooks/files';
        Storage::disk('public')->makeDirectory($folder);

        $relPath = $folder . '/upload_' . $validated['upload_id'] . '.' . $validated['ext'];
        $absPath = Storage::disk('public')->path($relPath);

        $binary = base64_decode($validated['chunk_base64'], true);
        if ($binary === false) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu mảnh tải lên không hợp lệ.'], 422);
        }

        if ((int) $validated['chunk_index'] === 0 && is_file($absPath)) {
            @unlink($absPath);
        }

        file_put_contents($absPath, $binary, FILE_APPEND | LOCK_EX);

        $completed = ((int) $validated['chunk_index'] + 1) >= (int) $validated['total_chunks'];

        return response()->json([
            'success' => true,
            'completed' => $completed,
            'relative_path' => $relPath,
        ]);
    }

    public function store(StoreEbookRequest $request): RedirectResponse
    {
        $this->ebookService->createEbook(
            $request->validated(),
            $request->file('cover_image'),
            $request->file('file_path'),
            $request->user()->id
        );

        $msg = $request->user()->isAdmin()
            ? 'Đã đăng tải và xuất bản sách trực tiếp lên thư viện.'
            : 'Bản thảo đã được gửi lên hệ thống và đang chờ phê duyệt.';

        $targetRoute = $request->user()->isAdmin() ? 'admin.ebooks.index' : 'author.ebooks.index';

        return redirect()->route($targetRoute)
            ->with('success', $msg);
    }

    public function edit(int $id): View
    {
        $ebook = $this->ebookService->getEbookById($id);
        $this->authorize('update', $ebook);

        $categories = $this->ebookService->getCategories();
        $authors = $this->ebookService->getAuthors();

        return view('author.create', compact('ebook', 'categories', 'authors'));
    }

    public function update(UpdateEbookRequest $request, int $id): RedirectResponse
    {
        $ebook = $this->ebookService->getEbookById($id);
        $this->authorize('update', $ebook);

        $wasDraft = $ebook->status === 'draft';

        $this->ebookService->updateEbook(
            $id,
            $request->validated(),
            $request->file('cover_image'),
            $request->file('file_path')
        );

        $targetRoute = $request->user()->isAdmin() ? 'admin.ebooks.index' : 'author.ebooks.index';
        $msg = ($wasDraft && !$request->user()->isAdmin())
            ? 'Đã cập nhật bản thảo và gửi lại yêu cầu phê duyệt cho Quản trị viên.'
            : 'Đã cập nhật thông tin sách điện tử thành công.';

        return redirect()->route($targetRoute)
            ->with('success', $msg);
    }

    public function destroy(int $id): RedirectResponse
    {
        $ebook = $this->ebookService->getEbookById($id);
        $this->authorize('delete', $ebook);

        $this->ebookService->deleteEbook($id);

        return redirect()->route('author.ebooks.index')
            ->with('success', 'Đã chuyển sách điện tử vào thùng rác.');
    }
}
