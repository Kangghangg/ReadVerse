<?php

namespace App\Repositories\Eloquent;

use App\Models\Ebook;
use App\Repositories\Contracts\EbookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EbookRepository implements EbookRepositoryInterface
{
    public function getPaginatedPublished(
        int $perPage = 12,
        ?int $categoryId = null,
        ?string $search = null,
        ?int $authorId = null,
        ?string $sort = 'latest',
        ?string $priceSort = null,
        ?int $bookmarkedByUserId = null
    ): LengthAwarePaginator {
        $query = Ebook::with(['category', 'author', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', 'published');

        if ($bookmarkedByUserId) {
            $query->whereHas('bookmarkedByUsers', function ($bookmarkQuery) use ($bookmarkedByUserId) {
                $bookmarkQuery->where('users.id', $bookmarkedByUserId);
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($authorId) {
            $query->where('author_id', $authorId);
        }

        if ($search !== null && trim($search) !== '') {
            $keyword = trim($search);
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhereHas('author', function ($authorQuery) use ($keyword) {
                        $authorQuery->where('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($keyword) {
                        $categoryQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($priceSort === 'free') {
            $query->where('price', 0);
        } elseif ($priceSort === 'price_asc' || $sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($priceSort === 'price_desc' || $sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        }

        if ($sort === 'views_desc') {
            $query->orderBy('view_count', 'desc');
        } else {
            $query->latest();
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function searchPublished(string $keyword, int $limit = 8): Collection
    {
        $cleanKeyword = trim($keyword);

        if ($cleanKeyword === '') {
            return new Collection();
        }

        return Ebook::with(['category', 'author'])
            ->where('status', 'published')
            ->where(function ($query) use ($cleanKeyword) {
                $query->where('title', 'like', "%{$cleanKeyword}%")
                    ->orWhereHas('author', function ($authorQuery) use ($cleanKeyword) {
                        $authorQuery->where('name', 'like', "%{$cleanKeyword}%");
                    });
            })
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get();
    }

    public function findPublishedBySlug(string $slug): Ebook
    {
        return Ebook::with(['category', 'author', 'uploader', 'reviews.user'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function findById(int $id): Ebook
    {
        return Ebook::with(['category', 'author', 'uploader'])->findOrFail($id);
    }

    public function getByUploader(int $userId): Collection
    {
        return Ebook::with(['category', 'author', 'reviews'])
            ->withCount('reviews')
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function getPending(): Collection
    {
        return Ebook::with(['category', 'author', 'uploader'])
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function create(array $data): Ebook
    {
        return Ebook::create($data);
    }

    public function update(Ebook $ebook, array $data): Ebook
    {
        $ebook->update($data);
        return $ebook->fresh(['category', 'author']);
    }

    public function delete(Ebook $ebook): bool
    {
        return (bool) $ebook->delete();
    }
}
