@extends('layouts.app')

@section('title', 'Категории - Админ-панель')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <div class="categories-header">
            <div class="categories-title-row">
                <h1 class="dashboard-title">
                    @if($parentCategory)
                        {{ $parentCategory->name }}
                    @else
                        Категории
                    @endif
                </h1>
                <a href="{{ route('admin.categories.create', ['parent' => $parentCategory?->id]) }}" class="btn-add">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Добавить категорию
                </a>
            </div>

            @if(count($breadcrumbs) > 0)
            <nav class="breadcrumbs">
                <a href="{{ route('admin.categories.index') }}" class="breadcrumb-link">Все категории</a>
                @foreach($breadcrumbs as $crumb)
                    <span class="breadcrumb-sep">/</span>
                    @if(!$loop->last)
                        <a href="{{ route('admin.categories.index', ['parent' => $crumb->id]) }}" class="breadcrumb-link">{{ $crumb->name }}</a>
                    @else
                        <span class="breadcrumb-current">{{ $crumb->name }}</span>
                    @endif
                @endforeach
            </nav>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="categories-stats">
            <span>Всего: {{ $categories->count() }} категорий</span>
            <span class="save-indicator" id="saveIndicator" style="display: none;">
                <svg class="spinner" width="16" height="16" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4" stroke-dashoffset="10"/>
                </svg>
                Сохранение...
            </span>
            <span class="saved-indicator" id="savedIndicator" style="display: none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранено
            </span>
        </div>

        <div class="categories-list" id="categoriesList">
            @forelse($categories as $category)
            <div class="category-item" data-id="{{ $category->id }}" data-weight="{{ $category->weight }}">
                <div class="category-drag-handle">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm0 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm-2 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm8-14a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm-2 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm2 4a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
                    </svg>
                </div>
                <div class="category-info">
                    <div class="category-name">
                        @if($category->children_count > 0)
                            <a href="{{ route('admin.categories.index', ['parent' => $category->id]) }}" class="category-name-link">
                                {{ $category->name }}
                            </a>
                        @else
                            <span class="category-name-text">{{ $category->name }}</span>
                        @endif
                        @if(!$category->is_active)
                            <span class="category-badge inactive">Скрыта</span>
                        @endif
                    </div>
                    <div class="category-meta">
                        <span class="meta-item">Slug: {{ $category->slug }}</span>
                        <span class="meta-item">Книг: {{ $category->books_count }}</span>
                        @if($category->children_count > 0)
                            <span class="meta-item">Подкатегорий: {{ $category->children_count }}</span>
                        @endif
                        <span class="meta-item">Вес: <span class="weight-value">{{ $category->weight }}</span></span>
                    </div>
                </div>
                <div class="category-actions">
                    @if($category->children_count > 0)
                        <a href="{{ route('admin.categories.index', ['parent' => $category->id]) }}" class="btn-action" title="Подкатегории">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </a>
                    @endif
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn-action" title="Редактировать">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    @if($category->children_count == 0 && $category->books_count == 0)
                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="delete-form" onsubmit="return confirm('Удалить категорию {{ $category->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action btn-danger" title="Удалить">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-message">
                <p>Категории не найдены</p>
                <a href="{{ route('admin.categories.create', ['parent' => $parentCategory?->id]) }}" class="btn-add-empty">Добавить первую категорию</a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('partials.dashboard-styles')
<style>
    .dashboard-title { margin-bottom: 0; }

    .categories-header { margin-bottom: 1.5rem; }
    .categories-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.75rem;
    }

    .breadcrumbs {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }
    .breadcrumb-link { color: var(--primary); }
    .breadcrumb-link:hover { text-decoration: underline; }
    .breadcrumb-sep { color: var(--text-muted); }
    .breadcrumb-current { color: var(--text-main); font-weight: 500; }

    .btn-add {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        font-size: 0.875rem;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-add:hover { background: var(--primary-dark, #5b4dc4); }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-size: 0.875rem;
    }
    .alert-success { background: #d1fae5; color: #065f46; }
    .alert-error { background: #fee2e2; color: #991b1b; }

    .categories-stats {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    .save-indicator, .saved-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary);
    }
    .saved-indicator { color: #059669; }
    .spinner {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .categories-list {
        background: var(--bg-card);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px var(--shadow);
    }

    .category-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--border);
        background: var(--bg-card);
        transition: background 0.2s, box-shadow 0.2s;
    }
    .category-item:last-child { border-bottom: none; }
    .category-item:hover { background: var(--bg-secondary); }
    .category-item.dragging {
        opacity: 0.5;
        background: var(--bg-secondary);
    }
    .category-item.drag-over {
        box-shadow: inset 0 -2px 0 var(--primary);
    }

    .category-drag-handle {
        cursor: grab;
        color: var(--text-muted);
        padding: 0.25rem;
        border-radius: 4px;
        transition: color 0.2s, background 0.2s;
    }
    .category-drag-handle:hover {
        color: var(--text-main);
        background: var(--bg-secondary);
    }
    .category-drag-handle:active { cursor: grabbing; }

    .category-info { flex: 1; min-width: 0; }
    .category-name {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    .category-name-link { color: var(--primary); }
    .category-name-link:hover { text-decoration: underline; }
    .category-name-text { color: var(--text-main); }

    .category-badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .category-badge.inactive { background: #fef3c7; color: #92400e; }

    .category-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .meta-item { white-space: nowrap; }

    .category-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-secondary);
        background: transparent;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-action:hover {
        color: var(--primary);
        border-color: var(--primary);
    }
    .btn-danger:hover {
        color: #dc2626;
        border-color: #dc2626;
    }
    .delete-form { display: inline; }

    .empty-message {
        padding: 3rem;
        text-align: center;
        color: var(--text-secondary);
    }
    .btn-add-empty {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
        padding: 0.5rem 1rem;
        background: var(--primary);
        color: white;
        border-radius: 8px;
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .categories-title-row { flex-direction: column; align-items: flex-start; }
        .category-meta { flex-direction: column; gap: 0.25rem; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById('categoriesList');
    const saveIndicator = document.getElementById('saveIndicator');
    const savedIndicator = document.getElementById('savedIndicator');

    if (!list || list.children.length === 0) return;

    let draggedItem = null;

    // Initialize draggable items
    function initDraggable() {
        const items = list.querySelectorAll('.category-item');

        items.forEach(item => {
            item.setAttribute('draggable', 'true');

            item.addEventListener('dragstart', function(e) {
                draggedItem = item;
                item.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', item.dataset.id);
            });

            item.addEventListener('dragend', function(e) {
                item.classList.remove('dragging');
                list.querySelectorAll('.category-item').forEach(i => i.classList.remove('drag-over'));
                draggedItem = null;
            });

            item.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                if (item !== draggedItem) {
                    const rect = item.getBoundingClientRect();
                    const midY = rect.top + rect.height / 2;

                    list.querySelectorAll('.category-item').forEach(i => i.classList.remove('drag-over'));
                    item.classList.add('drag-over');
                }
            });

            item.addEventListener('dragleave', function(e) {
                item.classList.remove('drag-over');
            });

            item.addEventListener('drop', function(e) {
                e.preventDefault();

                if (item !== draggedItem && draggedItem) {
                    const rect = item.getBoundingClientRect();
                    const midY = rect.top + rect.height / 2;

                    if (e.clientY < midY) {
                        list.insertBefore(draggedItem, item);
                    } else {
                        list.insertBefore(draggedItem, item.nextSibling);
                    }

                    saveOrder();
                }

                list.querySelectorAll('.category-item').forEach(i => i.classList.remove('drag-over'));
            });
        });
    }

    initDraggable();

    function saveOrder() {
        const items = list.querySelectorAll('.category-item');
        const categories = [];

        items.forEach((item, index) => {
            const weight = index * 10;
            categories.push({
                id: parseInt(item.dataset.id),
                weight: weight
            });
            item.querySelector('.weight-value').textContent = weight;
        });

        saveIndicator.style.display = 'inline-flex';
        savedIndicator.style.display = 'none';

        fetch('{{ route('admin.categories.update-order') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ categories })
        })
        .then(response => response.json())
        .then(data => {
            saveIndicator.style.display = 'none';
            savedIndicator.style.display = 'inline-flex';
            setTimeout(() => {
                savedIndicator.style.display = 'none';
            }, 2000);
        })
        .catch(error => {
            console.error('Error saving order:', error);
            saveIndicator.style.display = 'none';
            alert('Ошибка сохранения порядка');
        });
    }
});
</script>
@endpush
