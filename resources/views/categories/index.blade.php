@extends('layouts.app')

@section('title', 'Категории - Esxatos')
@section('description', 'Каталог категорий богословской библиотеки Esxatos. Библеистика, богословие, история церкви и другие разделы.')

@section('content')
    <h1 class="page-title">Категории</h1>

    <div class="categories-list">
        @foreach($categories as $category)
            <div class="category-block card">
                <a href="{{ route('category.show', $category->slug) }}" class="category-block-header">
                    <h2>{{ $category->name }}</h2>
                    <span class="category-block-count">{{ $category->books_count }} книг</span>
                </a>

                @if($category->children->isNotEmpty())
                    <div class="category-block-children">
                        @foreach($category->children->take(8) as $child)
                            <a href="{{ route('category.show', $child->slug) }}" class="category-child">
                                {{ $child->name }}
                                <span>({{ $child->books_count }})</span>
                            </a>
                        @endforeach
                        @if($category->children->count() > 8)
                            <a href="{{ route('category.show', $category->slug) }}" class="category-child more">
                                Ещё {{ $category->children->count() - 8 }}...
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endsection

@push('styles')
<style>
    .categories-list {
        display: grid;
        gap: 1.5rem;
    }

    .category-block {
        padding: 1.5rem;
    }

    .category-block-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--text);
        margin-bottom: 1rem;
    }

    .category-block-header:hover {
        text-decoration: none;
    }

    .category-block-header:hover h2 {
        color: var(--primary);
    }

    .category-block-header h2 {
        font-size: 1.25rem;
        transition: color 0.2s;
    }

    .category-block-count {
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    .category-block-children {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .category-child {
        font-size: 0.875rem;
        color: var(--text-muted);
        background: var(--bg);
        padding: 0.375rem 0.75rem;
        border-radius: var(--radius);
        transition: all 0.2s;
    }

    .category-child:hover {
        background: var(--primary);
        color: white;
        text-decoration: none;
    }

    .category-child span {
        opacity: 0.7;
    }

    .category-child.more {
        font-style: italic;
    }
</style>
@endpush
