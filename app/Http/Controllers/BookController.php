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
            ->books() // Only show books, not modules/software/audio
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
            ->where(function ($q) {
                // Only show categories under "КНИГИ ЭСХАТОС" (id=1)
                $q->where('id', 1)->orWhere('parent_id', 1);
            })
            ->withCount(['books' => fn($q) => $q->published()->books()])
            ->having('books_count', '>', 0)
            ->orderBy('weight')
            ->get();

        return view('books.index', compact('books', 'categories', 'sort'));
    }

    public function show(string $slug): View
    {
        $book = Book::where('slug', $slug)
            ->books()
            ->published()
            ->with(['categories', 'files', 'user'])
            ->firstOrFail();

        $book->incrementViews();

        // Related books (same type and categories)
        // Get IDs first, then shuffle in PHP (much faster than ORDER BY RAND())
        $relatedBookIds = Book::published()
            ->books()
            ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $book->categories->pluck('id')))
            ->where('id', '!=', $book->id)
            ->limit(100)
            ->pluck('id')
            ->shuffle()
            ->take(6);

        $relatedBooks = $relatedBookIds->isNotEmpty()
            ? Book::whereIn('id', $relatedBookIds)->get()
            : collect();

        return view('books.show', compact('book', 'relatedBooks'));
    }
}
