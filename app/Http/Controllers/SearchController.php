<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->get('q', '');
        $books = collect();

        if (strlen($query) >= 2) {
            $books = Book::published()
                ->with(['categories'])
                ->where(function ($q) use ($query) {
                    // Fulltext search for relevance
                    $q->whereRaw(
                        "MATCH(title, description_plain) AGAINST(? IN NATURAL LANGUAGE MODE)",
                        [$query]
                    );
                })
                ->orWhere(function ($q) use ($query) {
                    // Fallback to LIKE for partial matches
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->where('is_published', true);
                })
                ->orderByRaw(
                    "MATCH(title, description_plain) AGAINST(? IN NATURAL LANGUAGE MODE) DESC",
                    [$query]
                )
                ->paginate(24)
                ->withQueryString();
        }

        // Show recent books when no query
        $recentBooks = collect();
        if (empty($query)) {
            $recentBooks = Book::published()
                ->books()
                ->with(['categories'])
                ->latest('created_at')
                ->limit(8)
                ->get();
        }

        return view('search.index', compact('books', 'query', 'recentBooks'));
    }

    public function autocomplete(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $books = Book::published()
            ->where('title', 'LIKE', "%{$query}%")
            ->select('id', 'title', 'slug', 'cover_image')
            ->limit(10)
            ->get()
            ->map(fn($book) => [
                'id' => $book->id,
                'title' => $book->title,
                'url' => route('book.show', $book->slug),
                'cover' => $book->cover_url,
            ]);

        return response()->json($books);
    }
}
