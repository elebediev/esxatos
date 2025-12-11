<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Latest books
        $latestBooks = Book::published()
            ->with(['categories'])
            ->latest('published_at')
            ->limit(12)
            ->get();

        // Popular books
        $popularBooks = Book::published()
            ->with(['categories'])
            ->orderBy('views_count', 'desc')
            ->limit(12)
            ->get();

        // Top categories
        $categories = Category::active()
            ->roots()
            ->withCount(['books' => fn($q) => $q->published()])
            ->having('books_count', '>', 0)
            ->orderBy('weight')
            ->limit(12)
            ->get();

        // Stats
        $stats = [
            'books' => Book::published()->count(),
            'categories' => Category::active()->count(),
        ];

        return view('home', compact('latestBooks', 'popularBooks', 'categories', 'stats'));
    }
}
