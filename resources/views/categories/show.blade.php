@extends('layouts.app')

@section('title', $category->name . ' - Esxatos')
@section('description', 'Книги в категории ' . $category->name . '. Богословская библиотека Esxatos.')

@section('content')
    <div class="category-page">
        <div class="category-header mb-3">
            <nav class="breadcrumb">
                <a href="{{ route('home') }}">Главная</a>
                <span>/</span>
                <a href="{{ route('categories.index') }}">Категории</a>
                <span>/</span>
                <span>{{ $category->name }}</span>
            </nav>

            <h1 class="page-title">{{ $category->name }}</h1>

            @if($category->description)
                <p class="text-muted">{{ $category->description }}</p>
            @endif
        </div>

        @if($subcategories->isNotEmpty())
            <div class="subcategories mb-3">
                <h2>Подкатегории</h2>
                <div class="subcategories-list">
                    @foreach($subcategories as $subcategory)
                        <a href="{{ route('category.show', $subcategory->slug) }}" class="subcategory-item">
                            {{ $subcategory->name }}
                            <span>({{ $subcategory->books_count }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="category-books">
            <div class="books-header">
                <h2>Книги в категории ({{ $books->total() }})</h2>
                <div class="books-sort">
                    <span>Сортировка:</span>
                    <a href="{{ route('category.show', ['slug' => $category->slug, 'sort' => 'newest']) }}"
                       class="{{ $sort == 'newest' ? 'active' : '' }}">Новые</a>
                    <a href="{{ route('category.show', ['slug' => $category->slug, 'sort' => 'popular']) }}"
                       class="{{ $sort == 'popular' ? 'active' : '' }}">Популярные</a>
                    <a href="{{ route('category.show', ['slug' => $category->slug, 'sort' => 'title']) }}"
                       class="{{ $sort == 'title' ? 'active' : '' }}">По названию</a>
                </div>
            </div>

            @if($books->isEmpty())
                <div class="empty-state">
                    <p>В этой категории пока нет книг</p>
                </div>
            @else
                <div class="grid grid-4">
                    @foreach($books as $book)
                        @include('components.book-card', ['book' => $book])
                    @endforeach
                </div>

                {{ $books->links('pagination.simple') }}
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .breadcrumb a {
        color: var(--text-muted);
    }

    .breadcrumb a:hover {
        color: var(--primary);
    }

    .subcategories {
        background: var(--bg-card);
        border-radius: var(--radius);
        padding: 1.5rem;
    }

    .subcategories h2 {
        font-size: 1rem;
        margin-bottom: 1rem;
    }

    .subcategories-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .subcategory-item {
        font-size: 0.875rem;
        color: var(--text);
        background: var(--bg);
        padding: 0.5rem 1rem;
        border-radius: var(--radius);
        transition: all 0.2s;
    }

    .subcategory-item:hover {
        background: var(--primary);
        color: white;
        text-decoration: none;
    }

    .subcategory-item span {
        opacity: 0.6;
        margin-left: 0.25rem;
    }

    .books-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .books-header h2 {
        font-size: 1.25rem;
    }

    .books-sort {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.875rem;
    }

    .books-sort span {
        color: var(--text-muted);
    }

    .books-sort a {
        color: var(--text-muted);
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius);
    }

    .books-sort a:hover {
        color: var(--text);
        text-decoration: none;
    }

    .books-sort a.active {
        color: var(--primary);
        font-weight: 500;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }
</style>
@endpush
