<?php

use App\Http\Controllers\Admin\AdminAuthorController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminEbookController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Author\AuthorEbookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reader\CartController;
use App\Http\Controllers\Reader\EbookReaderController;
use App\Http\Controllers\Reader\HomeController;
use App\Http\Controllers\Reader\LibraryController;
use Illuminate\Support\Facades\Route;

// Khu vuc Cong khai & Trang chu Doc gia
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/live-search', [HomeController::class, 'liveSearch'])->name('ebooks.live_search');
Route::get('/api/live-search', [HomeController::class, 'liveSearch'])->name('api.live_search');
Route::get('/ebooks/{slug}', [HomeController::class, 'show'])->name('ebooks.show');
Route::get('/read/{slug}', [EbookReaderController::class, 'read'])->name('ebooks.read');

// Dashboard redirect mac dinh cua Breeze
Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->isAdmin()) {
        return redirect()->route('admin.ebooks.pending');
    }
    return redirect()->route('home');
})->middleware(['auth'])->name('dashboard');

// Khu vuc Yeu cau Dang nhap (Doc gia, Tac gia, Admin)
Route::middleware('auth')->group(function () {
    // Tu sach ca nhan & Lich su doc (Ho tro ca /my-library va /library)
    Route::get('/my-library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/library', [LibraryController::class, 'index'])->name('reader.library');

    // Gio hang & Thanh toan gop
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{ebookId}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove/{ebookId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    // Mo khoa sach, Danh gia, Dau trang, Tien do doc
    Route::post('/ebooks/{ebookId}/unlock', [EbookReaderController::class, 'unlock'])->name('ebooks.unlock');
    Route::post('/ebooks/{ebookId}/reviews', [EbookReaderController::class, 'storeReview'])->name('reviews.store');
    Route::put('/reviews/{review}', [EbookReaderController::class, 'updateReview'])->name('reviews.update');
    Route::delete('/reviews/{review}', [EbookReaderController::class, 'destroyReview'])->name('reviews.destroy');
    Route::post('/bookmarks/{ebookId?}', [EbookReaderController::class, 'toggleBookmark'])->name('bookmarks.toggle');
    Route::post('/progress/{ebookId}', [EbookReaderController::class, 'updateProgress'])->name('progress.update');

    // Ho so ca nhan (Profile cho Author & Reader)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Cong Tac gia (Author & Admin)
    Route::middleware('role:author,admin')->prefix('author')->name('author.')->group(function () {
        Route::get('/ebooks', [AuthorEbookController::class, 'index'])->name('ebooks.index');
        Route::get('/ebooks/create', [AuthorEbookController::class, 'create'])->name('ebooks.create');
        Route::get('/ebooks/api-lookup', [AuthorEbookController::class, 'lookupExternalApi'])->name('ebooks.api_lookup');
        Route::post('/ebooks/api-prepare', [AuthorEbookController::class, 'prepareApiSelection'])->name('ebooks.api_prepare');
        Route::post('/ebooks/upload-chunk', [AuthorEbookController::class, 'uploadChunk'])->name('ebooks.upload_chunk');
        Route::post('/ebooks', [AuthorEbookController::class, 'store'])->name('ebooks.store');
        Route::get('/ebooks/{id}/edit', [AuthorEbookController::class, 'edit'])->name('ebooks.edit');
        Route::match(['put', 'patch'], '/ebooks/{id}', [AuthorEbookController::class, 'update'])->name('ebooks.update');
        Route::delete('/ebooks/{id}', [AuthorEbookController::class, 'destroy'])->name('ebooks.destroy');
    });

    // Khu vuc Quan tri vien (Admin)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/pending-ebooks', [AdminEbookController::class, 'pending'])->name('ebooks.pending');
        Route::get('/ebooks', [AdminEbookController::class, 'index'])->name('ebooks.index');
        Route::match(['post', 'patch'], '/ebooks/{id}/approve', [AdminEbookController::class, 'approve'])->name('ebooks.approve');
        Route::match(['post', 'patch'], '/ebooks/{id}/reject', [AdminEbookController::class, 'reject'])->name('ebooks.reject');
        Route::patch('/ebooks/{id}/restore', [AdminEbookController::class, 'restore'])->name('ebooks.restore');
        Route::delete('/ebooks/{id}/force-delete', [AdminEbookController::class, 'forceDestroy'])->name('ebooks.force_destroy');
        Route::delete('/ebooks/{id}', [AdminEbookController::class, 'destroy'])->name('ebooks.destroy');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::match(['put', 'patch'], '/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/authors', [AdminAuthorController::class, 'index'])->name('authors.index');
        Route::post('/authors', [AdminAuthorController::class, 'store'])->name('authors.store');
        Route::match(['put', 'patch'], '/authors/{author}', [AdminAuthorController::class, 'update'])->name('authors.update');
        Route::delete('/authors/{author}', [AdminAuthorController::class, 'destroy'])->name('authors.destroy');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::match(['post', 'patch'], '/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update_role');
        Route::match(['post', 'patch'], '/users/{user}/status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle_status');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/export', [AdminOrderController::class, 'exportCsv'])->name('orders.export');
    });
});

require __DIR__.'/auth.php';
