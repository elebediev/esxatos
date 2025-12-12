@props(['book', 'isNew' => false])

<article class="book-card-modern">
    <a href="{{ route('book.show', $book->slug) }}" class="book-card-link">
        <div class="book-card-cover">
            @if($book->cover_image)
                <img src="{{ asset('storage/uploads/' . $book->cover_image) }}"
                     alt="{{ $book->cover_alt ?? $book->title }}"
                     loading="lazy">
            @else
                <div class="book-card-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
            @endif
        </div>
        <div class="book-card-info">
            @if($book->author)
                <div class="book-card-author">{{ $book->author }}</div>
            @endif
            <h3 class="book-card-title">{{ $book->title }}</h3>
        </div>
    </a>
</article>

<style>
    .book-card-modern {
        display: flex;
        flex-direction: column;
    }

    .book-card-modern .book-card-link {
        display: flex;
        flex-direction: column;
        height: 100%;
        color: inherit;
        text-decoration: none;
    }

    .book-card-modern .book-card-cover {
        aspect-ratio: 2/3;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
        background: #e5e7eb;
    }

    .book-card-modern .book-card-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .book-card-modern .book-card-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
    }

    .book-card-modern .book-card-info {
        text-align: center;
        flex: 1;
    }

    .book-card-modern .book-card-author {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .book-card-modern .book-card-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #111827;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .book-card-modern .book-card-link:hover .book-card-title {
        color: #3b82f6;
    }
</style>
