@extends('layouts.app')

@section('title', $titlePlural . ' - Esxatos')
@section('description', 'Каталог: ' . $titlePlural . ' богословской библиотеки Esxatos.')

@section('content')
    <div class="catalog-page">
        {{-- Sidebar --}}
        <aside class="catalog-sidebar">
            {{-- Categories Section --}}
            @php
                $indexRouteName = match($contentType) {
                    'module' => 'modules.index',
                    'software' => 'software.index',
                    'audio' => 'audio.index',
                    default => 'books.index',
                };
            @endphp
            <div class="sidebar-section">
                <h3 class="sidebar-title">Категории</h3>
                <nav class="sidebar-nav">
                    @foreach($categories as $category)
                        <a href="{{ route($indexRouteName, ['category' => $category->slug]) }}"
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
                <h1 class="catalog-title">{{ $titlePlural }}</h1>

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

            @if($items->isEmpty())
                <div class="empty-state">
                    <p>{{ $titlePlural }} не найдены</p>
                </div>
            @else
                <div class="books-container" id="booksContainer">
                    {{-- Grid View --}}
                    <div class="books-grid" id="booksGrid">
                        @foreach($items as $item)
                            @include('components.content-card', ['item' => $item, 'contentType' => $contentType])
                        @endforeach
                    </div>

                    {{-- List View (hidden by default) --}}
                    <div class="books-list hidden" id="booksList">
                        @foreach($items as $item)
                            @include('components.content-list-item', ['item' => $item, 'contentType' => $contentType])
                        @endforeach
                    </div>
                </div>

                {{-- Pagination --}}
                @if($items->hasPages())
                    <nav class="pagination-wrapper">
                        <div class="pagination">
                            @if($items->onFirstPage())
                                <span class="pagination-link disabled">Назад</span>
                            @else
                                <a href="{{ $items->previousPageUrl() }}" class="pagination-link">Назад</a>
                            @endif

                            @php
                                $currentPage = $items->currentPage();
                                $lastPage = $items->lastPage();
                            @endphp

                            @for($i = 1; $i <= min(3, $lastPage); $i++)
                                <a href="{{ $items->url($i) }}"
                                   class="pagination-link {{ $currentPage == $i ? 'active' : '' }}">{{ $i }}</a>
                            @endfor

                            @if($lastPage > 4)
                                <span class="pagination-ellipsis">...</span>
                                <a href="{{ $items->url($lastPage) }}"
                                   class="pagination-link {{ $currentPage == $lastPage ? 'active' : '' }}">{{ $lastPage }}</a>
                            @elseif($lastPage == 4)
                                <a href="{{ $items->url(4) }}"
                                   class="pagination-link {{ $currentPage == 4 ? 'active' : '' }}">4</a>
                            @endif

                            @if($items->hasMorePages())
                                <a href="{{ $items->nextPageUrl() }}" class="pagination-link">Дальше</a>
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
    .catalog-page { display: grid; grid-template-columns: 220px 1fr; gap: 3rem; align-items: start; }
    .catalog-sidebar { }
    .sidebar-section { margin-bottom: 2rem; }
    .sidebar-section:last-child { margin-bottom: 0; }
    .sidebar-title { font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; }
    .sidebar-nav { display: flex; flex-direction: column; gap: 0.25rem; }
    .sidebar-link { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; color: var(--text-main); font-size: 0.95rem; border-bottom: none; transition: color 0.2s; }
    .sidebar-link:hover { color: var(--primary); }
    .sidebar-link.active { color: var(--primary); font-weight: 500; }
    .sidebar-link-name { flex: 1; }
    .sidebar-link-count { color: var(--text-muted); font-size: 0.875rem; margin-left: 0.5rem; }
    .sidebar-link:hover .sidebar-link-count, .sidebar-link.active .sidebar-link-count { color: var(--primary); }
    .catalog-content { min-width: 0; }
    .catalog-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
    .catalog-title { font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0; }
    .view-switcher { display: flex; gap: 0.25rem; background: var(--bg-secondary); padding: 0.25rem; border-radius: 8px; }
    .view-btn { display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: none; background: transparent; color: var(--text-muted); border-radius: 6px; cursor: pointer; transition: all 0.2s; }
    .view-btn:hover { color: var(--text-main); }
    .view-btn.active { background: var(--bg-card); color: var(--primary); box-shadow: 0 1px 3px var(--shadow); }
    .books-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 3rem; }
    .books-grid.grid-4 { grid-template-columns: repeat(4, 1fr); }
    .books-list { display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 3rem; }
    .books-list.hidden, .books-grid.hidden { display: none; }
    .empty-state { text-align: center; padding: 4rem 2rem; color: var(--text-muted); background: var(--bg-secondary); border-radius: 8px; }
    .pagination-wrapper { display: flex; justify-content: center; padding-top: 2rem; border-top: 1px solid var(--border); }
    .pagination { display: flex; align-items: center; gap: 0.5rem; }
    .pagination-link { display: flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: var(--text-secondary); font-size: 0.95rem; font-weight: 500; background: var(--bg-card); transition: all 0.2s; }
    .pagination-link:hover:not(.disabled):not(.active) { border-color: var(--primary); color: var(--primary); }
    .pagination-link.active { background: var(--primary); border-color: var(--primary); color: white; }
    .pagination-link.disabled { color: var(--text-muted); cursor: not-allowed; opacity: 0.5; }
    .pagination-ellipsis { color: var(--text-muted); padding: 0 0.5rem; }
    @media (max-width: 1024px) { .catalog-page { grid-template-columns: 180px 1fr; gap: 2rem; } .books-grid, .books-grid.grid-4 { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 900px) { .books-grid, .books-grid.grid-4 { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) { .catalog-page { grid-template-columns: 1fr; } .catalog-sidebar { position: static; } .books-grid, .books-grid.grid-4 { grid-template-columns: repeat(2, 1fr); gap: 1.5rem; } }
    @media (max-width: 480px) { .books-grid, .books-grid.grid-4 { grid-template-columns: 1fr; max-width: 280px; margin: 0 auto 3rem; } .view-switcher { display: none; } }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('.view-btn');
    const booksGrid = document.getElementById('booksGrid');
    const booksList = document.getElementById('booksList');
    const savedView = localStorage.getItem('{{ $contentType }}sView') || 'grid-3';
    setView(savedView);

    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            setView(view);
            localStorage.setItem('{{ $contentType }}sView', view);
        });
    });

    function setView(view) {
        viewButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.view === view));
        if (view === 'list') {
            booksGrid.classList.add('hidden');
            booksList.classList.remove('hidden');
        } else {
            booksGrid.classList.remove('hidden');
            booksList.classList.add('hidden');
            booksGrid.classList.toggle('grid-4', view === 'grid-4');
        }
    }
});
</script>
@endpush
