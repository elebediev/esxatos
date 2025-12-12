@extends('layouts.app')

@section('title', $book->title . ' - Esxatos')
@section('description', Str::limit(strip_tags($book->description), 160))

@section('meta')
    <meta property="og:title" content="{{ $book->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($book->description), 160) }}">
    @if($book->cover_image)
        <meta property="og:image" content="{{ asset('storage/uploads/' . $book->cover_image) }}">
    @endif
    <meta property="og:type" content="book">
@endsection

@section('content')
    {{-- Breadcrumb --}}
    <nav class="breadcrumb">
        <a href="{{ route('home') }}">Главная</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('books.index') }}">Книги</a>
        @if($book->categories->isNotEmpty())
            <span class="breadcrumb-separator">/</span>
            <a href="{{ route('category.show', $book->categories->first()->slug) }}">{{ $book->categories->first()->name }}</a>
        @endif
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">{{ Str::limit($book->title, 50) }}</span>
    </nav>

    {{-- Author --}}
    @if($book->user)
        <div class="book-author-name">{{ $book->user->name }}</div>
    @endif

    {{-- Title --}}
    <h1 class="book-title">{{ $book->title }}</h1>

    {{-- Main Grid --}}
    <div class="book-layout">
        {{-- Column 1: Cover --}}
        <div class="book-cover-column">
            <div class="book-cover-wrapper">
                @if($book->cover_image)
                    <img src="{{ asset('storage/uploads/' . $book->cover_image) }}"
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
        </div>

        {{-- Column 2: Meta Info --}}
        <div class="book-meta-column">
            <div class="meta-item">
                <span class="meta-label">Категория</span>
                <span class="meta-value">
                    @if($book->categories->isNotEmpty())
                        {{ $book->categories->pluck('name')->join(', ') }}
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Просмотров</span>
                <span class="meta-value">{{ number_format($book->views_count, 0, '', ' ') }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Рейтинг</span>
                <span class="meta-value">
                    @if($book->rating_count > 0)
                        {{ number_format($book->rating_stars, 1) }} / 5
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Добавлено</span>
                <span class="meta-value">{{ $book->published_at?->format('d.m.Y') ?? $book->created_at->format('d.m.Y') }}</span>
            </div>
        </div>

        {{-- Column 3: Accordions --}}
        <div class="book-accordions-column">
            {{-- Description Accordion --}}
            <details class="accordion" open>
                <summary class="accordion-header">
                    <span>Описание</span>
                    <svg class="accordion-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </summary>
                <div class="accordion-content">
                    @if($book->description)
                        {!! $book->description !!}
                    @else
                        <p class="text-muted">Описание отсутствует</p>
                    @endif
                </div>
            </details>

            {{-- Contents Accordion --}}
            <details class="accordion">
                <summary class="accordion-header">
                    <span>Содержание</span>
                    <svg class="accordion-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </summary>
                <div class="accordion-content">
                    <p class="text-muted">Содержание книги недоступно</p>
                </div>
            </details>

            {{-- Details Accordion --}}
            <details class="accordion">
                <summary class="accordion-header">
                    <span>Подробности</span>
                    <svg class="accordion-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </summary>
                <div class="accordion-content">
                    <div class="details-grid">
                        @if($book->categories->isNotEmpty())
                            <div class="detail-item">
                                <span class="detail-label">Категория:</span>
                                <span class="detail-value">{{ $book->categories->pluck('name')->join(', ') }}</span>
                            </div>
                        @endif
                        <div class="detail-item">
                            <span class="detail-label">Добавлено:</span>
                            <span class="detail-value">{{ $book->published_at?->format('d.m.Y') ?? $book->created_at->format('d.m.Y') }}</span>
                        </div>
                        @if($book->files->isNotEmpty())
                            <div class="detail-item">
                                <span class="detail-label">Формат:</span>
                                <span class="detail-value">{{ $book->files->pluck('file_type')->filter()->unique()->map(fn($t) => strtoupper($t))->join(', ') ?: '—' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </details>

            {{-- Download Button --}}
            @if($book->files->isNotEmpty())
                <div class="download-section">
                    @foreach($book->files as $file)
                        <a href="{{ $file->url }}" target="_blank" rel="noopener" class="btn-download">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Скачать{{ $file->file_type ? ' ' . strtoupper($file->file_type) : '' }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Related Books --}}
    @if($relatedBooks->isNotEmpty())
        <section class="related-section">
            <div class="flex items-center justify-between" style="margin-bottom: 1.5rem;">
                <h2 class="section-title">Похожие книги</h2>
                <a href="{{ route('books.index') }}" class="see-all-link">
                    Все книги
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
            <div class="related-grid">
                @foreach($relatedBooks->take(4) as $relatedBook)
                    @include('components.book-card-modern', ['book' => $relatedBook])
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('styles')
<style>
    /* Breadcrumb */
    .breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 1.5rem;
    }

    .breadcrumb a {
        color: #6b7280;
    }

    .breadcrumb a:hover {
        color: #3b82f6;
    }

    .breadcrumb-separator {
        color: #d1d5db;
    }

    .breadcrumb-current {
        color: #111827;
    }

    /* Author */
    .book-author-name {
        font-size: 1rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    /* Title */
    .book-title {
        font-size: 2rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.3;
        margin-bottom: 2rem;
    }

    /* Main Layout */
    .book-layout {
        display: grid;
        grid-template-columns: 220px 200px 1fr;
        gap: 2rem;
        margin-bottom: 3rem;
    }

    /* Cover Column */
    .book-cover-column {
        position: sticky;
        top: 2rem;
        align-self: start;
    }

    .book-cover-wrapper {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    }

    .book-cover {
        width: 100%;
        height: auto;
        display: block;
    }

    .book-cover-placeholder {
        aspect-ratio: 2/3;
        background: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
    }

    /* Meta Column */
    .book-meta-column {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .meta-label {
        font-size: 0.75rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .meta-value {
        font-size: 0.95rem;
        color: #111827;
        font-weight: 500;
    }

    /* Accordions Column */
    .book-accordions-column {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .accordion {
        border-bottom: 1px solid #e5e7eb;
    }

    .accordion:first-child {
        border-top: 1px solid #e5e7eb;
    }

    .accordion-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 0;
        font-weight: 600;
        font-size: 1rem;
        color: #111827;
        cursor: pointer;
        list-style: none;
    }

    .accordion-header::-webkit-details-marker {
        display: none;
    }

    .accordion-icon {
        transition: transform 0.2s ease;
    }

    .accordion[open] .accordion-icon {
        transform: rotate(180deg);
    }

    .accordion-content {
        padding: 0 0 1.5rem 0;
        color: #4b5563;
        line-height: 1.7;
        font-size: 0.95rem;
    }

    .accordion-content p {
        margin-bottom: 1rem;
    }

    .accordion-content p:last-child {
        margin-bottom: 0;
    }

    /* Details Grid */
    .details-grid {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .detail-item {
        display: flex;
        gap: 0.5rem;
    }

    .detail-label {
        color: #6b7280;
        min-width: 100px;
    }

    .detail-value {
        color: #111827;
    }

    /* Download Section */
    .download-section {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }

    .btn-download {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: #3b82f6;
        color: white;
        padding: 0.875rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        transition: background 0.2s;
    }

    .btn-download:hover {
        background: #2563eb;
        color: white;
    }

    /* Related Section */
    .related-section {
        margin-top: 3rem;
        padding-top: 3rem;
        border-top: 1px solid #e5e7eb;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
    }

    .see-all-link {
        color: #4b5563;
        font-weight: 500;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .see-all-link:hover {
        color: #3b82f6;
    }

    .related-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
    }

    /* Text utilities */
    .text-muted {
        color: #9ca3af;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .book-layout {
            grid-template-columns: 200px 1fr;
            gap: 1.5rem;
        }

        .book-meta-column {
            grid-column: 1 / -1;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .book-accordions-column {
            grid-column: 1 / -1;
        }

        .related-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .book-title {
            font-size: 1.5rem;
        }

        .book-layout {
            grid-template-columns: 1fr;
        }

        .book-cover-column {
            position: static;
            max-width: 200px;
            margin: 0 auto;
        }

        .book-meta-column {
            justify-content: center;
            text-align: center;
        }

        .meta-item {
            align-items: center;
        }

        .related-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .related-grid {
            grid-template-columns: 1fr;
            max-width: 280px;
            margin: 0 auto;
        }
    }
</style>
@endpush
