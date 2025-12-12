@extends('layouts.app')

@section('title', $thread->subject . ' - Сообщения - Esxatos')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <div class="thread-header-bar">
            <a href="{{ route('messages.index') }}" class="back-link">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Назад к сообщениям
            </a>
            <form action="{{ route('messages.destroy', $thread) }}" method="POST" class="delete-form" onsubmit="return confirm('Удалить переписку?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete" title="Удалить переписку">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>

        <div class="thread-container">
            <div class="thread-subject-header">
                <h1>{{ $thread->subject }}</h1>
                <div class="thread-participants">
                    @foreach($participants as $participant)
                        <span class="participant-tag">{{ $participant->name }}</span>
                    @endforeach
                </div>
            </div>

            <div class="messages-list">
                @foreach($messages as $message)
                    <div class="message-item {{ $message->sender_id === auth()->id() ? 'sent' : 'received' }}">
                        <div class="message-avatar">
                            {{ mb_substr($message->sender->name ?? '?', 0, 1) }}
                        </div>
                        <div class="message-bubble">
                            <div class="message-header">
                                <span class="message-sender">{{ $message->sender->name ?? 'Удаленный пользователь' }}</span>
                                <span class="message-time">{{ $message->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                            <div class="message-body">{!! nl2br(e(strip_tags($message->body))) !!}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="reply-form">
                <form action="{{ route('messages.reply', $thread) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <textarea name="body" rows="4" placeholder="Напишите ответ..." required class="form-textarea @error('body') error @enderror">{{ old('body') }}</textarea>
                        @error('body')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Отправить
                        </button>
                    </div>
                </form>
            </div>
        </div>
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

    .thread-header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-secondary); font-weight: 500; text-decoration: none; }
    .back-link:hover { color: var(--primary); }
    .btn-delete { background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0.5rem; border-radius: 6px; transition: all 0.2s; }
    .btn-delete:hover { background: #fee2e2; color: #dc2626; }

    .thread-container { background: var(--bg-card); border-radius: 12px; box-shadow: 0 1px 3px var(--shadow); overflow: hidden; }
    .thread-subject-header { padding: 1.5rem; border-bottom: 1px solid var(--border); }
    .thread-subject-header h1 { font-size: 1.25rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; }
    .thread-participants { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .participant-tag { background: var(--bg-secondary); color: var(--text-secondary); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.8rem; }

    .messages-list { padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
    .message-item { display: flex; gap: 0.75rem; max-width: 85%; }
    .message-item.sent { margin-left: auto; flex-direction: row-reverse; }
    .message-item.received { margin-right: auto; }

    .message-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.9rem; flex-shrink: 0; }
    .message-item.sent .message-avatar { background: var(--text-secondary); }

    .message-bubble { background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: 12px; }
    .message-item.sent .message-bubble { background: var(--primary); color: white; }
    .message-item.sent .message-header { color: rgba(255,255,255,0.8); }

    .message-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 0.5rem; font-size: 0.8rem; }
    .message-sender { font-weight: 600; color: var(--text-main); }
    .message-item.sent .message-sender { color: white; }
    .message-time { color: var(--text-muted); }
    .message-item.sent .message-time { color: rgba(255,255,255,0.7); }

    .message-body { line-height: 1.5; word-wrap: break-word; }

    .reply-form { padding: 1.5rem; border-top: 1px solid var(--border); background: var(--bg-secondary); }
    .form-textarea { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem; background: var(--bg-card); color: var(--text-main); resize: vertical; font-family: inherit; }
    .form-textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .form-textarea.error { border-color: #ef4444; }
    .form-error { color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-actions { display: flex; justify-content: flex-end; }
    .btn-primary { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background 0.2s; text-decoration: none; }
    .btn-primary:hover { background: var(--primary-hover); }

    @media (max-width: 768px) {
        .dashboard-page { grid-template-columns: 1fr; }
        .dashboard-sidebar { order: 2; }
        .dashboard-nav { flex-direction: row; flex-wrap: wrap; }
        .dashboard-nav-form { margin: 0; padding: 0; border: none; }
        .message-item { max-width: 95%; }
        .messages-list { padding: 1rem; }
        .reply-form { padding: 1rem; }
    }
</style>
@endpush
