@extends('layouts.app')

@section('title', 'Новое сообщение - Esxatos')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <div class="page-header-bar">
            <a href="{{ route('messages.index') }}" class="back-link">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Назад к сообщениям
            </a>
        </div>

        <div class="compose-container">
            <h1 class="compose-title">Новое сообщение</h1>

            <form action="{{ route('messages.store') }}" method="POST" class="compose-form">
                @csrf

                <div class="form-group">
                    <label for="recipient_search" class="form-label">Кому</label>
                    <div class="recipient-field">
                        <input type="hidden" name="recipient_id" id="recipient_id" value="{{ $recipient?->id }}">
                        <input type="text" id="recipient_search" class="form-input @error('recipient_id') error @enderror"
                               placeholder="Начните вводить имя пользователя..."
                               value="{{ $recipient?->name }}"
                               autocomplete="off"
                               {{ $recipient ? 'readonly' : '' }}>
                        @if($recipient)
                            <button type="button" class="clear-recipient" onclick="clearRecipient()">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                        <div id="recipient_results" class="recipient-results"></div>
                    </div>
                    @error('recipient_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="subject" class="form-label">Тема</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject', $subject ?? '') }}" required
                           class="form-input @error('subject') error @enderror"
                           placeholder="Тема сообщения">
                    @error('subject')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="body" class="form-label">Сообщение</label>
                    <textarea name="body" id="body" rows="8" required
                              class="form-textarea @error('body') error @enderror"
                              placeholder="Текст сообщения...">{{ old('body') }}</textarea>
                    @error('body')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-actions">
                    <a href="{{ route('messages.index') }}" class="btn-secondary">Отмена</a>
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
@endsection

@push('styles')
@include('partials.dashboard-styles')
<style>
    .page-header-bar { margin-bottom: 1.5rem; }
    .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-secondary); font-weight: 500; text-decoration: none; }
    .back-link:hover { color: var(--primary); }

    .compose-container { background: var(--bg-card); border-radius: 12px; box-shadow: 0 1px 3px var(--shadow); padding: 2rem; max-width: 600px; }
    .compose-title { font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 2rem; }

    .form-group { margin-bottom: 1.5rem; }
    .form-label { display: block; font-size: 0.9rem; font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; }
    .form-input { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem; background: var(--bg-card); color: var(--text-main); }
    .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .form-input.error { border-color: #ef4444; }
    .form-textarea { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem; background: var(--bg-card); color: var(--text-main); resize: vertical; font-family: inherit; }
    .form-textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .form-textarea.error { border-color: #ef4444; }
    .form-error { color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; }

    .recipient-field { position: relative; }
    .recipient-results { position: absolute; top: 100%; left: 0; right: 0; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 4px 6px var(--shadow); z-index: 100; display: none; max-height: 200px; overflow-y: auto; }
    .recipient-results.active { display: block; }
    .recipient-result { padding: 0.75rem 1rem; cursor: pointer; transition: background 0.2s; }
    .recipient-result:hover { background: var(--bg-secondary); }
    .recipient-result-name { font-weight: 500; color: var(--text-main); }
    .recipient-result-email { font-size: 0.85rem; color: var(--text-muted); }
    .clear-recipient { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0.25rem; }
    .clear-recipient:hover { color: var(--text-main); }

    .form-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; }
    .btn-primary { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background 0.2s; text-decoration: none; }
    .btn-primary:hover { background: var(--primary-hover); }
    .btn-secondary { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: var(--bg-secondary); color: var(--text-main); border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: background 0.2s; }
    .btn-secondary:hover { background: var(--border); }

    @media (max-width: 768px) {
        .compose-container { max-width: none; }
    }
</style>
@endpush

@push('scripts')
<script>
    const searchInput = document.getElementById('recipient_search');
    const recipientId = document.getElementById('recipient_id');
    const resultsDiv = document.getElementById('recipient_results');
    let searchTimeout;

    if (!searchInput.readOnly) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                resultsDiv.classList.remove('active');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('messages.search-users') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(users => {
                        if (users.length === 0) {
                            resultsDiv.innerHTML = '<div class="recipient-result"><span class="recipient-result-name">Пользователь не найден</span></div>';
                        } else {
                            resultsDiv.innerHTML = users.map(user => `
                                <div class="recipient-result" data-id="${user.id}" data-name="${user.name}">
                                    <div class="recipient-result-name">${user.name}</div>
                                    <div class="recipient-result-email">${user.email}</div>
                                </div>
                            `).join('');
                        }
                        resultsDiv.classList.add('active');
                    });
            }, 300);
        });

        resultsDiv.addEventListener('click', function(e) {
            const result = e.target.closest('.recipient-result');
            if (result && result.dataset.id) {
                recipientId.value = result.dataset.id;
                searchInput.value = result.dataset.name;
                searchInput.readOnly = true;
                resultsDiv.classList.remove('active');

                // Add clear button
                const clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'clear-recipient';
                clearBtn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                clearBtn.onclick = clearRecipient;
                searchInput.parentNode.appendChild(clearBtn);
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.recipient-field')) {
                resultsDiv.classList.remove('active');
            }
        });
    }

    function clearRecipient() {
        recipientId.value = '';
        searchInput.value = '';
        searchInput.readOnly = false;
        searchInput.focus();
        const clearBtn = document.querySelector('.clear-recipient');
        if (clearBtn) clearBtn.remove();
    }
</script>
@endpush
