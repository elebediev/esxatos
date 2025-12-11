@props(['book'])

<article class="book-card card">
    <a href="{{ route('book.show', $book->slug) }}" class="book-card-cover">
        @if($book->cover_image)
            <img src="https://esxatos.com/sites/default/files/{{ $book->cover_image }}"
                 alt="{{ $book->cover_alt ?? $book->title }}"
                 loading="lazy">
        @else
            <div class="book-card-cover-placeholder">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
            </div>
        @endif
    </a>
    <div class="book-card-content">
        <h3 class="book-card-title">
            <a href="{{ route('book.show', $book->slug) }}">{{ Str::limit($book->title, 60) }}</a>
        </h3>
        @if($book->categories->isNotEmpty())
            <div class="book-card-categories">
                @foreach($book->categories->take(2) as $category)
                    <a href="{{ route('category.show', $category->slug) }}" class="book-card-category">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif
        <div class="book-card-meta">
            <span title="Просмотры">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                {{ number_format($book->views_count) }}
            </span>
        </div>
    </div>
</article>

<style>
    .book-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .book-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .book-card-cover {
        aspect-ratio: 3/4;
        overflow: hidden;
        background: var(--bg);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .book-card-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .book-card:hover .book-card-cover img {
        transform: scale(1.05);
    }

    .book-card-cover-placeholder {
        color: var(--text-muted);
    }

    .book-card-content {
        padding: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .book-card-title {
        font-size: 0.9375rem;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 0.5rem;
        flex: 1;
    }

    .book-card-title a {
        color: var(--text);
    }

    .book-card-title a:hover {
        color: var(--primary);
    }

    .book-card-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        margin-bottom: 0.5rem;
    }

    .book-card-category {
        font-size: 0.75rem;
        color: var(--text-muted);
        background: var(--bg);
        padding: 0.125rem 0.5rem;
        border-radius: 999px;
    }

    .book-card-category:hover {
        background: var(--border);
        text-decoration: none;
    }

    .book-card-meta {
        font-size: 0.75rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .book-card-meta svg {
        flex-shrink: 0;
    }
</style>
