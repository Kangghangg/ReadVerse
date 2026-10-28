<?php

namespace Tests\Feature\Author;

use App\Models\Author;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookApiLookupTest extends TestCase
{
    use RefreshDatabase;

    private const GOOGLE = 'www.googleapis.com/*';
    private const OPEN_LIBRARY = 'openlibrary.org/*';
    private const COVERS = 'covers.openlibrary.org/*';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function author(): User
    {
        return User::factory()->create(['role' => 'author']);
    }

    private function png(int $width, int $height): string
    {
        $raw = str_repeat("\x00" . str_repeat("\x80\x80\x80", $width), $height);
        $chunk = fn (string $type, string $data) => pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));

        return "\x89PNG\r\n\x1a\n"
            . $chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0))
            . $chunk('IDAT', gzcompress($raw))
            . $chunk('IEND', '');
    }

    private function googleBook(array $overrides = []): array
    {
        return array_replace_recursive([
            'volumeInfo' => [
                'title' => 'Clean Code',
                'authors' => ['Robert C. Martin'],
                'description' => '<p>Sách về <b>mã sạch</b>.</p><br>Dành cho lập trình viên.',
                'pageCount' => 464,
                'publishedDate' => '2008-08-01',
                'imageLinks' => [
                    'thumbnail' => 'http://books.google.com/books/content?id=abc&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api',
                ],
            ],
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/author/ebooks/api-lookup?q=clean')->assertRedirect('/login');
    }

    public function test_reader_cannot_use_lookup_or_prepare(): void
    {
        $reader = User::factory()->create(['role' => 'reader']);

        $this->actingAs($reader)->getJson('/author/ebooks/api-lookup?q=clean')->assertForbidden();
        $this->actingAs($reader)->postJson('/author/ebooks/api-prepare', ['author' => 'A'])->assertForbidden();
    }

    public function test_empty_and_too_long_queries_are_rejected(): void
    {
        $this->actingAs($this->author())->getJson('/author/ebooks/api-lookup?q=')->assertStatus(422);
        $this->actingAs($this->author())->getJson('/author/ebooks/api-lookup?q=' . str_repeat('a', 201))->assertStatus(422);
    }

    public function test_google_books_result_is_mapped(): void
    {
        Http::fake([
            self::GOOGLE => Http::response(['items' => [$this->googleBook()]]),
            self::OPEN_LIBRARY => Http::response(['docs' => []]),
        ]);

        $response = $this->actingAs($this->author())->getJson('/author/ebooks/api-lookup?q=clean code');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('results.0.source', 'Google Books API')
            ->assertJsonPath('results.0.title', 'Clean Code')
            ->assertJsonPath('results.0.author', 'Robert C. Martin')
            ->assertJsonPath('results.0.pages', 464)
            ->assertJsonPath('results.0.published_year', '2008')
            ->assertJsonPath('results.0.cover_url', 'https://books.google.com/books/content?id=abc&printsec=frontcover&img=1&zoom=2&source=gbs_api')
            ->assertJsonMissingPath('offline');

        $description = $response->json('results.0.description');
        $this->assertStringNotContainsString('<', $description);
        $this->assertStringContainsString('mã sạch', $description);
    }

    public function test_isbn_query_is_sent_with_isbn_prefix(): void
    {
        Http::fake([
            self::GOOGLE => Http::response(['items' => [$this->googleBook()]]),
            self::OPEN_LIBRARY => Http::response(['docs' => []]),
        ]);

        $this->actingAs($this->author())->getJson('/author/ebooks/api-lookup?q=' . urlencode('ISBN 978-0-13-235088-4'))->assertOk();

        Http::assertSent(fn ($request) => str_contains($request->url(), 'googleapis.com')
            && ($request->data()['q'] ?? null) === 'isbn:9780132350884');
        Http::assertSent(fn ($request) => str_contains($request->url(), 'openlibrary.org')
            && ($request->data()['q'] ?? null) === 'isbn:9780132350884');
    }

    public function test_open_library_is_used_when_google_books_fails(): void
    {
        Http::fake([
            self::GOOGLE => Http::response(['error' => ['code' => 429]], 429),
            self::OPEN_LIBRARY => Http::response(['docs' => [[
                'title' => 'Clean Code',
                'author_name' => ['Robert C. Martin'],
                'cover_i' => 8065615,
                'first_publish_year' => 2008,
                'number_of_pages_median' => 444,
            ]]]),
        ]);

        $this->actingAs($this->author())->getJson('/author/ebooks/api-lookup?q=clean code')
            ->assertOk()
            ->assertJsonPath('results.0.source', 'OpenLibrary API')
            ->assertJsonPath('results.0.pages', 444)
            ->assertJsonPath('results.0.published_year', '2008')
            ->assertJsonPath('results.0.cover_url', 'https://covers.openlibrary.org/b/id/8065615-L.jpg?default=false')
            ->assertJsonMissingPath('offline');
    }

    public function test_missing_pages_and_year_are_not_made_up(): void
    {
        Http::fake([
            self::GOOGLE => Http::response(['items' => [['volumeInfo' => ['title' => 'Sách không rõ thông tin']]]]),
            self::OPEN_LIBRARY => Http::response(['docs' => []]),
        ]);

        $this->actingAs($this->author())->getJson('/author/ebooks/api-lookup?q=abc')
            ->assertOk()
            ->assertJsonPath('results.0.pages', null)
            ->assertJsonPath('results.0.published_year', null)
            ->assertJsonPath('results.0.author', null)
            ->assertJsonPath('results.0.cover_url', null);
    }

    public function test_sample_record_is_flagged_when_both_services_are_unreachable(): void
    {
        Http::fake([
            self::GOOGLE => Http::response('', 500),
            self::OPEN_LIBRARY => Http::response('', 503),
        ]);

        $this->actingAs($this->author())->getJson('/author/ebooks/api-lookup?q=clean code')
            ->assertOk()
            ->assertJsonPath('offline', true)
            ->assertJsonPath('results.0.is_sample', true)
            ->assertJsonPath('results.0.title', 'Clean Code')
            ->assertJsonPath('results.0.author', null);
    }

    public function test_not_found_is_reported_when_services_answer_without_results(): void
    {
        Http::fake([
            self::GOOGLE => Http::response(['totalItems' => 0]),
            self::OPEN_LIBRARY => Http::response(['docs' => []]),
        ]);

        $this->actingAs($this->author())->getJson('/author/ebooks/api-lookup?q=khong-co-sach-nay')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_prepare_creates_author_and_stores_cover(): void
    {
        Http::fake([self::COVERS => Http::response($this->png(80, 120), 200, ['Content-Type' => 'image/png'])]);

        $response = $this->actingAs($this->author())->postJson('/author/ebooks/api-prepare', [
            'author' => 'Robert C. Martin',
            'cover_url' => 'https://covers.openlibrary.org/b/id/8065615-L.jpg?default=false',
        ]);

        $response->assertOk()->assertJsonPath('success', true)->assertJsonPath('cover_error', null);

        $path = $response->json('pre_uploaded_cover');
        $this->assertMatchesRegularExpression('#^ebooks/covers/api_[A-Za-z0-9_]+\.png$#', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseHas('authors', ['name' => 'Robert C. Martin']);
        $this->assertSame(Author::where('name', 'Robert C. Martin')->value('id'), $response->json('author_id'));
    }

    public function test_prepare_reuses_existing_author(): void
    {
        Author::create(['name' => 'Robert C. Martin', 'slug' => 'robert-c-martin']);

        $this->actingAs($this->author())->postJson('/author/ebooks/api-prepare', ['author' => 'Robert C. Martin'])
            ->assertOk()
            ->assertJsonPath('pre_uploaded_cover', null);

        $this->assertDatabaseCount('authors', 1);
    }

    public function test_prepare_without_author_does_not_create_one(): void
    {
        $this->actingAs($this->author())->postJson('/author/ebooks/api-prepare', ['author' => '', 'cover_url' => ''])
            ->assertOk()
            ->assertJsonPath('author_id', null);

        $this->assertDatabaseCount('authors', 0);
    }

    public function test_prepare_refuses_cover_urls_from_unknown_hosts(): void
    {
        Http::fake();

        foreach (['http://127.0.0.1/secret.png', 'https://example.com/cover.png', 'https://evilarchive.org/cover.png', 'file:///etc/passwd'] as $url) {
            $this->actingAs($this->author())->postJson('/author/ebooks/api-prepare', ['author' => 'A', 'cover_url' => $url])
                ->assertOk()
                ->assertJsonPath('pre_uploaded_cover', null)
                ->assertJsonPath('cover_error', 'Nguồn ảnh bìa không được hỗ trợ.');
        }

        Http::assertNothingSent();
    }

    public function test_prepare_refuses_files_that_are_not_images(): void
    {
        Http::fake([self::COVERS => Http::response('<html>' . str_repeat('x', 3000) . '</html>', 200)]);

        $this->actingAs($this->author())->postJson('/author/ebooks/api-prepare', [
            'author' => 'A',
            'cover_url' => 'https://covers.openlibrary.org/b/id/1-L.jpg',
        ])->assertOk()->assertJsonPath('pre_uploaded_cover', null);

        $this->assertSame([], Storage::disk('public')->allFiles('ebooks/covers'));
    }

    public function test_prepare_refuses_placeholder_sized_images(): void
    {
        Http::fake([self::COVERS => Http::response($this->png(1, 1), 200)]);

        $this->actingAs($this->author())->postJson('/author/ebooks/api-prepare', [
            'author' => 'A',
            'cover_url' => 'https://covers.openlibrary.org/b/id/1-L.jpg',
        ])->assertOk()->assertJsonPath('pre_uploaded_cover', null);

        $this->assertSame([], Storage::disk('public')->allFiles('ebooks/covers'));
    }

    public function test_store_rejects_pre_uploaded_cover_outside_covers_folder(): void
    {
        $category = Category::create(['name' => 'Công nghệ', 'slug' => 'cong-nghe']);

        $this->actingAs($this->author())->post('/author/ebooks', [
            'title' => 'Clean Code',
            'description' => 'Sách về mã sạch.',
            'category_id' => $category->id,
            'pre_uploaded_file' => 'ebooks/files/upload_abc.pdf',
            'pre_uploaded_cover' => 'ebooks/files/someone-elses-book.pdf',
        ])->assertSessionHasErrors('pre_uploaded_cover');
    }

    public function test_create_page_loads_lookup_script_for_authors(): void
    {
        $this->actingAs($this->author())->get('/author/ebooks/create')
            ->assertOk()
            ->assertSee('js/readverse-book-lookup.js', false)
            ->assertSee('data-api-lookup', false);
    }
}
