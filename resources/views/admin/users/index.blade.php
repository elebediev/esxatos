@extends('layouts.app')

@section('title', 'Пользователи - Админ-панель')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <h1 class="dashboard-title">Пользователи</h1>

        <div class="users-filters">
            <form method="GET" action="{{ route('admin.users.index') }}" class="filters-form">
                <div class="filter-group">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Поиск по имени или email..." class="filter-input">
                </div>
                <div class="filter-group">
                    <select name="role" class="filter-select">
                        <option value="">Все роли</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="club" {{ request('role') === 'club' ? 'selected' : '' }}>Club</option>
                        <option value="aide" {{ request('role') === 'aide' ? 'selected' : '' }}>Aide</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="active" class="filter-select">
                        <option value="">Все статусы</option>
                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Активные</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Заблокированные</option>
                    </select>
                </div>
                <button type="submit" class="filter-btn">Найти</button>
                @if(request()->hasAny(['search', 'role', 'active']))
                    <a href="{{ route('admin.users.index') }}" class="filter-reset">Сбросить</a>
                @endif
            </form>
        </div>

        <div class="users-stats">
            <span>Всего: {{ $users->total() }} пользователей</span>
        </div>

        <div class="users-table-wrap">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Роли</th>
                        <th>Статус</th>
                        <th>Регистрация</th>
                        <th>Последний вход</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <div class="user-name">
                                {{ $user->name }}
                                @if($user->drupal_uid)
                                    <span class="drupal-badge" title="Drupal UID: {{ $user->drupal_uid }}">D</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="role-badge role-{{ $role->name }}">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="status-badge status-active">Активен</span>
                            @else
                                <span class="status-badge status-blocked">Заблокирован</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at?->format('d.m.Y') }}</td>
                        <td>{{ $user->last_login_at?->format('d.m.Y H:i') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="empty-message">Пользователи не найдены</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="users-pagination">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
@include('partials.dashboard-styles')
<style>
    .dashboard-title { margin-bottom: 1.5rem; }

    .users-filters { margin-bottom: 1.5rem; }
    .filters-form { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
    .filter-group { flex: 1; min-width: 150px; max-width: 250px; }
    .filter-input, .filter-select {
        width: 100%;
        padding: 0.625rem 1rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-main);
        font-size: 0.875rem;
    }
    .filter-input:focus, .filter-select:focus {
        outline: none;
        border-color: var(--primary);
    }
    .filter-btn {
        padding: 0.625rem 1.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.2s;
    }
    .filter-btn:hover { background: var(--primary-dark, #5b4dc4); }
    .filter-reset {
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.875rem;
    }
    .filter-reset:hover { color: var(--primary); }

    .users-stats {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .users-table-wrap {
        background: var(--bg-card);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px var(--shadow);
    }
    .users-table {
        width: 100%;
        border-collapse: collapse;
    }
    .users-table th {
        text-align: left;
        padding: 1rem;
        background: var(--bg-secondary);
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .users-table td {
        padding: 1rem;
        border-top: 1px solid var(--border);
        font-size: 0.875rem;
        color: var(--text-main);
    }
    .users-table tbody tr:hover {
        background: var(--bg-secondary);
    }

    .user-name { display: flex; align-items: center; gap: 0.5rem; font-weight: 500; }
    .drupal-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        background: #3b82f6;
        color: white;
        font-size: 0.625rem;
        font-weight: 700;
        border-radius: 4px;
    }

    .role-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-right: 0.25rem;
    }
    .role-admin { background: #fef3c7; color: #92400e; }
    .role-club { background: #dbeafe; color: #1e40af; }
    .role-aide { background: #d1fae5; color: #065f46; }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .status-active { background: #d1fae5; color: #065f46; }
    .status-blocked { background: #fee2e2; color: #991b1b; }

    .empty-message {
        text-align: center;
        color: var(--text-secondary);
        padding: 3rem !important;
    }

    .users-pagination {
        margin-top: 1.5rem;
    }

    @media (max-width: 1024px) {
        .users-table-wrap { overflow-x: auto; }
        .users-table { min-width: 800px; }
    }
    @media (max-width: 768px) {
        .filter-group { max-width: none; }
    }
</style>
@endpush
