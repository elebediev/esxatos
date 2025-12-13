@extends('layouts.app')

@section('title', 'Начислить баллы: ' . $user->name . ' - Админ-панель')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <div class="page-header">
            <div class="header-info">
                <a href="{{ route('admin.points.user-history', $user) }}" class="back-link">&larr; К истории</a>
                <h1 class="dashboard-title">Начислить/Списать баллы</h1>
                <p class="subtitle">Пользователь: {{ $user->name }} ({{ $user->email }})</p>
            </div>
        </div>

        <div class="user-balances">
            <div class="balance-card total">
                <div class="balance-label">Текущий баланс</div>
                <div class="balance-value">{{ number_format($user->total_points, 0, '.', ' ') }} баллов</div>
            </div>
            @foreach($user->pointBalances as $balance)
            <div class="balance-card">
                <div class="balance-label">{{ $balance->category?->name ?? 'Без категории' }}</div>
                <div class="balance-value">{{ number_format($balance->points, 0, '.', ' ') }}</div>
            </div>
            @endforeach
        </div>

        <div class="form-card">
            <form method="POST" action="{{ route('admin.points.store-transaction', $user) }}">
                @csrf

                <div class="form-group">
                    <label for="points">Количество баллов *</label>
                    <input type="number" name="points" id="points" value="{{ old('points') }}"
                           class="form-input @error('points') error @enderror"
                           placeholder="Введите число (+ для начисления, - для списания)" required>
                    @error('points')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <span class="help-text">Положительное число — начисление, отрицательное — списание</span>
                </div>

                <div class="form-group">
                    <label for="description">Описание операции *</label>
                    <textarea name="description" id="description" rows="3"
                              class="form-textarea @error('description') error @enderror"
                              placeholder="Укажите причину начисления/списания" required>{{ old('description') }}</textarea>
                    @error('description')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="expires_at">Дата истечения (необязательно)</label>
                    <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}"
                           class="form-input @error('expires_at') error @enderror"
                           min="{{ now()->addDay()->format('Y-m-d') }}">
                    @error('expires_at')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <span class="help-text">Если указано, баллы автоматически спишутся после этой даты</span>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.points.user-history', $user) }}" class="btn-secondary">Отмена</a>
                    <button type="submit" class="btn-primary">Сохранить</button>
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

    .user-balances {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .balance-card {
        background: var(--bg-card);
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 1px 3px var(--shadow);
    }
    .balance-card.total {
        background: var(--primary);
        color: white;
    }
    .balance-card.total .balance-label { color: rgba(255,255,255,0.8); }
    .balance-card.total .balance-value { color: white; }
    .balance-label { font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem; }
    .balance-value { font-size: 1.25rem; font-weight: 700; white-space: nowrap; }

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
