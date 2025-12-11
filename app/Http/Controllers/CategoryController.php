<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::active()
            ->roots()
            ->with(['children' => fn($q) => $q->active()->withCount(['books' => fn($q) => $q->published()])])
            ->withCount(['books' => fn($q) => $q->published()])
            ->having('books_count', '>', 0)
            ->orderBy('weight')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function show(string $slug, Request $request): View
    {
        $category = Category::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $query = Book::published()
            ->with(['categories'])
            ->whereHas('categories', fn($q) => $q->where('categories.id', $category->id));

        // Sort
        $sort = $request->get('sort', 'newest');
        $query = match ($sort) {
            'popular' => $query->orderBy('views_count', 'desc'),
            'title' => $query->orderBy('title', 'asc'),
            default => $query->orderBy('published_at', 'desc'),
        };

        $books = $query->paginate(24)->withQueryString();

        // Subcategories
        $subcategories = $category->children()
            ->active()
            ->withCount(['books' => fn($q) => $q->published()])
            ->having('books_count', '>', 0)
            ->get();

        return view('categories.show', compact('category', 'books', 'subcategories', 'sort'));
    }
}
