@extends('layouts.app')

@section('title', 'Сообщения - Esxatos')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <div class="messages-header">
            <h1 class="messages-title">
                Сообщения
                @if($unreadCount > 0)
                    <span class="unread-badge">{{ $unreadCount }}</span>
                @endif
            </h1>
            <a href="{{ route('messages.create') }}" class="btn-primary">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Написать
            </a>
        </div>

        @if($threads->isEmpty())
            <div class="messages-empty">
                <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <p>У вас пока нет сообщений</p>
                <a href="{{ route('messages.create') }}" class="btn-primary">Написать первое сообщение</a>
            </div>
        @else
            <div class="threads-list">
                @foreach($threads as $thread)
                    @php
                        $otherParticipant = $thread->participants->where('id', '!=', auth()->id())->first();
                        $isUnread = $thread->isUnreadFor(auth()->user());
                    @endphp
                    <a href="{{ route('messages.show', $thread) }}" class="thread-item {{ $isUnread ? 'unread' : '' }}">
                        <div class="thread-avatar">
                            {{ $otherParticipant ? mb_substr($otherParticipant->name, 0, 1) : '?' }}
                        </div>
                        <div class="thread-content">
                            <div class="thread-header">
                                <span class="thread-participant">{{ $otherParticipant->name ?? 'Удаленный пользователь' }}</span>
                                <span class="thread-date">{{ $thread->latestMessage?->created_at?->diffForHumans() }}</span>
                            </div>
                            <div class="thread-subject">{{ $thread->subject }}</div>
                            <div class="thread-preview">{{ Str::limit(strip_tags($thread->latestMessage?->body), 80) }}</div>
                        </div>
                        @if($isUnread)
                            <div class="thread-unread-dot"></div>
                        @endif
                    </a>
                @endforeach
            </div>

            @if($threads->hasPages())
            <div class="messages-pagination">
                <div class="pagination-wrapper">
                    @if($threads->onFirstPage())
                        <span class="pagination-link disabled">&laquo; Назад</span>
                    @else
                        <a href="{{ $threads->previousPageUrl() }}" class="pagination-link">&laquo; Назад</a>
                    @endif

                    <span class="pagination-info">
                        Страница {{ $threads->currentPage() }} из {{ $threads->lastPage() }}
                    </span>

                    @if($threads->hasMorePages())
                        <a href="{{ $threads->nextPageUrl() }}" class="pagination-link">Далее &raquo;</a>
                    @else
                        <span class="pagination-link disabled">Далее &raquo;</span>
                    @endif
                </div>
            </div>
            @endif
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .dashboard-page { display: grid; grid-template-columns: 220px 1fr; gap: 2rem; }
    .dashboard-sidebar { }
    .dashboard-nav { display: flex; flex-direction: column; gap: 0.25rem; }
    .dashboard-nav-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 8px; color: var(--text-secondary); font-weight: 500; transition: all 0.2s; text-decoration: none; }
    .dashboard-nav-link:hover { background: var(--bg-secondary); color: var(--text-main); }
    .dashboard-nav-link.active { background: var(--primary); color: white; }
    .nav-badge { background: #ef4444; color: white; font-size: 0.75rem; padding: 0.125rem 0.5rem; border-radius: 9999px; margin-left: auto; }
    .dashboard-nav-link.logout { width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-size: 1rem; font-family: inherit; }
    .dashboard-nav-link.logout:hover { background: #fee2e2; color: #dc2626; }
    .dashboard-nav-form { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); }
    .dashboard-content { }

    .messages-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; }
    .messages-title { font-size: 1.75rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 0.75rem; }
    .unread-badge { background: var(--primary); color: white; font-size: 0.875rem; padding: 0.25rem 0.625rem; border-radius: 9999px; }
    .btn-primary { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background 0.2s; text-decoration: none; }
    .btn-primary:hover { background: var(--primary-hover); }

    .messages-empty { text-align: center; padding: 4rem 2rem; background: var(--bg-card); border-radius: 12px; }
    .messages-empty svg { color: var(--text-muted); margin-bottom: 1rem; }
    .messages-empty p { color: var(--text-secondary); margin-bottom: 1.5rem; }

    .threads-list { background: var(--bg-card); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px var(--shadow); }
    .thread-item { display: flex; align-items: center; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); transition: background 0.2s; text-decoration: none; color: inherit; }
    .thread-item:last-child { border-bottom: none; }
    .thread-item:hover { background: var(--bg-secondary); }
    .thread-item.unread { background: rgba(59, 130, 246, 0.05); }
    .thread-item.unread .thread-subject { font-weight: 600; }

    .thread-avatar { width: 48px; height: 48px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.25rem; flex-shrink: 0; }
    .thread-content { flex: 1; min-width: 0; }
    .thread-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem; }
    .thread-participant { font-weight: 600; color: var(--text-main); }
    .thread-date { font-size: 0.8rem; color: var(--text-muted); }
    .thread-subject { color: var(--text-main); margin-bottom: 0.25rem; }
    .thread-preview { font-size: 0.9rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .thread-unread-dot { width: 10px; height: 10px; background: var(--primary); border-radius: 50%; flex-shrink: 0; }

    .messages-pagination { margin-top: 1.5rem; }
    .pagination-wrapper { display: flex; justify-content: center; align-items: center; gap: 1rem; }
    .pagination-link { padding: 0.5rem 1rem; background: var(--bg-card); border: 1px solid var(--border); border-radius: 6px; color: var(--text-main); font-weight: 500; transition: all 0.2s; text-decoration: none; }
    .pagination-link:hover:not(.disabled) { background: var(--primary); color: white; border-color: var(--primary); }
    .pagination-link.disabled { color: var(--text-muted); cursor: not-allowed; }
    .pagination-info { color: var(--text-secondary); font-size: 0.9rem; }

    @media (max-width: 768px) {
        .dashboard-page { grid-template-columns: 1fr; }
        .dashboard-sidebar { order: 2; }
        .dashboard-nav { flex-direction: row; flex-wrap: wrap; }
        .dashboard-nav-form { margin: 0; padding: 0; border: none; }
        .messages-header { flex-direction: column; gap: 1rem; align-items: stretch; }
        .thread-avatar { width: 40px; height: 40px; font-size: 1rem; }
    }
</style>
@endpush
