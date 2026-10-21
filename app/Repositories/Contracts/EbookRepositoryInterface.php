<?php

namespace App\Repositories\Contracts;

use App\Models\Ebook;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface EbookRepositoryInterface
{
    public function getPaginatedPublished(
        int $perPage = 12,
        ?int $categoryId = null,
        ?string $search = null,
        ?int $authorId = null,
        ?string $sort = 'latest',
        ?string $priceSort = null,
        ?int $bookmarkedByUserId = null
    ): LengthAwarePaginator;

    public function searchPublished(string $keyword, int $limit = 8): Collection;

    public function findPublishedBySlug(string $slug): Ebook;

    public function findById(int $id): Ebook;

    public function getByUploader(int $userId): Collection;

    public function getPending(): Collection;

    public function create(array $data): Ebook;

    public function update(Ebook $ebook, array $data): Ebook;

    public function delete(Ebook $ebook): bool;
}
