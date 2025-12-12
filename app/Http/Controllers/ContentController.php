<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ContentController extends Controller
{
    protected string $contentType;
    protected string $viewPrefix;
    protected string $titleSingular;
    protected string $titlePlural;
    protected int $rootCategoryId;

    public function index(Request $request): View
    {
        $query = Book::published()
            ->ofType($this->contentType)
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

        $items = $query->paginate(24)->withQueryString();

        // Get categories for this content type (children of root category), cached for 1 hour
        $cacheKey = $this->contentType . '_sidebar_categories';
        $rootCategoryId = $this->rootCategoryId;
        $contentType = $this->contentType;

        $categories = Cache::remember($cacheKey, 3600, function () use ($rootCategoryId, $contentType) {
            return Category::active()
                ->where(function ($q) use ($rootCategoryId) {
                    $q->where('id', $rootCategoryId)
                        ->orWhere('parent_id', $rootCategoryId);
                })
                ->withCount(['books' => fn($q) => $q->published()->ofType($contentType)])
                ->having('books_count', '>', 0)
                ->orderBy('weight')
                ->get();
        });

        return view('content.index', [
            'items' => $items,
            'categories' => $categories,
            'sort' => $sort,
            'contentType' => $this->contentType,
            'titleSingular' => $this->titleSingular,
            'titlePlural' => $this->titlePlural,
        ]);
    }

    public function show(string $slug): View
    {
        $item = Book::where('slug', $slug)
            ->ofType($this->contentType)
            ->published()
            ->with(['categories', 'files', 'user'])
            ->firstOrFail();

        $item->incrementViews();

        // Related items (same type and categories)
        $relatedItems = Book::published()
            ->ofType($this->contentType)
            ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $item->categories->pluck('id')))
            ->where('id', '!=', $item->id)
            ->inRandomOrder()
            ->limit(6)
            ->get();

        return view('content.show', [
            'item' => $item,
            'relatedItems' => $relatedItems,
            'contentType' => $this->contentType,
            'titleSingular' => $this->titleSingular,
            'titlePlural' => $this->titlePlural,
        ]);
    }
}
