<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Services\EbookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected EbookService $ebookService
    ) {}

    public function index(Request $request): View
    {
        $categoryId = $request->query('category') ? (int) $request->query('category') : null;
        $authorId = $request->query('author') ? (int) $request->query('author') : null;
        $search = $request->query('search');
        $sort = $request->query('sort', 'latest');
        $priceSort = $request->query('price_sort');
        $favorites = $request->boolean('favorites');

        $bookmarkedByUserId = ($favorites && auth()->check()) ? (int) auth()->id() : null;

        $ebooks = $this->ebookService
            ->getCatalog(12, $categoryId, $search, $authorId, $sort, $priceSort, $bookmarkedByUserId)
            ->appends($request->query());

        $categories = $this->ebookService->getCategories();
        $authors = $this->ebookService->getAuthors();
        $favoriteCount = auth()->check()
            ? auth()->user()->bookmarks()->where('status', 'published')->count()
            : 0;

        return view('reader.home', compact(
            'ebooks',
            'categories',
            'authors',
            'categoryId',
            'authorId',
            'search',
            'sort',
            'priceSort',
            'favorites',
            'favoriteCount'
        ));
    }

    public function show(string $slug): View
    {
        $ebook = $this->ebookService->getEbookBySlug($slug);
        return view('reader.show', compact('ebook'));
    }

    public function liveSearch(Request $request): JsonResponse
    {
        $keyword = (string) $request->query('q', '');
        $results = $this->ebookService->searchLive($keyword, 8);

        $data = $results->map(function ($ebook) {
            return [
                'id' => $ebook->id,
                'title' => $ebook->title,
                'slug' => $ebook->slug,
                'url' => route('ebooks.show', $ebook->slug),
                'cover_url' => $ebook->cover_url,
                'price' => number_format((float) $ebook->price, 0, ',', '.'),
                'author' => $ebook->author?->name ?? 'ReadVerse',
                'category' => $ebook->category?->name ?? 'Tổng hợp',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
