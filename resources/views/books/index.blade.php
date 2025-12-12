@extends('layouts.app')

@section('title', 'Библиотека - Esxatos')
@section('description', 'Полный каталог книг богословской библиотеки Esxatos. Книги по библеистике, богословию, истории церкви.')

@section('content')
    <div class="books-page">
        <div class="books-sidebar">
            <h2>Категории</h2>
            <nav class="sidebar-categories">
                <a href="{{ route('books.index') }}" class="{{ !request('category') ? 'active' : '' }}">
                    Все книги
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('books.index', ['category' => $category->slug]) }}"
                       class="{{ request('category') == $category->slug ? 'active' : '' }}">
                        {{ $category->name }}
                        <span>({{ $category->books_count }})</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="books-content">
            <div class="books-header">
                <h1 class="page-title">Библиотека</h1>
                <div class="books-sort">
                    <span>Сортировка:</span>
                    <a href="{{ route('books.index', array_merge(request()->except('sort'), ['sort' => 'newest'])) }}"
                       class="{{ $sort == 'newest' ? 'active' : '' }}">Новые</a>
                    <a href="{{ route('books.index', array_merge(request()->except('sort'), ['sort' => 'popular'])) }}"
                       class="{{ $sort == 'popular' ? 'active' : '' }}">Популярные</a>
                    <a href="{{ route('books.index', array_merge(request()->except('sort'), ['sort' => 'title'])) }}"
                       class="{{ $sort == 'title' ? 'active' : '' }}">По названию</a>
                </div>
            </div>

            @if($books->isEmpty())
                <div class="empty-state">
                    <p>Книги не найдены</p>
                </div>
            @else
                <div class="books-grid">
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
    .books-page {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 2rem;
    }

    .books-sidebar {
        background: var(--bg-card);
        border-radius: var(--radius);
        padding: 1.5rem;
        height: fit-content;
        position: sticky;
        top: 100px;
    }

    .books-sidebar h2 {
        font-size: 1.125rem;
        margin-bottom: 1rem;
    }

    .sidebar-categories {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .sidebar-categories a {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius);
        color: var(--text);
        font-size: 0.875rem;
    }

    .sidebar-categories a:hover {
        background: var(--bg);
        text-decoration: none;
    }

    .sidebar-categories a.active {
        background: var(--primary);
        color: white;
    }

    .sidebar-categories a span {
        opacity: 0.6;
    }

    .books-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
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

    .books-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }

    @media (max-width: 1200px) {
        .books-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .books-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 1024px) {
        .books-page {
            grid-template-columns: 1fr;
        }

        .books-sidebar {
            position: static;
        }

        .sidebar-categories {
            flex-direction: row;
            flex-wrap: wrap;
        }
    }
</style>
@endpush
