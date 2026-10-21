<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEbookRequest;
use App\Http\Requests\UpdateEbookRequest;
use App\Models\Ebook;
use App\Services\EbookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AuthorEbookController extends Controller
{
    public function __construct(
        protected EbookService $ebookService
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

        return redirect()->route('author.ebooks.index')
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

        $this->ebookService->updateEbook(
            $id,
            $request->validated(),
            $request->file('cover_image'),
            $request->file('file_path')
        );

        return redirect()->route('author.ebooks.index')
            ->with('success', 'Đã cập nhật thông tin sách điện tử thành công.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $ebook = $this->ebookService->getEbookById($id);
        $this->authorize('delete', $ebook);

        $this->ebookService->deleteEbook($id);

        return redirect()->route('author.ebooks.index')
            ->with('success', 'Đã xoá sách điện tử thành công.');
    }
}
