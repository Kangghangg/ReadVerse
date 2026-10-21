<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reader\HomeController;
use App\Http\Controllers\Reader\EbookReaderController;
use App\Http\Controllers\Reader\LibraryController;
use App\Http\Controllers\Author\AuthorEbookController;
use App\Http\Controllers\Admin\AdminEbookController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminAuthorController;
use App\Http\Controllers\ProfileController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/api/ebooks/search', [HomeController::class, 'liveSearch'])->name('api.ebooks.search');
Route::get('/ebooks/{slug}', [HomeController::class, 'show'])->name('ebooks.show');
Route::get('/read/{slug}', [EbookReaderController::class, 'read'])->name('ebooks.read');

Route::middleware('auth')->group(function () {
    Route::get('/my-library', [LibraryController::class, 'index'])->name('reader.library');

    Route::post('/bookmarks/toggle', [EbookReaderController::class, 'toggleBookmark'])->name('bookmarks.toggle.body');
    Route::post('/bookmarks/{ebookId}', [EbookReaderController::class, 'toggleBookmark'])->name('bookmarks.toggle');
    Route::post('/progress/{ebookId}', [EbookReaderController::class, 'updateProgress'])->name('progress.update');
    Route::post('/ebooks/{ebookId}/unlock', [EbookReaderController::class, 'unlock'])->name('ebooks.unlock');

    Route::post('/ebooks/{ebookId}/reviews', [EbookReaderController::class, 'storeReview'])->name('reviews.store');
    Route::put('/reviews/{review}', [EbookReaderController::class, 'updateReview'])->name('reviews.update');
    Route::delete('/reviews/{review}', [EbookReaderController::class, 'destroyReview'])->name('reviews.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('author')->name('author.')->middleware('role:author,admin')->group(function () {
        Route::get('/dashboard', [AuthorEbookController::class, 'index'])->name('ebooks.index');
        Route::get('/ebooks/create', [AuthorEbookController::class, 'create'])->name('ebooks.create');
        Route::post('/ebooks/upload-chunk', [AuthorEbookController::class, 'uploadChunk'])->name('ebooks.upload-chunk');
        Route::post('/ebooks', [AuthorEbookController::class, 'store'])->name('ebooks.store');
        Route::get('/ebooks/{id}/edit', [AuthorEbookController::class, 'edit'])->name('ebooks.edit');
        Route::put('/ebooks/{id}', [AuthorEbookController::class, 'update'])->name('ebooks.update');
        Route::delete('/ebooks/{id}', [AuthorEbookController::class, 'destroy'])->name('ebooks.destroy');
    });

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/pending-ebooks', [AdminEbookController::class, 'pending'])->name('ebooks.pending');
        Route::get('/ebooks', [AdminEbookController::class, 'index'])->name('ebooks.index');
        Route::post('/ebooks/{id}/approve', [AdminEbookController::class, 'approve'])->name('ebooks.approve');
        Route::post('/ebooks/{id}/reject', [AdminEbookController::class, 'reject'])->name('ebooks.reject');
        Route::delete('/ebooks/{id}', [AdminEbookController::class, 'destroy'])->name('ebooks.destroy');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
        Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/authors', [AdminAuthorController::class, 'index'])->name('authors.index');
        Route::post('/authors', [AdminAuthorController::class, 'store'])->name('authors.store');
        Route::put('/authors/{author}', [AdminAuthorController::class, 'update'])->name('authors.update');
        Route::delete('/authors/{author}', [AdminAuthorController::class, 'destroy'])->name('authors.destroy');
    });
});

require __DIR__.'/auth.php';
