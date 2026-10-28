<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExternalBookService
{
    private const MAX_RESULTS = 5;
    private const COVER_HOSTS = ['books.google.com', 'books.googleusercontent.com', 'covers.openlibrary.org', 'archive.org'];
    private const COVER_MAX_BYTES = 5242880;
    private const COVER_MIN_SIDE = 50;
    private const COVER_TYPES = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
        IMAGETYPE_GIF => 'gif',
    ];

    /**
     * Tra cuu Google Books roi OpenLibrary. 'offline' = true khi khong dich vu nao tra loi duoc.
     *
     * @return array{items: array<int, array<string, mixed>>, offline: bool}
     */
    public function search(string $query): array
    {
        $isbn = $this->extractIsbn($query);
        $term = $isbn ? 'isbn:' . $isbn : $query;
        $reachable = false;
        $items = [];

        $google = $this->searchGoogleBooks($term);
        if ($google !== null) {
            $reachable = true;
            $items = $google;
        }

        if (count($items) < 4) {
            $openLibrary = $this->searchOpenLibrary($term);
            if ($openLibrary !== null) {
                $reachable = true;
                $items = array_merge($items, $openLibrary);
            }
        }

        if (!$reachable) {
            return ['items' => [$this->offlineSample($query, $isbn)], 'offline' => true];
        }

        return ['items' => array_slice($this->dedupe($items), 0, self::MAX_RESULTS), 'offline' => false];
    }

    /**
     * Tai anh bia tu URL cua API ve disk public. Chi chap nhan host nam trong danh sach cho phep.
     *
     * @return array{path: ?string, error: ?string}
     */
    public function downloadCover(string $url): array
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = (string) ($parts['host'] ?? '');

        if (!in_array($scheme, ['http', 'https'], true) || !$this->isAllowedCoverHost($host)) {
            return ['path' => null, 'error' => 'Nguồn ảnh bìa không được hỗ trợ.'];
        }

        try {
            $response = Http::timeout(10)
                ->withOptions([
                    'allow_redirects' => [
                        'max' => 3,
                        'protocols' => ['http', 'https'],
                        'on_redirect' => function ($request, $redirect, $uri) {
                            if (!$this->isAllowedCoverHost($uri->getHost())) {
                                throw new \RuntimeException('Redirect to a host that is not allowed.');
                            }
                        },
                    ],
                ])
                ->get($url);
        } catch (\Throwable $e) {
            return ['path' => null, 'error' => 'Không tải được ảnh bìa từ nguồn dữ liệu.'];
        }

        $body = $response->successful() ? $response->body() : '';
        $info = $body !== '' && strlen($body) <= self::COVER_MAX_BYTES ? @getimagesizefromstring($body) : false;

        if (!$info || !isset(self::COVER_TYPES[$info[2]]) || $info[0] < self::COVER_MIN_SIDE || $info[1] < self::COVER_MIN_SIDE) {
            return ['path' => null, 'error' => 'Nguồn dữ liệu không có ảnh bìa hợp lệ cho sách này.'];
        }

        Storage::disk('public')->makeDirectory('ebooks/covers');
        $relPath = 'ebooks/covers/api_' . time() . '_' . Str::lower(Str::random(5)) . '.' . self::COVER_TYPES[$info[2]];
        Storage::disk('public')->put($relPath, $body);

        return ['path' => $relPath, 'error' => null];
    }

    public function extractIsbn(string $query): ?string
    {
        $candidate = preg_replace('/^\s*isbn[\s:\-]*/i', '', $query);
        $candidate = strtoupper((string) preg_replace('/[\s\-]+/', '', (string) $candidate));

        return preg_match('/^(\d{9}[\dX]|\d{13})$/', $candidate) ? $candidate : null;
    }

    protected function searchGoogleBooks(string $term): ?array
    {
        $params = [
            'q' => $term,
            'maxResults' => self::MAX_RESULTS,
            'fields' => 'items(volumeInfo(title,authors,description,pageCount,publishedDate,imageLinks))',
        ];

        if ($key = config('services.google_books.key')) {
            $params['key'] = $key;
        }

        try {
            $response = Http::timeout(8)->get('https://www.googleapis.com/books/v1/volumes', $params);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $items = [];
        foreach ($response->json('items', []) as $item) {
            $info = $item['volumeInfo'] ?? [];
            $title = trim((string) ($info['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $description = $this->cleanText((string) ($info['description'] ?? ''));
            $pages = (int) ($info['pageCount'] ?? 0);

            $items[] = [
                'source' => 'Google Books API',
                'title' => $title,
                'author' => $this->firstAuthor($info['authors'] ?? []),
                'description' => $description !== ''
                    ? Str::limit($description, 1200)
                    : "Tác phẩm \"{$title}\". Thông tin được lấy từ Google Books API.",
                'pages' => $pages > 0 ? $pages : null,
                'cover_url' => $this->googleCoverUrl($info['imageLinks'] ?? []),
                'published_year' => $this->extractYear($info['publishedDate'] ?? null),
            ];
        }

        return $items;
    }

    protected function searchOpenLibrary(string $term): ?array
    {
        try {
            $response = Http::timeout(8)->get('https://openlibrary.org/search.json', [
                'q' => $term,
                'limit' => self::MAX_RESULTS,
                'fields' => 'title,author_name,number_of_pages_median,cover_i,first_publish_year,first_sentence',
            ]);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $items = [];
        foreach ($response->json('docs', []) as $doc) {
            $title = trim((string) ($doc['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $author = $this->firstAuthor($doc['author_name'] ?? []);
            $year = $this->extractYear($doc['first_publish_year'] ?? null);
            $sentence = is_array($doc['first_sentence'] ?? null) ? ($doc['first_sentence'][0] ?? '') : ($doc['first_sentence'] ?? '');
            $sentence = $this->cleanText((string) $sentence);
            $pages = (int) ($doc['number_of_pages_median'] ?? 0);

            $summary = "Tác phẩm \"{$title}\""
                . ($author ? " của tác giả {$author}" : '')
                . ($year ? " (xuất bản lần đầu: {$year})" : '')
                . '. Thông tin được lấy từ OpenLibrary API.';

            $items[] = [
                'source' => 'OpenLibrary API',
                'title' => $title,
                'author' => $author,
                'description' => $sentence !== '' ? $sentence . "\n\n" . $summary : $summary,
                'pages' => $pages > 0 ? $pages : null,
                'cover_url' => !empty($doc['cover_i'])
                    ? 'https://covers.openlibrary.org/b/id/' . (int) $doc['cover_i'] . '-L.jpg?default=false'
                    : null,
                'published_year' => $year,
            ];
        }

        return $items;
    }

    protected function offlineSample(string $query, ?string $isbn): array
    {
        return [
            'source' => 'Dữ liệu mẫu (ngoại tuyến)',
            'title' => $isbn ? 'Sách ISBN ' . $isbn : Str::title($query),
            'author' => null,
            'description' => 'Bản ghi mẫu do không kết nối được Google Books và OpenLibrary. Hãy chỉnh lại tiêu đề và mô tả trước khi gửi bản thảo.',
            'pages' => null,
            'cover_url' => null,
            'published_year' => null,
            'is_sample' => true,
        ];
    }

    protected function googleCoverUrl(array $imageLinks): ?string
    {
        foreach (['extraLarge', 'large', 'medium', 'small', 'thumbnail', 'smallThumbnail'] as $size) {
            if (!empty($imageLinks[$size])) {
                $url = str_replace(['http://', '&edge=curl'], ['https://', ''], (string) $imageLinks[$size]);

                return str_replace('zoom=1', 'zoom=2', $url);
            }
        }

        return null;
    }

    protected function isAllowedCoverHost(string $host): bool
    {
        $host = strtolower($host);

        foreach (self::COVER_HOSTS as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
                return true;
            }
        }

        return false;
    }

    protected function firstAuthor(mixed $authors): ?string
    {
        $name = is_array($authors) ? trim((string) ($authors[0] ?? '')) : '';

        return $name !== '' ? Str::limit($name, 255, '') : null;
    }

    protected function extractYear(mixed $value): ?string
    {
        return preg_match('/^\d{4}/', (string) $value, $matches) ? $matches[0] : null;
    }

    protected function cleanText(string $text): string
    {
        $text = preg_replace('/<\s*(br|\/p)\s*\/?\s*>/i', "\n", $text);

        return trim(html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    protected function dedupe(array $items): array
    {
        $seen = [];

        return array_values(array_filter($items, function (array $item) use (&$seen) {
            $key = Str::lower($item['title']) . '|' . Str::lower((string) $item['author']);
            if (isset($seen[$key])) {
                return false;
            }
            $seen[$key] = true;

            return true;
        }));
    }
}
