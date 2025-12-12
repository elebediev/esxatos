@extends('layouts.app')

@section('title', $user->name . ' - Пользователи - Админ-панель')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <div class="page-header">
            <a href="{{ route('admin.users.index') }}" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Назад к списку
            </a>
            <h1 class="dashboard-title">{{ $user->name }}</h1>
        </div>

        <div class="user-detail-grid">
            {{-- Main Info Card --}}
            <div class="detail-card">
                <h2 class="card-title">Основная информация</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">ID</span>
                        <span class="info-value">{{ $user->id }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Drupal UID</span>
                        <span class="info-value">{{ $user->drupal_uid ?? '—' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Имя пользователя</span>
                        <span class="info-value">{{ $user->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Имя</span>
                        <span class="info-value">{{ $user->first_name ?? '—' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Фамилия</span>
                        <span class="info-value">{{ $user->last_name ?? '—' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value">
                            <a href="mailto:{{ $user->email }}" class="email-link">{{ $user->email }}</a>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Статус</span>
                        <span class="info-value">
                            @if($user->is_active)
                                <span class="status-badge status-active">Активен</span>
                            @else
                                <span class="status-badge status-blocked">Заблокирован</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Роли</span>
                        <span class="info-value">
                            @forelse($user->roles as $role)
                                <span class="role-badge role-{{ $role->name }}">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted">Нет ролей</span>
                            @endforelse
                        </span>
                    </div>
                </div>
            </div>

            {{-- Activity Card --}}
            <div class="detail-card">
                <h2 class="card-title">Активность</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Дата регистрации</span>
                        <span class="info-value">{{ $user->created_at?->format('d.m.Y H:i') ?? '—' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Последний вход</span>
                        <span class="info-value">{{ $user->last_login_at?->format('d.m.Y H:i') ?? 'Никогда' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email подтвержден</span>
                        <span class="info-value">{{ $user->email_verified_at?->format('d.m.Y H:i') ?? 'Нет' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Последнее обновление</span>
                        <span class="info-value">{{ $user->updated_at?->format('d.m.Y H:i') ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Stats Card --}}
            <div class="detail-card">
                <h2 class="card-title">Статистика</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-value">{{ $booksCount }}</span>
                        <span class="stat-label">Загруженных книг</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $messageThreadsCount }}</span>
                        <span class="stat-label">Переписок</span>
                    </div>
                </div>
            </div>

            {{-- Settings Card --}}
            <div class="detail-card">
                <h2 class="card-title">Настройки</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Язык</span>
                        <span class="info-value">{{ $user->language ?? 'ru' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Часовой пояс</span>
                        <span class="info-value">{{ $user->timezone ?? 'UTC' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Login History --}}
        <div class="detail-card detail-card-full">
            <h2 class="card-title">История входов (последние 20)</h2>
            @if($loginLogs->count() > 0)
                <div class="login-logs-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Дата/время</th>
                                <th>IP-адрес</th>
                                <th>Устройство</th>
                                <th>Браузер</th>
                                <th>ОС</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loginLogs as $log)
                                <tr>
                                    <td>{{ $log->logged_in_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <code class="ip-address">{{ $log->ip_address }}</code>
                                    </td>
                                    <td>
                                        <span class="device-badge device-{{ $log->device_type }}">
                                            @if($log->device_type === 'mobile')
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                            @elseif($log->device_type === 'tablet')
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                            @else
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                            @endif
                                            {{ ucfirst($log->device_type ?? 'desktop') }}
                                        </span>
                                    </td>
                                    <td>{{ $log->browser ?? '—' }}{{ $log->browser_version ? ' ' . $log->browser_version : '' }}</td>
                                    <td>{{ $log->platform ?? '—' }}{{ $log->platform_version ? ' ' . $log->platform_version : '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="no-data">Нет записей о входах</p>
            @endif
        </div>

        {{-- Actions --}}
        <div class="user-actions">
            <a href="{{ route('messages.create', ['to' => $user->id]) }}" class="action-btn action-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                Написать сообщение
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('partials.dashboard-styles')
<style>
    .page-header { margin-bottom: 2rem; }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 1rem;
        transition: color 0.2s;
    }
    .back-link:hover { color: var(--primary); }
    .dashboard-title { margin: 0; }

    .user-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .detail-card {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px var(--shadow);
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0 0 1.25rem 0;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border);
    }

    .info-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .info-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
        flex-shrink: 0;
    }

    .info-value {
        font-size: 0.875rem;
        color: var(--text-main);
        font-weight: 500;
        text-align: right;
    }

    .email-link {
        color: var(--primary);
        transition: color 0.2s;
    }
    .email-link:hover { color: var(--primary-hover); text-decoration: underline; }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .stat-item {
        text-align: center;
        padding: 1rem;
        background: var(--bg-secondary);
        border-radius: 8px;
    }

    .stat-value {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .role-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-left: 0.25rem;
    }
    .role-badge:first-child { margin-left: 0; }
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

    .text-muted { color: var(--text-muted); }

    .user-actions {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 1rem;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .action-btn-primary {
        background: var(--primary);
        color: white;
    }
    .action-btn-primary:hover {
        background: var(--primary-hover);
        color: white;
    }

    @media (max-width: 1024px) {
        .user-detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .detail-card-full {
        grid-column: 1 / -1;
    }

    .login-logs-table {
        overflow-x: auto;
    }

    .login-logs-table table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .login-logs-table th,
    .login-logs-table td {
        padding: 0.75rem 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }

    .login-logs-table th {
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: var(--bg-secondary);
    }

    .login-logs-table tr:hover {
        background: var(--bg-secondary);
    }

    .ip-address {
        font-family: monospace;
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
        background: var(--bg-secondary);
        border-radius: 4px;
        color: var(--text-main);
    }

    .device-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    .device-desktop { background: #dbeafe; color: #1e40af; }
    .device-mobile { background: #d1fae5; color: #065f46; }
    .device-tablet { background: #fef3c7; color: #92400e; }

    .no-data {
        color: var(--text-muted);
        text-align: center;
        padding: 2rem;
    }

    @media (max-width: 768px) {
        .info-item {
            flex-direction: column;
            gap: 0.25rem;
        }
        .info-value {
            text-align: left;
        }
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .login-logs-table th,
        .login-logs-table td {
            padding: 0.5rem;
            font-size: 0.75rem;
        }
    }
</style>
@endpush
