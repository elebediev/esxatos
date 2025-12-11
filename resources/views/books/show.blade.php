@extends('layouts.app')

@section('title', $book->title . ' - Esxatos')
@section('description', Str::limit(strip_tags($book->description), 160))

@section('meta')
    <meta property="og:title" content="{{ $book->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($book->description), 160) }}">
    @if($book->cover_image)
        <meta property="og:image" content="https://esxatos.com/sites/default/files/{{ $book->cover_image }}">
    @endif
    <meta property="og:type" content="book">
@endsection

@section('content')
    <div class="book-page">
        <div class="book-main">
            <div class="book-cover-wrapper">
                @if($book->cover_image)
                    <img src="https://esxatos.com/sites/default/files/{{ $book->cover_image }}"
                         alt="{{ $book->cover_alt ?? $book->title }}"
                         class="book-cover">
                @else
                    <div class="book-cover-placeholder">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        </svg>
                    </div>
                @endif
            </div>

            <div class="book-info">
                <h1 class="book-title">{{ $book->title }}</h1>

                @if($book->categories->isNotEmpty())
                    <div class="book-categories mb-2">
                        @foreach($book->categories as $category)
                            <a href="{{ route('category.show', $category->slug) }}" class="book-category">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="book-meta mb-3">
                    <span title="Просмотры">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        {{ number_format($book->views_count) }} просмотров
                    </span>
                    <span title="Дата публикации">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        {{ $book->published_at?->format('d.m.Y') }}
                    </span>
                </div>

                @if($book->files->isNotEmpty())
                    <div class="book-downloads mb-3">
                        <h3>Скачать</h3>
                        <div class="download-list">
                            @foreach($book->files as $file)
                                <a href="{{ $file->url }}" target="_blank" rel="noopener" class="download-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    <span class="download-title">{{ $file->title ?: 'Скачать файл' }}</span>
                                    @if($file->file_type)
                                        <span class="download-type">{{ strtoupper($file->file_type) }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="book-description card">
            <h2>Описание</h2>
            <div class="book-content">
                {!! $book->description !!}
            </div>
        </div>

        @if($relatedBooks->isNotEmpty())
            <section class="related-books mt-4">
                <h2 class="mb-2">Похожие книги</h2>
                <div class="grid grid-6">
                    @foreach($relatedBooks as $relatedBook)
                        @include('components.book-card', ['book' => $relatedBook])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .book-page {
        max-width: 1000px;
        margin: 0 auto;
    }

    .book-main {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .book-cover-wrapper {
        aspect-ratio: 3/4;
        background: var(--bg-card);
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }

    .book-cover {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .book-cover-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        background: var(--bg);
    }

    .book-title {
        font-size: 1.75rem;
        line-height: 1.3;
        margin-bottom: 1rem;
    }

    .book-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .book-category {
        font-size: 0.875rem;
        color: var(--primary);
        background: rgba(30, 64, 175, 0.1);
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
    }

    .book-category:hover {
        background: rgba(30, 64, 175, 0.2);
        text-decoration: none;
    }

    .book-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        color: var(--text-muted);
        font-size: 0.875rem;
    }

    .book-meta span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .book-downloads h3 {
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }

    .download-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .download-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: var(--bg);
        border-radius: var(--radius);
        color: var(--text);
        transition: all 0.2s;
    }

    .download-item:hover {
        background: var(--primary);
        color: white;
        text-decoration: none;
    }

    .download-title {
        flex: 1;
        font-size: 0.875rem;
    }

    .download-type {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.125rem 0.5rem;
        background: rgba(0,0,0,0.1);
        border-radius: var(--radius);
    }

    .book-description {
        padding: 2rem;
    }

    .book-description h2 {
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    .book-content {
        line-height: 1.7;
    }

    .book-content h2, .book-content h3 {
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .book-content p {
        margin-bottom: 1rem;
    }

    .book-content a {
        word-break: break-word;
    }

    .book-content img {
        max-width: 100%;
        height: auto;
    }

    .related-books h2 {
        font-size: 1.25rem;
    }

    @media (max-width: 768px) {
        .book-main {
            grid-template-columns: 1fr;
        }

        .book-cover-wrapper {
            max-width: 280px;
            margin: 0 auto;
        }

        .book-title {
            font-size: 1.5rem;
        }
    }
</style>
@endpush
