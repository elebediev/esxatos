@extends('layouts.app')

@section('title', 'Редагування транзакції #' . $transaction->id . ' - Админ-панель')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <div class="page-header">
            <div class="header-info">
                <a href="{{ route('admin.points.user-history', $transaction->user) }}" class="back-link">&larr; К истории</a>
                <h1 class="dashboard-title">Редактирование транзакции #{{ $transaction->id }}</h1>
                <p class="subtitle">Пользователь: {{ $transaction->user->name }} ({{ $transaction->user->email }})</p>
            </div>
        </div>

        <div class="transaction-info">
            <div class="info-item">
                <span class="label">Создано:</span>
                <span class="value">{{ $transaction->created_at?->format('d.m.Y H:i') ?? $transaction->drupal_created_at?->format('d.m.Y H:i') ?? '—' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Операция:</span>
                <span class="operation-badge operation-{{ $transaction->operation }}">{{ $transaction->operation }}</span>
            </div>
            @if($transaction->approver)
            <div class="info-item">
                <span class="label">Админ:</span>
                <span class="value">{{ $transaction->approver->name }}</span>
            </div>
            @endif
            <div class="info-item">
                <span class="label">Статус:</span>
                @if($transaction->status === 'cancelled')
                    <span class="status-badge cancelled">Отменено</span>
                @elseif($transaction->is_expired)
                    <span class="status-badge expired">Истекло</span>
                @else
                    <span class="status-badge active">Активно</span>
                @endif
            </div>
        </div>

        <div class="form-card">
            <form method="POST" action="{{ route('admin.points.update-transaction', $transaction) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="points">Количество баллов *</label>
                    <input type="number" name="points" id="points" value="{{ old('points', $transaction->points) }}"
                           class="form-input @error('points') error @enderror"
                           placeholder="Введите число (+ для начисления, - для списания)" required>
                    @error('points')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <span class="help-text">Положительное число - начисление, отрицательное - списание</span>
                </div>

                <div class="form-group">
                    <label for="category_id">Категория</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">- Без категории -</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Описание операции *</label>
                    <textarea name="description" id="description" rows="3"
                              class="form-textarea @error('description') error @enderror"
                              placeholder="Укажите причину начисления/списания" required>{{ old('description', $transaction->description) }}</textarea>
                    @error('description')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="expires_at">Дата истечения (необязательно)</label>
                    <input type="date" name="expires_at" id="expires_at"
                           value="{{ old('expires_at', $transaction->expires_at?->format('Y-m-d')) }}"
                           class="form-input @error('expires_at') error @enderror">
                    @error('expires_at')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <span class="help-text">Если указано, баллы автоматически спишутся после этой даты</span>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.points.user-history', $transaction->user) }}" class="btn-secondary">Отмена</a>
                    <button type="submit" class="btn-primary">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('partials.dashboard-styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .back-link {
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        display: inline-block;
    }
    .back-link:hover { color: var(--primary); }
    .dashboard-title { margin: 0; }
    .subtitle { color: var(--text-secondary); margin-top: 0.5rem; }

    .transaction-info {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        background: var(--bg-card);
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px var(--shadow);
    }
    .transaction-info .info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .transaction-info .label {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }
    .transaction-info .value {
        font-size: 0.875rem;
        font-weight: 500;
    }

    .operation-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .operation-admin { background: #dbeafe; color: #1e40af; }
    .operation-expiry { background: #fef3c7; color: #92400e; }
    .operation-download { background: #d1fae5; color: #065f46; }
    .operation-refund { background: #fee2e2; color: #991b1b; }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .status-badge.active { background: #d1fae5; color: #065f46; }
    .status-badge.expired { background: #fef3c7; color: #92400e; }
    .status-badge.cancelled { background: #fee2e2; color: #991b1b; }

    .form-card {
        background: var(--bg-card);
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 1px 3px var(--shadow);
        max-width: 600px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--text-main);
    }
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-secondary);
        color: var(--text-main);
        font-size: 1rem;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
    }
    .form-input.error, .form-textarea.error {
        border-color: #dc2626;
    }
    .form-textarea { resize: vertical; }

    .error-message {
        display: block;
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    .help-text {
        display: block;
        color: var(--text-secondary);
        font-size: 0.75rem;
        margin-top: 0.5rem;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }
    .btn-primary {
        padding: 0.75rem 1.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }
    .btn-primary:hover { background: var(--primary-dark, #5b4dc4); }
    .btn-secondary {
        padding: 0.75rem 1.5rem;
        background: var(--bg-secondary);
        color: var(--text-main);
        border: 1px solid var(--border);
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
    }
    .btn-secondary:hover { background: var(--bg-card); }
</style>
@endpush
