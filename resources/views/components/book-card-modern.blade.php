@props(['book', 'isNew' => false])

<article class="book-card" style="box-shadow: none; background: transparent;">
    <a href="{{ route('book.show', $book->slug) }}" class="book-card-link">
        <div class="book-card-cover" style="border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 1rem;">
            @if($book->cover_image)
                <img src="{{ asset('storage/uploads/' . $book->cover_image) }}"
                     alt="{{ $book->cover_alt ?? $book->title }}"
                     loading="lazy"
                     style="width: 100%; height: auto; display: block;">
            @else
                <div class="book-card-cover-placeholder" style="background: #e5e7eb; aspect-ratio: 2/3; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
            @endif
        </div>
        <div class="book-card-content" style="padding: 0; text-align: center;">
            @if($book->author)
                <div class="book-card-author" style="font-size: 0.85rem; color: #6b7280; margin-bottom: 0.25rem;">{{ $book->author }}</div>
            @endif
            <h3 class="book-card-title" style="font-size: 1rem; font-weight: 700; color: #111827; line-height: 1.3;">{{ $book->title }}</h3>
        </div>
    </a>
</article>

<style>
    .book-card-link {
        display: block;
        color: inherit;
        text-decoration: none;
    }
    .book-card-link:hover .book-card-title {
        color: var(--primary);
    }
</style>
