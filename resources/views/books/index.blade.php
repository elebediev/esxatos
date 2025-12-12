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
            <div class="catalog-header">
                <h1 class="catalog-title">Книги</h1>

                {{-- View Switcher --}}
                <div class="view-switcher">
                    <button type="button" class="view-btn active" data-view="grid-3" title="3 в ряд">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="currentColor">
                            <rect x="0" y="0" width="5" height="5" rx="1"/>
                            <rect x="6.5" y="0" width="5" height="5" rx="1"/>
                            <rect x="13" y="0" width="5" height="5" rx="1"/>
                            <rect x="0" y="6.5" width="5" height="5" rx="1"/>
                            <rect x="6.5" y="6.5" width="5" height="5" rx="1"/>
                            <rect x="13" y="6.5" width="5" height="5" rx="1"/>
                            <rect x="0" y="13" width="5" height="5" rx="1"/>
                            <rect x="6.5" y="13" width="5" height="5" rx="1"/>
                            <rect x="13" y="13" width="5" height="5" rx="1"/>
                        </svg>
                    </button>
                    <button type="button" class="view-btn" data-view="grid-4" title="4 в ряд">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="currentColor">
                            <rect x="0" y="0" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="4.8" y="0" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="9.6" y="0" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="14.5" y="0" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="0" y="4.8" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="4.8" y="4.8" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="9.6" y="4.8" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="14.5" y="4.8" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="0" y="9.6" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="4.8" y="9.6" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="9.6" y="9.6" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="14.5" y="9.6" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="0" y="14.5" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="4.8" y="14.5" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="9.6" y="14.5" width="3.5" height="3.5" rx="0.5"/>
                            <rect x="14.5" y="14.5" width="3.5" height="3.5" rx="0.5"/>
                        </svg>
                    </button>
                    <button type="button" class="view-btn" data-view="list" title="Список">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="currentColor">
                            <rect x="0" y="0" width="18" height="5" rx="1"/>
                            <rect x="0" y="6.5" width="18" height="5" rx="1"/>
                            <rect x="0" y="13" width="18" height="5" rx="1"/>
                        </svg>
                    </button>
                </div>
            </div>

            @if($books->isEmpty())
                <div class="empty-state">
                    <p>Книги не найдены</p>
                </div>
            @else
                <div class="books-container" id="booksContainer">
                    {{-- Grid View --}}
                    <div class="books-grid" id="booksGrid">
                        @foreach($books as $book)
                            @include('components.book-card-modern', ['book' => $book])
                        @endforeach
                    </div>

                    {{-- List View (hidden by default) --}}
                    <div class="books-list hidden" id="booksList">
                        @foreach($books as $book)
                            <article class="book-list-item">
                                <a href="{{ route('book.show', $book->slug) }}" class="book-list-cover">
                                    @if($book->cover_image)
                                        <img src="{{ asset('storage/uploads/' . $book->cover_image) }}"
                                             alt="{{ $book->cover_alt ?? $book->title }}"
                                             loading="lazy">
                                    @else
                                        <div class="book-list-placeholder">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>
                                <div class="book-list-info">
                                    <a href="{{ route('book.show', $book->slug) }}" class="book-list-title">{{ $book->title }}</a>
                                    @if($book->categories->isNotEmpty())
                                        <div class="book-list-category">
                                            {{ $book->categories->pluck('name')->join(', ') }}
                                        </div>
                                    @endif
                                    @if($book->description_plain)
                                        <p class="book-list-desc">{{ Str::limit($book->description_plain, 250) }}</p>
                                    @endif
                                    <div class="book-list-meta">
                                        <span>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            {{ number_format($book->views_count, 0, '', ' ') }}
                                        </span>
                                        <span>{{ $book->published_at?->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
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

    .catalog-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .catalog-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    /* View Switcher */
    .view-switcher {
        display: flex;
        gap: 0.25rem;
        background: #f3f4f6;
        padding: 0.25rem;
        border-radius: 8px;
    }

    .view-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: none;
        background: transparent;
        color: #6b7280;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .view-btn:hover {
        color: #111827;
    }

    .view-btn.active {
        background: white;
        color: #3b82f6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Books Grid */
    .books-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .books-grid.grid-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    /* Books List */
    .books-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .books-list.hidden {
        display: none;
    }

    .books-grid.hidden {
        display: none;
    }

    .book-list-item {
        display: flex;
        gap: 1.5rem;
        padding: 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .book-list-cover {
        flex-shrink: 0;
        width: 120px;
    }

    .book-list-cover img {
        width: 100%;
        height: auto;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .book-list-placeholder {
        width: 100%;
        aspect-ratio: 2/3;
        background: #e5e7eb;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
    }

    .book-list-info {
        flex: 1;
        min-width: 0;
    }

    .book-list-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.4;
        display: block;
        margin-bottom: 0.5rem;
    }

    .book-list-title:hover {
        color: #3b82f6;
    }

    .book-list-category {
        font-size: 0.85rem;
        color: #3b82f6;
        margin-bottom: 0.75rem;
    }

    .book-list-desc {
        font-size: 0.9rem;
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .book-list-meta {
        display: flex;
        gap: 1.5rem;
        font-size: 0.85rem;
        color: #9ca3af;
    }

    .book-list-meta span {
        display: flex;
        align-items: center;
        gap: 0.35rem;
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
        }
        .books-grid.grid-4 {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1024px) {
        .catalog-page {
            grid-template-columns: 180px 1fr;
            gap: 2rem;
        }

        .books-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        .books-grid.grid-4 {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 900px) {
        .books-grid,
        .books-grid.grid-4 {
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

        .books-grid,
        .books-grid.grid-4 {
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .book-list-item {
            padding: 1rem;
            gap: 1rem;
        }

        .book-list-cover {
            width: 80px;
        }

        .book-list-title {
            font-size: 1rem;
        }

        .book-list-desc {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .catalog-sidebar {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .books-grid,
        .books-grid.grid-4 {
            grid-template-columns: 1fr;
            max-width: 280px;
            margin: 0 auto 3rem;
        }

        .view-switcher {
            display: none;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('.view-btn');
    const booksGrid = document.getElementById('booksGrid');
    const booksList = document.getElementById('booksList');

    // Load saved view preference
    const savedView = localStorage.getItem('booksView') || 'grid-3';
    setView(savedView);

    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            setView(view);
            localStorage.setItem('booksView', view);
        });
    });

    function setView(view) {
        // Update buttons
        viewButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === view);
        });

        // Update view
        if (view === 'list') {
            booksGrid.classList.add('hidden');
            booksList.classList.remove('hidden');
        } else {
            booksGrid.classList.remove('hidden');
            booksList.classList.add('hidden');

            if (view === 'grid-4') {
                booksGrid.classList.add('grid-4');
            } else {
                booksGrid.classList.remove('grid-4');
            }
        }
    }
});
</script>
@endpush
