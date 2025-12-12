@php
    $currentRoute = Route::currentRouteName();
    $unreadMessages = \App\Models\MessageThread::forUser(auth()->user())->withUnread(auth()->user())->count();
@endphp

<div class="dashboard-sidebar">
    <nav class="dashboard-nav">
        <a href="{{ route('dashboard') }}" class="dashboard-nav-link {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Главная
        </a>
        <a href="{{ route('messages.index') }}" class="dashboard-nav-link {{ str_starts_with($currentRoute, 'messages.') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Сообщения
            @if($unreadMessages > 0)
                <span class="nav-badge">{{ $unreadMessages }}</span>
            @endif
        </a>
        <a href="{{ route('profile.edit') }}" class="dashboard-nav-link {{ $currentRoute === 'profile.edit' ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Профиль
        </a>
        @if(auth()->user()->hasRole('admin'))
        <a href="{{ route('admin.users.index') }}" class="dashboard-nav-link {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Пользователи
        </a>
        <a href="{{ route('admin.cache.index') }}" class="dashboard-nav-link {{ str_starts_with($currentRoute, 'admin.cache') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Кеш
        </a>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="dashboard-nav-form">
            @csrf
            <button type="submit" class="dashboard-nav-link logout">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Выйти
            </button>
        </form>
    </nav>
</div>
