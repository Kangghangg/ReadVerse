<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Category;
use App\Models\Ebook;
use App\Models\ReadingHistory;
use App\Models\User;
use App\Repositories\Contracts\EbookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EbookService
{
    public function __construct(
        protected EbookRepositoryInterface $ebookRepository
    ) {}

    public function getCatalog(
        int $perPage = 12,
        ?int $categoryId = null,
        ?string $search = null,
        ?int $authorId = null,
        ?string $sort = 'latest',
        ?string $priceSort = null,
        ?int $bookmarkedByUserId = null
    ): LengthAwarePaginator {
        return $this->ebookRepository->getPaginatedPublished(
            $perPage,
            $categoryId,
            $search,
            $authorId,
            $sort,
            $priceSort,
            $bookmarkedByUserId
        );
    }

    public function searchLive(string $keyword, int $limit = 8): Collection
    {
        return $this->ebookRepository->searchPublished($keyword, $limit);
    }

    public function getCategories(): Collection
    {
        return Category::withCount(['ebooks' => function ($query) {
            $query->where('status', 'published');
        }])->orderBy('name')->get();
    }

    public function getAuthors(): Collection
    {
        return Author::withCount(['ebooks' => function ($query) {
            $query->where('status', 'published');
        }])->orderBy('name')->get();
    }

    public function getEbookBySlug(string $slug): Ebook
    {
        return $this->ebookRepository->findPublishedBySlug($slug);
    }

    public function getEbookForReading(string $slug): Ebook
    {
        $ebook = $this->ebookRepository->findPublishedBySlug($slug);
        $ebook->increment('view_count');
        return $ebook;
    }

    public function getEbookById(int $id): Ebook
    {
        return $this->ebookRepository->findById($id);
    }

    public function getAuthorEbooks(int $userId): Collection
    {
        return $this->ebookRepository->getByUploader($userId);
    }

    public function getPendingEbooks(): Collection
    {
        return $this->ebookRepository->getPending();
    }

    protected function resolveAuthorId(?int $authorId, int $userId): int
    {
        if ($authorId && Author::where('id', $authorId)->exists()) {
            return $authorId;
        }

        $user = User::find($userId);
        $authorName = $user ? $user->name : 'Tác giả ReadVerse';

        $author = Author::firstOrCreate(
            ['name' => $authorName],
            [
                'slug' => Str::slug($authorName) . '-' . Str::lower(Str::random(4)),
                'bio' => 'Tác giả trên hệ thống ReadVerse.',
            ]
        );

        return (int) $author->id;
    }

    protected function detectPdfPages(string $absolutePath): int
    {
        if (!is_file($absolutePath)) {
            return 100;
        }

        try {
            $content = @file_get_contents($absolutePath);
            if ($content !== false) {
                $count = preg_match_all("/\/Page\W/", $content, $matches);
                if ($count > 0) {
                    return $count;
                }
            }
        } catch (\Throwable $e) {
        }

        return 100;
    }

    public function createEbook(
        array $validated,
        ?UploadedFile $coverImage,
        ?UploadedFile $ebookFile,
        int $userId
    ): Ebook {
        $baseSlug = Str::slug($validated['title']);
        if ($baseSlug === '') {
            $baseSlug = 'ebook';
        }
        $slug = $baseSlug . '-' . Str::lower(Str::random(6));

        Storage::disk('public')->makeDirectory('ebooks/files');
        Storage::disk('public')->makeDirectory('ebooks/covers');

        $coverPath = null;
        if (!empty($validated['pre_uploaded_cover']) && Storage::disk('public')->exists($validated['pre_uploaded_cover'])) {
            $ext = pathinfo($validated['pre_uploaded_cover'], PATHINFO_EXTENSION) ?: 'jpg';
            $finalCover = 'ebooks/covers/' . $slug . '-cover.' . $ext;
            Storage::disk('public')->move($validated['pre_uploaded_cover'], $finalCover);
            $coverPath = $finalCover;
        } elseif ($coverImage) {
            $coverExt = strtolower($coverImage->getClientOriginalExtension()) ?: 'jpg';
            $coverPath = $coverImage->storeAs('ebooks/covers', $slug . '-cover.' . $coverExt, 'public');
        }

        $filePath = null;
        if (!empty($validated['pre_uploaded_file']) && Storage::disk('public')->exists($validated['pre_uploaded_file'])) {
            $ext = pathinfo($validated['pre_uploaded_file'], PATHINFO_EXTENSION) ?: 'pdf';
            $finalFile = 'ebooks/files/' . $slug . '.' . $ext;
            Storage::disk('public')->move($validated['pre_uploaded_file'], $finalFile);
            $filePath = $finalFile;
        } elseif ($ebookFile) {
            $fileExt = strtolower($ebookFile->getClientOriginalExtension()) ?: 'pdf';
            $filePath = $ebookFile->storeAs('ebooks/files', $slug . '.' . $fileExt, 'public');
        }

        $pages = !empty($validated['pages']) && (int) $validated['pages'] > 0
            ? (int) $validated['pages']
            : $this->detectPdfPages(Storage::disk('public')->path((string) $filePath));

        $resolvedAuthorId = $this->resolveAuthorId(
            isset($validated['author_id']) ? (int) $validated['author_id'] : null,
            $userId
        );

        $user = User::find($userId);
        $initialStatus = ($user && $user->isAdmin()) ? 'published' : 'pending';

        return $this->ebookRepository->create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'cover_image' => $coverPath,
            'file_path' => $filePath,
            'price' => $validated['price'] ?? 0.00,
            'pages' => $pages,
            'status' => $initialStatus,
            'category_id' => (int) $validated['category_id'],
            'author_id' => $resolvedAuthorId,
            'user_id' => $userId,
        ]);
    }

    public function updateEbook(
        int $id,
        array $validated,
        ?UploadedFile $coverImage = null,
        ?UploadedFile $ebookFile = null
    ): Ebook {
        $ebook = $this->ebookRepository->findById($id);

        $resolvedAuthorId = !empty($validated['author_id'])
            ? (int) $validated['author_id']
            : $ebook->author_id;

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'] ?? $ebook->price,
            'pages' => (!empty($validated['pages']) && (int) $validated['pages'] > 0) ? (int) $validated['pages'] : $ebook->pages,
            'category_id' => (int) $validated['category_id'],
            'author_id' => $resolvedAuthorId,
        ];

        if ($ebook->status === 'draft' && auth()->check() && !auth()->user()->isAdmin()) {
            $payload['status'] = 'pending';
            $payload['rejection_reason'] = null;
            $payload['rejection_note'] = null;
            $payload['rejected_at'] = null;
        }

        if (!empty($validated['pre_uploaded_cover']) && Storage::disk('public')->exists($validated['pre_uploaded_cover'])) {
            if ($ebook->cover_image && !str_starts_with($ebook->cover_image, 'http')) {
                Storage::disk('public')->delete($ebook->cover_image);
            }
            $ext = pathinfo($validated['pre_uploaded_cover'], PATHINFO_EXTENSION) ?: 'jpg';
            $finalCover = 'ebooks/covers/' . $ebook->slug . '-cover-' . Str::lower(Str::random(4)) . '.' . $ext;
            Storage::disk('public')->move($validated['pre_uploaded_cover'], $finalCover);
            $payload['cover_image'] = $finalCover;
        } elseif ($coverImage) {
            if ($ebook->cover_image && !str_starts_with($ebook->cover_image, 'http')) {
                Storage::disk('public')->delete($ebook->cover_image);
            }
            $coverExt = strtolower($coverImage->getClientOriginalExtension()) ?: 'jpg';
            $payload['cover_image'] = $coverImage->storeAs('ebooks/covers', $ebook->slug . '-cover-' . Str::lower(Str::random(4)) . '.' . $coverExt, 'public');
        }

        if (!empty($validated['pre_uploaded_file']) && Storage::disk('public')->exists($validated['pre_uploaded_file'])) {
            if ($ebook->file_path && !str_starts_with($ebook->file_path, 'http')) {
                Storage::disk('public')->delete($ebook->file_path);
            }
            $ext = pathinfo($validated['pre_uploaded_file'], PATHINFO_EXTENSION) ?: 'pdf';
            $finalFile = 'ebooks/files/' . $ebook->slug . '-' . Str::lower(Str::random(4)) . '.' . $ext;
            Storage::disk('public')->move($validated['pre_uploaded_file'], $finalFile);
            $payload['file_path'] = $finalFile;
            if (empty($validated['pages'])) {
                $payload['pages'] = $this->detectPdfPages(Storage::disk('public')->path($finalFile));
            }
        } elseif ($ebookFile) {
            if ($ebook->file_path && !str_starts_with($ebook->file_path, 'http')) {
                Storage::disk('public')->delete($ebook->file_path);
            }
            $fileExt = strtolower($ebookFile->getClientOriginalExtension()) ?: 'pdf';
            $newFilePath = $ebookFile->storeAs('ebooks/files', $ebook->slug . '-' . Str::lower(Str::random(4)) . '.' . $fileExt, 'public');
            $payload['file_path'] = $newFilePath;
            if (empty($validated['pages'])) {
                $payload['pages'] = $this->detectPdfPages(Storage::disk('public')->path($newFilePath));
            }
        }

        return $this->ebookRepository->update($ebook, $payload);
    }

    public function deleteEbook(int $id): bool
    {
        $ebook = $this->ebookRepository->findById($id);
        return $this->ebookRepository->delete($ebook);
    }

    public function restoreEbook(int $id): Ebook
    {
        $ebook = Ebook::onlyTrashed()->findOrFail($id);
        $ebook->restore();
        return $ebook;
    }

    public function forceDeleteEbook(int $id): bool
    {
        $ebook = Ebook::withTrashed()->findOrFail($id);

        if ($ebook->cover_image && !str_starts_with($ebook->cover_image, 'http')) {
            Storage::disk('public')->delete($ebook->cover_image);
        }

        if ($ebook->file_path && !str_starts_with($ebook->file_path, 'http')) {
            Storage::disk('public')->delete($ebook->file_path);
        }

        return (bool) $ebook->forceDelete();
    }

    public function approveEbook(int $id): Ebook
    {
        $ebook = $this->ebookRepository->findById($id);
        return $this->ebookRepository->update($ebook, [
            'status' => 'published',
            'rejection_reason' => null,
            'rejection_note' => null,
            'rejected_at' => null,
        ]);
    }

    public function rejectEbook(int $id, ?string $reason = null, ?string $note = null): Ebook
    {
        $ebook = $this->ebookRepository->findById($id);
        return $this->ebookRepository->update($ebook, [
            'status' => 'draft',
            'rejection_reason' => $reason ?: 'Chưa đạt tiêu chuẩn xuất bản',
            'rejection_note' => $note ?: 'Vui lòng kiểm tra và chỉnh sửa lại nội dung bản thảo trước khi gửi duyệt lại.',
            'rejected_at' => now(),
        ]);
    }

    public function toggleBookmark(User $user, int $ebookId): bool
    {
        $result = $user->bookmarks()->toggle($ebookId);
        return !empty($result['attached']);
    }

    public function attachBookmark(User $user, int $ebookId): void
    {
        $user->bookmarks()->syncWithoutDetaching([$ebookId]);
    }

    public function detachBookmark(User $user, int $ebookId): void
    {
        $user->bookmarks()->detach($ebookId);
    }

    public function getOrCreateReadingHistory(User $user, Ebook $ebook): ReadingHistory
    {
        return ReadingHistory::firstOrCreate(
            [
                'user_id' => $user->id,
                'ebook_id' => $ebook->id,
            ],
            [
                'last_page' => 1,
                'progress_percentage' => 0.00,
            ]
        );
    }

    public function updateReadingProgress(
        User $user,
        int $ebookId,
        int $lastPage,
        float $progressPercentage
    ): ReadingHistory {
        return ReadingHistory::updateOrCreate(
            [
                'user_id' => $user->id,
                'ebook_id' => $ebookId,
            ],
            [
                'last_page' => $lastPage,
                'progress_percentage' => $progressPercentage,
            ]
        );
    }
}
