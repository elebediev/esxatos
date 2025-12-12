@extends('layouts.app')

@section('title', 'Книги - Esxatos')
@section('description', 'Полный каталог книг богословской библиотеки Esxatos. Книги по библеистике, богословию, истории церкви.')

@section('content')
    <div class="catalog-page">
        {{-- Sidebar --}}
        <aside class="catalog-sidebar">
            {{-- Categories Section --}}
            <div class="sidebar-section">
                <h3 class="sidebar-title">Категории</h3>
                <nav class="sidebar-nav">
                    @foreach($categories as $category)
                        <a href="{{ route('books.index', ['category' => $category->slug]) }}"
                           class="sidebar-link {{ request('category') == $category->slug ? 'active' : '' }}">
                            <span class="sidebar-link-name">{{ $category->name }}</span>
                            <span class="sidebar-link-count">{{ $category->books_count }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="catalog-content">
            <h1 class="catalog-title">Книги</h1>

            @if($books->isEmpty())
                <div class="empty-state">
                    <p>Книги не найдены</p>
                </div>
            @else
                <div class="books-grid">
                    @foreach($books as $book)
                        @include('components.book-card-modern', ['book' => $book])
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($books->hasPages())
                    <nav class="pagination-wrapper">
                        <div class="pagination">
                            {{-- Previous --}}
                            @if($books->onFirstPage())
                                <span class="pagination-link disabled">Назад</span>
                            @else
                                <a href="{{ $books->previousPageUrl() }}" class="pagination-link">Назад</a>
                            @endif

                            {{-- Page Numbers --}}
                            @php
                                $currentPage = $books->currentPage();
                                $lastPage = $books->lastPage();
                            @endphp

                            @for($i = 1; $i <= min(3, $lastPage); $i++)
                                <a href="{{ $books->url($i) }}"
                                   class="pagination-link {{ $currentPage == $i ? 'active' : '' }}">{{ $i }}</a>
                            @endfor

                            @if($lastPage > 4)
                                <span class="pagination-ellipsis">...</span>
                                <a href="{{ $books->url($lastPage) }}"
                                   class="pagination-link {{ $currentPage == $lastPage ? 'active' : '' }}">{{ $lastPage }}</a>
                            @elseif($lastPage == 4)
                                <a href="{{ $books->url(4) }}"
                                   class="pagination-link {{ $currentPage == 4 ? 'active' : '' }}">4</a>
                            @endif

                            {{-- Next --}}
                            @if($books->hasMorePages())
                                <a href="{{ $books->nextPageUrl() }}" class="pagination-link">Дальше</a>
                            @else
                                <span class="pagination-link disabled">Дальше</span>
                            @endif
                        </div>
                    </nav>
                @endif
            @endif
        </main>
    </div>
@endsection

@push('styles')
<style>
    /* Catalog Layout */
    .catalog-page {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 3rem;
        align-items: start;
    }

    /* Sidebar */
    .catalog-sidebar {
        position: sticky;
        top: 2rem;
    }

    .sidebar-section {
        margin-bottom: 2rem;
    }

    .sidebar-section:last-child {
        margin-bottom: 0;
    }

    .sidebar-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 1rem;
    }

    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .sidebar-link {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        color: #111827;
        font-size: 0.95rem;
        border-bottom: none;
        transition: color 0.2s;
    }

    .sidebar-link:hover {
        color: #3b82f6;
    }

    .sidebar-link.active {
        color: #3b82f6;
        font-weight: 500;
    }

    .sidebar-link-name {
        flex: 1;
    }

    .sidebar-link-count {
        color: #9ca3af;
        font-size: 0.875rem;
        margin-left: 0.5rem;
    }

    .sidebar-link:hover .sidebar-link-count,
    .sidebar-link.active .sidebar-link-count {
        color: #3b82f6;
    }

    /* Main Content */
    .catalog-content {
        min-width: 0;
    }

    .catalog-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 2rem;
    }

    /* Books Grid - 3 columns */
    .books-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        margin-bottom: 3rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
        background: #f9fafb;
        border-radius: 8px;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }

    .pagination {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pagination-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        color: #4b5563;
        font-size: 0.95rem;
        font-weight: 500;
        background: white;
        transition: all 0.2s;
    }

    .pagination-link:hover:not(.disabled):not(.active) {
        border-color: #3b82f6;
        color: #3b82f6;
    }

    .pagination-link.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    .pagination-link.disabled {
        color: #d1d5db;
        cursor: not-allowed;
    }

    .pagination-ellipsis {
        color: #9ca3af;
        padding: 0 0.5rem;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .books-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
    }

    @media (max-width: 1024px) {
        .catalog-page {
            grid-template-columns: 180px 1fr;
            gap: 2rem;
        }

        .books-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .catalog-page {
            grid-template-columns: 1fr;
        }

        .catalog-sidebar {
            position: static;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .sidebar-section {
            margin-bottom: 0;
        }

        .books-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .catalog-sidebar {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .books-grid {
            grid-template-columns: 1fr;
            max-width: 280px;
            margin: 0 auto 3rem;
        }
    }
</style>
@endpush
