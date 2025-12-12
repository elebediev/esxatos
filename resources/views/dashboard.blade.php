@extends('layouts.app')

@section('title', 'Личный кабинет - Esxatos')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <h1 class="dashboard-title">Личный кабинет</h1>

        <div class="dashboard-welcome">
            <div class="welcome-icon">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="welcome-text">
                <h2>Добро пожаловать, {{ auth()->user()->name }}!</h2>
                <p>Вы успешно вошли в систему.</p>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value">0</span>
                    <span class="stat-label">Избранных книг</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value">0</span>
                    <span class="stat-label">Комментариев</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value">0</span>
                    <span class="stat-label">Оценок</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('partials.dashboard-styles')
<style>
    .dashboard-welcome { display: flex; align-items: center; gap: 1.5rem; background: var(--bg-card); padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 1px 3px var(--shadow); }
    .welcome-icon { color: var(--primary); }
    .welcome-text h2 { font-size: 1.25rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem; }
    .welcome-text p { color: var(--text-secondary); }
    .dashboard-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    .stat-card { display: flex; align-items: center; gap: 1rem; background: var(--bg-card); padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px var(--shadow); }
    .stat-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; background: var(--bg-secondary); border-radius: 10px; color: var(--primary); }
    .stat-info { display: flex; flex-direction: column; }
    .stat-value { font-size: 1.5rem; font-weight: 700; color: var(--text-main); }
    .stat-label { font-size: 0.875rem; color: var(--text-muted); }
    @media (max-width: 1024px) { .dashboard-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) { .dashboard-stats { grid-template-columns: 1fr; } }
</style>
@endpush
