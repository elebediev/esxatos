<?php

use App\Http\Controllers\AudioController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SoftwareController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/api/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

// Categories
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

// Books
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{slug}', [BookController::class, 'show'])->name('book.show');

// BibleQuote Modules
Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
Route::get('/modules/{slug}', [ModuleController::class, 'show'])->name('module.show');

// Software
Route::get('/software', [SoftwareController::class, 'index'])->name('software.index');
Route::get('/software/{slug}', [SoftwareController::class, 'show'])->name('software.show');

// Audio
Route::get('/audio', [AudioController::class, 'index'])->name('audio.index');
Route::get('/audio/{slug}', [AudioController::class, 'show'])->name('audio.show');

// Legacy URL support - redirect /files/{slug} to appropriate section
Route::get('/files/{slug}', function (string $slug) {
    $book = \App\Models\Book::where('slug', $slug)->first();
    if ($book) {
        return match($book->content_type) {
            'module' => redirect()->route('module.show', $slug, 301),
            'software' => redirect()->route('software.show', $slug, 301),
            'audio' => redirect()->route('audio.show', $slug, 301),
            default => redirect()->route('book.show', $slug, 301),
        };
    }
    return redirect()->route('book.show', $slug, 301);
})->where('slug', '.*');

// Dashboard (authenticated users)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes (authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/search-users', [MessageController::class, 'searchUsers'])->name('messages.search-users');
    Route::get('/messages/{thread}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{thread}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    Route::delete('/messages/{thread}', [MessageController::class, 'destroy'])->name('messages.destroy');
});

require __DIR__.'/auth.php';
