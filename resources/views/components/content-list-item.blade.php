@props(['item', 'contentType' => 'book'])

@php
    $routeName = match($contentType) {
        'module' => 'module.show',
        'software' => 'software.show',
        'audio' => 'audio.show',
        default => 'book.show',
    };
@endphp

<article class="book-list-item">
    <a href="{{ route($routeName, $item->slug) }}" class="book-list-cover">
        @if($item->cover_image)
            <img src="{{ asset('storage/uploads/' . $item->cover_image) }}"
                 alt="{{ $item->cover_alt ?? $item->title }}"
                 loading="lazy">
        @else
            <div class="book-list-placeholder">
                @if($contentType === 'audio')
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 18V5l12-2v13"/>
                        <circle cx="6" cy="18" r="3"/>
                        <circle cx="18" cy="16" r="3"/>
                    </svg>
                @elseif($contentType === 'module')
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                @elseif($contentType === 'software')
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                @else
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                @endif
            </div>
        @endif
    </a>
    <div class="book-list-info">
        <a href="{{ route($routeName, $item->slug) }}" class="book-list-title">{{ $item->title }}</a>
        @if($item->categories->isNotEmpty())
            <div class="book-list-category">
                {{ $item->categories->pluck('name')->join(', ') }}
            </div>
        @endif
        @if($item->description_plain)
            <p class="book-list-desc">{{ Str::limit($item->description_plain, 250) }}</p>
        @endif
        <div class="book-list-meta">
            <span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                {{ number_format($item->views_count, 0, '', ' ') }}
            </span>
            <span>{{ $item->published_at?->format('d.m.Y') }}</span>
        </div>
    </div>
</article>

<style>
    .book-list-item { display: flex; gap: 1.5rem; padding: 1.5rem; background: var(--bg-card); border-radius: 12px; box-shadow: 0 1px 3px var(--shadow); }
    .book-list-cover { flex-shrink: 0; width: 120px; }
    .book-list-cover img { width: 100%; height: auto; border-radius: 6px; box-shadow: 0 2px 8px var(--shadow); }
    .book-list-placeholder { width: 100%; aspect-ratio: 2/3; background: var(--border); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
    .book-list-info { flex: 1; min-width: 0; }
    .book-list-title { font-size: 1.1rem; font-weight: 700; color: var(--text-main); line-height: 1.4; display: block; margin-bottom: 0.5rem; }
    .book-list-title:hover { color: var(--primary); }
    .book-list-category { font-size: 0.85rem; color: var(--primary); margin-bottom: 0.75rem; }
    .book-list-desc { font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 1rem; }
    .book-list-meta { display: flex; gap: 1.5rem; font-size: 0.85rem; color: var(--text-muted); }
    .book-list-meta span { display: flex; align-items: center; gap: 0.35rem; }
    @media (max-width: 768px) { .book-list-item { padding: 1rem; gap: 1rem; } .book-list-cover { width: 80px; } .book-list-title { font-size: 1rem; } .book-list-desc { display: none; } }
</style>
