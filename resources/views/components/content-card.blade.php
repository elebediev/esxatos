@props(['item', 'contentType' => 'book'])

@php
    $routeName = match($contentType) {
        'module' => 'module.show',
        'software' => 'software.show',
        'audio' => 'audio.show',
        default => 'book.show',
    };
@endphp

<article class="book-card-modern">
    <a href="{{ route($routeName, $item->slug) }}" class="book-card-link">
        <div class="book-card-cover">
            @if($item->cover_image)
                <img src="{{ asset('storage/uploads/' . $item->cover_image) }}"
                     alt="{{ $item->cover_alt ?? $item->title }}"
                     loading="lazy">
            @else
                <div class="book-card-placeholder">
                    @if($contentType === 'audio')
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 18V5l12-2v13"/>
                            <circle cx="6" cy="18" r="3"/>
                            <circle cx="18" cy="16" r="3"/>
                        </svg>
                    @elseif($contentType === 'module')
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10,9 9,9 8,9"/>
                        </svg>
                    @elseif($contentType === 'software')
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    @else
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        </svg>
                    @endif
                </div>
            @endif
        </div>
        <div class="book-card-info">
            <h3 class="book-card-title">{{ $item->title }}</h3>
        </div>
    </a>
</article>

<style>
    .book-card-modern { display: flex; flex-direction: column; }
    .book-card-modern .book-card-link { display: flex; flex-direction: column; height: 100%; color: inherit; text-decoration: none; }
    .book-card-modern .book-card-cover { aspect-ratio: 2/3; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px var(--shadow); margin-bottom: 1rem; background: var(--border); }
    .book-card-modern .book-card-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .book-card-modern .book-card-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
    .book-card-modern .book-card-info { text-align: center; flex: 1; }
    .book-card-modern .book-card-title { font-size: 0.95rem; font-weight: 600; color: var(--text-main); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .book-card-modern .book-card-link:hover .book-card-title { color: var(--primary); }
</style>
