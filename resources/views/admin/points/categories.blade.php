@extends('layouts.app')

@section('title', 'Категории баллов - Админ-панель')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <div class="page-header">
            <h1 class="dashboard-title">Категории баллов</h1>
            <a href="{{ route('admin.points.index') }}" class="action-btn secondary">&larr; Балансы</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="categories-grid">
            @foreach($categories as $category)
            <div class="category-card">
                <div class="category-header">
                    <h3 class="category-name">{{ $category->name }}</h3>
                    <span class="category-slug">{{ $category->slug }}</span>
                </div>

                @if($category->description)
                    <p class="category-description">{{ $category->description }}</p>
                @endif

                <div class="category-stats">
                    <div class="stat">
                        <span class="stat-value">{{ number_format($category->user_points_count, 0, '.', ' ') }}</span>
                        <span class="stat-label">пользователей</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">{{ number_format($category->transactions_count, 0, '.', ' ') }}</span>
                        <span class="stat-label">транзакций</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.points.categories.update', $category) }}" class="category-form">
                    @csrf
                    @method('PATCH')

                    <div class="form-row">
                        <input type="text" name="name" value="{{ $category->name }}" class="form-input" placeholder="Название">
                    </div>
                    <div class="form-row">
                        <textarea name="description" class="form-textarea" rows="2" placeholder="Описание">{{ $category->description }}</textarea>
                    </div>
                    <div class="form-row checkbox-row">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                            Активна
                        </label>
                    </div>
                    <button type="submit" class="btn-save">Сохранить</button>
                </form>

                @if($category->drupal_tid)
                    <div class="drupal-info">
                        Drupal TID: {{ $category->drupal_tid }}
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('partials.dashboard-styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .dashboard-title { margin: 0; }
    .action-btn {
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
    }
    .action-btn.secondary {
        background: var(--bg-secondary);
        color: var(--text-main);
        border: 1px solid var(--border);
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .alert-success { background: #d1fae5; color: #065f46; }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .category-card {
        background: var(--bg-card);
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 1px 3px var(--shadow);
    }

    .category-header {
        margin-bottom: 1rem;
    }
    .category-name {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
    }
    .category-slug {
        color: var(--text-secondary);
        font-size: 0.75rem;
        font-family: monospace;
    }

    .category-description {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .category-stats {
        display: flex;
        gap: 1.5rem;
        padding: 1rem 0;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        margin-bottom: 1rem;
    }
    .stat-value {
        display: block;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary);
        white-space: nowrap;
    }
    .stat-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .category-form { margin-top: 1rem; }
    .form-row { margin-bottom: 0.75rem; }
    .form-input, .form-textarea {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: var(--bg-secondary);
        color: var(--text-main);
        font-size: 0.875rem;
    }
    .form-textarea { resize: vertical; }
    .checkbox-row {
        display: flex;
        align-items: center;
    }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        cursor: pointer;
    }

    .btn-save {
        width: 100%;
        padding: 0.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        margin-top: 0.5rem;
    }
    .btn-save:hover { background: var(--primary-dark, #5b4dc4); }

    .drupal-info {
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px dashed var(--border);
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
</style>
@endpush
