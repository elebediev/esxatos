<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::published()
            ->with(['categories'])
            ->latest('published_at');

        // Filter by category
        if ($categorySlug = $request->get('category')) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $query->whereHas('categories', fn($q) => $q->where('categories.id', $category->id));
            }
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        $query = match ($sort) {
            'popular' => $query->reorder('views_count', 'desc'),
            'title' => $query->reorder('title', 'asc'),
            default => $query,
        };

        $books = $query->paginate(24)->withQueryString();

        $categories = Category::active()
            ->withCount(['books' => fn($q) => $q->published()])
            ->having('books_count', '>', 0)
            ->orderBy('weight')
            ->get();

        return view('books.index', compact('books', 'categories', 'sort'));
    }

    public function show(string $slug): View
    {
        $book = Book::where('slug', $slug)
            ->published()
            ->with(['categories', 'files', 'user'])
            ->firstOrFail();

        $book->incrementViews();

        // Related books (same categories)
        $relatedBooks = Book::published()
            ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $book->categories->pluck('id')))
            ->where('id', '!=', $book->id)
            ->inRandomOrder()
            ->limit(6)
            ->get();

        return view('books.show', compact('book', 'relatedBooks'));
    }
}
