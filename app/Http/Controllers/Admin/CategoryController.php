<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $parentId = $request->get('parent');

        $query = Category::query()
            ->withCount('books')
            ->withCount('children')
            ->orderBy('weight');

        if ($parentId) {
            $query->where('parent_id', $parentId);
            $parentCategory = Category::find($parentId);
        } else {
            $query->whereNull('parent_id');
            $parentCategory = null;
        }

        // Get breadcrumbs for navigation
        $breadcrumbs = [];
        if ($parentCategory) {
            $current = $parentCategory;
            while ($current) {
                array_unshift($breadcrumbs, $current);
                $current = $current->parent;
            }
        }

        $categories = $query->get();

        return view('admin.categories.index', compact('categories', 'parentCategory', 'breadcrumbs'));
    }

    public function create(Request $request)
    {
        $parentId = $request->get('parent');
        $parentCategory = $parentId ? Category::find($parentId) : null;

        // Get all categories for parent selection
        $allCategories = Category::orderBy('weight')->get();

        return view('admin.categories.create', compact('parentCategory', 'allCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'weight' => 'integer',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Category::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }

        $validated['is_active'] = $request->boolean('is_active');

        Category::create($validated);

        $redirectUrl = route('admin.categories.index');
        if ($request->parent_id) {
            $redirectUrl .= '?parent=' . $request->parent_id;
        }

        return redirect($redirectUrl)->with('success', 'Категория создана');
    }

    public function edit(Category $category)
    {
        // Get all categories except current and its descendants
        $excludeIds = $this->getDescendantIds($category);
        $excludeIds[] = $category->id;

        $allCategories = Category::whereNotIn('id', $excludeIds)
            ->orderBy('weight')
            ->get();

        return view('admin.categories.edit', compact('category', 'allCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'weight' => 'integer',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }

        // Prevent setting parent to self or descendant
        if ($validated['parent_id']) {
            $descendantIds = $this->getDescendantIds($category);
            if (in_array($validated['parent_id'], $descendantIds) || $validated['parent_id'] == $category->id) {
                return back()->withErrors(['parent_id' => 'Нельзя выбрать эту категорию как родительскую']);
            }
        }

        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        return redirect()->route('admin.categories.index', ['parent' => $category->parent_id])
            ->with('success', 'Категория обновлена');
    }

    public function destroy(Category $category)
    {
        // Check if has children
        if ($category->children()->count() > 0) {
            return back()->withErrors(['error' => 'Сначала удалите подкатегории']);
        }

        // Check if has books
        if ($category->books()->count() > 0) {
            return back()->withErrors(['error' => 'Категория имеет связанные книги']);
        }

        $parentId = $category->parent_id;
        $category->delete();

        return redirect()->route('admin.categories.index', ['parent' => $parentId])
            ->with('success', 'Категория удалена');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.weight' => 'required|integer',
        ]);

        foreach ($validated['categories'] as $item) {
            Category::where('id', $item['id'])->update(['weight' => $item['weight']]);
        }

        return response()->json(['success' => true, 'message' => 'Порядок обновлен']);
    }

    private function getDescendantIds(Category $category): array
    {
        $ids = [];
        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }
}
