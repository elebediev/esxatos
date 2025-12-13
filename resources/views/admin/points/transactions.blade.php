@extends('layouts.app')

@section('title', 'Все транзакции - Админ-панель')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <div class="page-header">
            <h1 class="dashboard-title">Все транзакции</h1>
            <a href="{{ route('admin.points.index') }}" class="action-btn secondary">&larr; Балансы</a>
        </div>

        <div class="filters-section">
            <form method="GET" action="{{ route('admin.points.transactions') }}" class="filters-form">
                <div class="filter-group">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Поиск по пользователю..." class="filter-input">
                </div>
                <div class="filter-group">
                    <select name="category" class="filter-select">
                        <option value="">Все категории</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <select name="operation" class="filter-select">
                        <option value="">Все операции</option>
                        <option value="admin" {{ request('operation') === 'admin' ? 'selected' : '' }}>Админ</option>
                        <option value="expiry" {{ request('operation') === 'expiry' ? 'selected' : '' }}>Истечение</option>
                        <option value="download" {{ request('operation') === 'download' ? 'selected' : '' }}>Скачивание</option>
                        <option value="refund" {{ request('operation') === 'refund' ? 'selected' : '' }}>Возврат</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="type" class="filter-select">
                        <option value="">Все типы</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Начисление (+)</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Списание (-)</option>
                    </select>
                </div>
                <button type="submit" class="filter-btn">Фильтровать</button>
                @if(request()->hasAny(['search', 'category', 'operation', 'type']))
                    <a href="{{ route('admin.points.transactions') }}" class="filter-reset">Сбросить</a>
                @endif
            </form>
        </div>

        <div class="transactions-table-wrap">
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Пользователь</th>
                        <th>Баллы</th>
                        <th>Категория</th>
                        <th>Операция</th>
                        <th>Описание</th>
                        <th>Админ</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                    <tr class="{{ $txn->status === 'cancelled' ? 'cancelled' : '' }}">
                        <td>{{ $txn->id }}</td>
                        <td>
                            <div class="date-cell">
                                {{ $txn->created_at?->format('d.m.Y') ?? '—' }}
                                <span class="time">{{ $txn->created_at?->format('H:i') ?? '' }}</span>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.points.user-history', $txn->user) }}" class="user-link">
                                {{ $txn->user->name }}
                            </a>
                        </td>
                        <td>
                            <span class="points-value {{ $txn->points >= 0 ? 'positive' : 'negative' }}">
                                {{ $txn->points >= 0 ? '+' : '' }}{{ number_format($txn->points, 0, '.', ' ') }}
                            </span>
                        </td>
                        <td>{{ $txn->category?->name ?? '—' }}</td>
                        <td>
                            <span class="operation-badge operation-{{ $txn->operation }}">
                                {{ $txn->operation }}
                            </span>
                        </td>
                        <td class="description-cell">
                            {{ Str::limit($txn->description, 40) }}
                        </td>
                        <td>{{ $txn->approver?->name ?? '—' }}</td>
                        <td>
                            @if($txn->status === 'cancelled')
                                <span class="status-badge cancelled">Отменено</span>
                            @elseif($txn->is_expired)
                                <span class="status-badge expired">Истекло</span>
                            @else
                                <span class="status-badge active">Активно</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="empty-message">Транзакции не найдены</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
        <nav class="pagination-wrapper">
            <div class="pagination">
                @if($transactions->onFirstPage())
                    <span class="pagination-link disabled">Назад</span>
                @else
                    <a href="{{ $transactions->previousPageUrl() }}" class="pagination-link">Назад</a>
                @endif

                @foreach($transactions->getUrlRange(max(1, $transactions->currentPage() - 2), min($transactions->lastPage(), $transactions->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}" class="pagination-link {{ $page == $transactions->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach

                @if($transactions->hasMorePages())
                    <a href="{{ $transactions->nextPageUrl() }}" class="pagination-link">Дальше</a>
                @else
                    <span class="pagination-link disabled">Дальше</span>
                @endif
            </div>
        </nav>
        @endif
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

    .filters-section { margin-bottom: 1.5rem; }
    .filters-form { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
    .filter-group { min-width: 150px; }
    .filter-input, .filter-select {
        width: 100%;
        padding: 0.625rem 1rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-main);
        font-size: 0.875rem;
    }
    .filter-btn {
        padding: 0.625rem 1.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }
    .filter-reset { color: var(--text-secondary); text-decoration: none; font-size: 0.875rem; }

    .transactions-table-wrap {
        background: var(--bg-card);
        border-radius: 12px;
        overflow-x: auto;
        box-shadow: 0 1px 3px var(--shadow);
    }
    .transactions-table { width: 100%; border-collapse: collapse; min-width: 900px; }
    .transactions-table th {
        text-align: left;
        padding: 1rem;
        background: var(--bg-secondary);
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
    }
    .transactions-table td {
        padding: 0.75rem 1rem;
        border-top: 1px solid var(--border);
        font-size: 0.875rem;
    }
    .transactions-table tr.cancelled { opacity: 0.6; }
    .transactions-table tbody tr:hover { background: var(--bg-secondary); }

    .date-cell .time { display: block; font-size: 0.75rem; color: var(--text-secondary); }
    .user-link { color: var(--primary); font-weight: 500; text-decoration: none; }
    .user-link:hover { text-decoration: underline; }

    .points-value { font-weight: 600; white-space: nowrap; }
    .points-value.positive { color: #059669; }
    .points-value.negative { color: #dc2626; }

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

    .description-cell { max-width: 200px; }

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

    .empty-message { text-align: center; color: var(--text-secondary); padding: 3rem !important; }

    .pagination-wrapper { display: flex; justify-content: center; margin-top: 1.5rem; }
    .pagination { display: flex; align-items: center; gap: 0.5rem; }
    .pagination-link {
        display: flex; align-items: center; justify-content: center;
        min-width: 40px; height: 40px; padding: 0 0.75rem;
        border: 1px solid var(--border); border-radius: 8px;
        color: var(--text-secondary); font-size: 0.95rem; font-weight: 500;
        background: var(--bg-card); text-decoration: none;
    }
    .pagination-link:hover:not(.disabled):not(.active) { border-color: var(--primary); color: var(--primary); }
    .pagination-link.active { background: var(--primary); border-color: var(--primary); color: white; }
    .pagination-link.disabled { color: var(--text-muted); cursor: not-allowed; opacity: 0.5; }
</style>
@endpush
