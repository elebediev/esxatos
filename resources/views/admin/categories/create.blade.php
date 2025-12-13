@extends('layouts.app')

@section('title', 'Создать категорию - Админ-панель')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <div class="form-header">
            <h1 class="dashboard-title">Создать категорию</h1>
            <a href="{{ route('admin.categories.index', ['parent' => $parentCategory?->id]) }}" class="btn-back">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад
            </a>
        </div>

        @if($parentCategory)
        <div class="parent-info">
            Создание подкатегории для: <strong>{{ $parentCategory->name }}</strong>
        </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="form-card">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">Название <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="slug" class="form-label">Slug (URL)</label>
                    <input type="text" name="slug" id="slug" class="form-input" value="{{ old('slug') }}" placeholder="Оставьте пустым для автогенерации">
                    <p class="form-hint">URL-дружественный идентификатор. Будет сгенерирован автоматически, если оставить пустым.</p>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Описание</label>
                    <textarea name="description" id="description" class="form-textarea" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="parent_id" class="form-label">Родительская категория</label>
                    <select name="parent_id" id="parent_id" class="form-select">
                        <option value="">Без родительской (корневая)</option>
                        @foreach($allCategories as $cat)
                            <option value="{{ $cat->id }}" {{ old('parent_id', $parentCategory?->id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->parent ? '— ' : '' }}{{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="weight" class="form-label">Вес (порядок)</label>
                        <input type="number" name="weight" id="weight" class="form-input" value="{{ old('weight', 0) }}">
                        <p class="form-hint">Меньший вес = выше позиция в списке</p>
                    </div>

                    <div class="form-group form-group-half">
                        <label class="form-label">Статус</label>
                        <label class="checkbox-label">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span>Активна (отображается на сайте)</span>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Создать категорию</button>
                    <a href="{{ route('admin.categories.index', ['parent' => $parentCategory?->id]) }}" class="btn-cancel">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('partials.dashboard-styles')
<style>
    .form-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .dashboard-title { margin-bottom: 0; }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        color: var(--text-secondary);
        border: 1px solid var(--border);
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .btn-back:hover {
        color: var(--text-main);
        border-color: var(--text-main);
    }

    .parent-info {
        background: var(--bg-secondary);
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-size: 0.875rem;
    }
    .alert-error { background: #fee2e2; color: #991b1b; }

    .form-card {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px var(--shadow);
    }

    .form-group { margin-bottom: 1.25rem; }
    .form-row {
        display: flex;
        gap: 1.5rem;
    }
    .form-group-half { flex: 1; }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--text-main);
    }
    .required { color: #dc2626; }

    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 0.625rem 1rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-main);
        color: var(--text-main);
        font-size: 0.875rem;
        transition: border-color 0.2s;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
    }
    .form-textarea { resize: vertical; min-height: 100px; }

    .form-hint {
        margin-top: 0.375rem;
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.875rem;
        padding: 0.625rem 0;
    }
    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
    }

    .form-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border);
    }

    .btn-submit {
        padding: 0.75rem 1.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        font-size: 0.875rem;
        transition: background 0.2s;
    }
    .btn-submit:hover { background: var(--primary-dark, #5b4dc4); }

    .btn-cancel {
        padding: 0.75rem 1.5rem;
        color: var(--text-secondary);
        border: 1px solid var(--border);
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .btn-cancel:hover {
        color: var(--text-main);
        border-color: var(--text-main);
    }

    @media (max-width: 768px) {
        .form-row { flex-direction: column; gap: 0; }
        .form-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
    }
</style>
@endpush
