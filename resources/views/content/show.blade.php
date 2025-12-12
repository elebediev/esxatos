@extends('layouts.app')

@section('title', $item->title . ' - Esxatos')
@section('description', Str::limit(strip_tags($item->description), 160))

@php
    $indexRoute = match($contentType) {
        'module' => 'modules.index',
        'software' => 'software.index',
        'audio' => 'audio.index',
        default => 'books.index',
    };
    $showRoute = match($contentType) {
        'module' => 'module.show',
        'software' => 'software.show',
        'audio' => 'audio.show',
        default => 'book.show',
    };
@endphp

@section('meta')
    <meta property="og:title" content="{{ $item->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($item->description), 160) }}">
    @if($item->cover_image)
        <meta property="og:image" content="{{ asset('storage/uploads/' . $item->cover_image) }}">
    @endif
    <meta property="og:type" content="book">
@endsection

@section('content')
    {{-- Breadcrumb --}}
    <nav class="breadcrumb">
        <a href="{{ route('home') }}">Главная</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route($indexRoute) }}">{{ $titlePlural }}</a>
        @if($item->categories->isNotEmpty())
            <span class="breadcrumb-separator">/</span>
            <a href="{{ route('category.show', $item->categories->first()->slug) }}">{{ $item->categories->first()->name }}</a>
        @endif
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">{{ Str::limit($item->title, 50) }}</span>
    </nav>

    {{-- Author --}}
    @if($item->user)
        <div class="book-author-name">{{ $item->user->name }}</div>
    @endif

    {{-- Title --}}
    <h1 class="book-title">{{ $item->title }}</h1>

    {{-- Main Grid --}}
    <div class="book-layout">
        {{-- Column 1: Cover --}}
        <div class="book-cover-column">
            <div class="book-cover-wrapper">
                @if($item->cover_image)
                    <img src="{{ asset('storage/uploads/' . $item->cover_image) }}"
                         alt="{{ $item->cover_alt ?? $item->title }}"
                         class="book-cover">
                @else
                    <div class="book-cover-placeholder">
                        @if($contentType === 'audio')
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M9 18V5l12-2v13"/>
                                <circle cx="6" cy="18" r="3"/>
                                <circle cx="18" cy="16" r="3"/>
                            </svg>
                        @elseif($contentType === 'module')
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14,2 14,8 20,8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                        @elseif($contentType === 'software')
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                        @else
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                            </svg>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Download Button --}}
            @if($item->files->isNotEmpty())
                <div class="download-section">
                    @foreach($item->files as $file)
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

        {{-- Column 2: Meta Info --}}
        <div class="book-meta-column">
            <div class="meta-item">
                <span class="meta-label">Категория</span>
                <span class="meta-value">
                    @if($item->categories->isNotEmpty())
                        {{ $item->categories->pluck('name')->join(', ') }}
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Просмотров</span>
                <span class="meta-value">{{ number_format($item->views_count, 0, '', ' ') }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Рейтинг</span>
                <span class="meta-value">
                    @if($item->rating_count > 0)
                        {{ number_format($item->rating_stars, 1) }} / 5
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Добавлено</span>
                <span class="meta-value">{{ $item->published_at?->format('d.m.Y') ?? $item->created_at->format('d.m.Y') }}</span>
            </div>
        </div>

        {{-- Column 3: Description --}}
        <div class="book-description-column">
            @if($item->description)
                <div class="book-description">
                    {!! $item->description !!}
                </div>
            @else
                <p class="text-muted">Описание отсутствует</p>
            @endif
        </div>
    </div>

    {{-- Related Items --}}
    @if($relatedItems->isNotEmpty())
        <section class="related-section">
            <div class="flex items-center justify-between" style="margin-bottom: 1.5rem;">
                <h2 class="section-title">Похожие {{ mb_strtolower($titlePlural) }}</h2>
                <a href="{{ route($indexRoute) }}" class="see-all-link">
                    Все {{ mb_strtolower($titlePlural) }}
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
            <div class="related-grid">
                @foreach($relatedItems->take(4) as $relatedItem)
                    @include('components.content-card', ['item' => $relatedItem, 'contentType' => $contentType])
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('styles')
<style>
    .breadcrumb { display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem; font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1.5rem; }
    .breadcrumb a { color: var(--text-muted); }
    .breadcrumb a:hover { color: var(--primary); }
    .breadcrumb-separator { color: var(--border); }
    .breadcrumb-current { color: var(--text-main); }
    .book-author-name { font-size: 1rem; color: var(--text-muted); margin-bottom: 0.5rem; }
    .book-title { font-size: 2rem; font-weight: 700; color: var(--text-main); line-height: 1.3; margin-bottom: 2rem; }
    .book-layout { display: grid; grid-template-columns: 220px 180px 1fr; gap: 2rem; margin-bottom: 3rem; }
    .book-cover-column { position: sticky; top: 2rem; align-self: start; }
    .book-cover-wrapper { border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px var(--shadow); }
    .book-cover { width: 100%; height: auto; display: block; }
    .book-cover-placeholder { aspect-ratio: 2/3; background: var(--border); display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
    .download-section { display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem; }
    .btn-download { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; background: var(--primary); color: white; padding: 0.75rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: background 0.2s; }
    .btn-download:hover { background: var(--primary-hover); color: white; }
    .book-meta-column { display: flex; flex-direction: column; gap: 1.25rem; }
    .meta-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .meta-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
    .meta-value { font-size: 0.95rem; color: var(--text-main); font-weight: 500; }
    .book-description-column { min-width: 0; }
    .book-description { color: var(--text-secondary); line-height: 1.8; font-size: 0.95rem; }
    .book-description p { margin-bottom: 1rem; }
    .book-description p:last-child { margin-bottom: 0; }
    .book-description a { color: var(--primary); }
    .book-description a:hover { text-decoration: underline; }
    .related-section { margin-top: 3rem; padding-top: 3rem; border-top: 1px solid var(--border); }
    .section-title { font-size: 1.5rem; font-weight: 700; color: var(--text-main); }
    .see-all-link { color: var(--text-secondary); font-weight: 500; font-size: 0.95rem; display: flex; align-items: center; gap: 4px; }
    .see-all-link:hover { color: var(--primary); }
    .related-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; }
    .text-muted { color: var(--text-muted); }
    @media (max-width: 1024px) { .book-layout { grid-template-columns: 200px 1fr; gap: 1.5rem; } .book-meta-column { grid-column: 1 / -1; flex-direction: row; flex-wrap: wrap; gap: 1.5rem; } .book-description-column { grid-column: 1 / -1; } .related-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .book-title { font-size: 1.5rem; } .book-layout { grid-template-columns: 1fr; } .book-cover-column { position: static; max-width: 200px; margin: 0 auto; } .book-meta-column { justify-content: center; text-align: center; } .meta-item { align-items: center; } .related-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .related-grid { grid-template-columns: 1fr; max-width: 280px; margin: 0 auto; } }
</style>
@endpush
