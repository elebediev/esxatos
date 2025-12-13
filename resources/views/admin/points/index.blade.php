@extends('layouts.app')

@section('title', 'Балансы пользователей - Админ-панель')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <h1 class="dashboard-title">Система баллов</h1>

        <div class="points-stats">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($stats['total_users'], 0, '.', ' ') }}</div>
                <div class="stat-label">Пользователей с баллами</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($stats['total_points'], 0, '.', ' ') }}</div>
                <div class="stat-label">Общее количество баллов</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($stats['active_subscriptions'], 0, '.', ' ') }}</div>
                <div class="stat-label">Активных подписок</div>
            </div>
        </div>

        <div class="points-actions">
            <a href="{{ route('admin.points.transactions') }}" class="action-btn">Все транзакции</a>
            <a href="{{ route('admin.points.categories') }}" class="action-btn secondary">Категории</a>
        </div>

        <div class="users-filters">
            <form method="GET" action="{{ route('admin.points.index') }}" class="filters-form">
                <div class="filter-group">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Поиск по имени или email..." class="filter-input">
                </div>
                <div class="filter-group">
                    <input type="number" name="min_points" value="{{ request('min_points') }}" placeholder="Мин. баллов" class="filter-input">
                </div>
                <div class="filter-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="show_all" value="1" {{ request('show_all') ? 'checked' : '' }}>
                        Показать всех
                    </label>
                </div>
                <button type="submit" class="filter-btn">Найти</button>
                @if(request()->hasAny(['search', 'min_points', 'show_all']))
                    <a href="{{ route('admin.points.index') }}" class="filter-reset">Сбросить</a>
                @endif
            </form>
        </div>

        <div class="users-table-wrap">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Баланс</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user) }}" class="user-name-link">{{ $user->name }}</a>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="points-badge {{ $user->total_points >= 0 ? 'positive' : 'negative' }}">
                                {{ number_format($user->total_points, 0, '.', ' ') }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.points.user-history', $user) }}" class="btn-small">История</a>
                                <a href="{{ route('admin.points.create-transaction', $user) }}" class="btn-small primary">+ Баллы</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="empty-message">Пользователи не найдены</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <nav class="pagination-wrapper">
            <div class="pagination">
                @if($users->onFirstPage())
                    <span class="pagination-link disabled">Назад</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="pagination-link">Назад</a>
                @endif

                @foreach($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}" class="pagination-link {{ $page == $users->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach

                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="pagination-link">Дальше</a>
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
    .dashboard-title { margin-bottom: 1.5rem; }

    .points-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: var(--bg-card);
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 1px 3px var(--shadow);
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        white-space: nowrap;
    }
    .stat-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .points-actions {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .action-btn {
        padding: 0.625rem 1.25rem;
        background: var(--primary);
        color: white;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.2s;
    }
    .action-btn:hover { background: var(--primary-dark, #5b4dc4); }
    .action-btn.secondary {
        background: var(--bg-secondary);
        color: var(--text-main);
        border: 1px solid var(--border);
    }
    .action-btn.secondary:hover { background: var(--bg-card); }

    .users-filters { margin-bottom: 1.5rem; }
    .filters-form { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
    .filter-group { flex: 1; min-width: 150px; max-width: 250px; }
    .filter-input {
        width: 100%;
        padding: 0.625rem 1rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-main);
        font-size: 0.875rem;
    }
    .filter-input:focus { outline: none; border-color: var(--primary); }
    .filter-btn {
        padding: 0.625rem 1.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
    }
    .filter-reset {
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.875rem;
    }
    .filter-reset:hover { color: var(--primary); }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-main);
        cursor: pointer;
    }

    .users-table-wrap {
        background: var(--bg-card);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px var(--shadow);
    }
    .users-table { width: 100%; border-collapse: collapse; }
    .users-table th {
        text-align: left;
        padding: 1rem;
        background: var(--bg-secondary);
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
    }
    .users-table td {
        padding: 1rem;
        border-top: 1px solid var(--border);
        font-size: 0.875rem;
        color: var(--text-main);
    }
    .users-table tbody tr:hover { background: var(--bg-secondary); }

    .user-name-link { color: var(--text-main); font-weight: 500; }
    .user-name-link:hover { color: var(--primary); }

    .points-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.875rem;
        white-space: nowrap;
    }
    .points-badge.positive { background: #d1fae5; color: #065f46; }
    .points-badge.negative { background: #fee2e2; color: #991b1b; }

    .category-balance {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        background: var(--bg-secondary);
        border-radius: 4px;
        font-size: 0.75rem;
        margin-right: 0.25rem;
        margin-bottom: 0.25rem;
        white-space: nowrap;
    }

    .action-buttons { display: flex; gap: 0.5rem; }
    .btn-small {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        text-decoration: none;
        background: var(--bg-secondary);
        color: var(--text-main);
        border: 1px solid var(--border);
    }
    .btn-small:hover { background: var(--bg-card); border-color: var(--primary); }
    .btn-small.primary { background: var(--primary); color: white; border-color: var(--primary); }
    .btn-small.primary:hover { background: var(--primary-dark, #5b4dc4); }

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
