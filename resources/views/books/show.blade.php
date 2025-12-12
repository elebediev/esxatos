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

            {{-- Download Files grouped by access level --}}
            @php
                $user = auth()->user();
                $publicFiles = $book->files->where('access_level', 'public');
                $clubFiles = $book->files->where('access_level', 'club');
                $aideFiles = $book->files->where('access_level', 'aide');
                $canAccessClub = $user?->hasAnyRole(['club', 'aide', 'admin']);
                $canAccessAide = $user?->hasAnyRole(['aide', 'admin']);
                // Check if book is in "ЦЕЛЕВАЯ ПРОГРАММА" category (id=19)
                $isTargetProgram = $book->categories->contains('id', 19);
            @endphp

            {{-- Public files (for everyone) --}}
            @if($publicFiles->isNotEmpty())
                <div class="download-section">
                    <div class="download-section-title">Скачать</div>
                    @foreach($publicFiles as $file)
                        <a href="{{ $file->url }}" target="_blank" rel="noopener" class="btn-download">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            {{ $file->title ?: ($file->file_type ? strtoupper($file->file_type) : 'Скачать') }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Club files --}}
            @if($clubFiles->isNotEmpty())
                <div class="download-section download-section-club">
                    <div class="download-section-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        Для членов клуба
                        @if($isTargetProgram)
                            <span class="target-program-badge">Целевая программа</span>
                        @endif
                    </div>
                    @if($canAccessClub)
                        @foreach($clubFiles as $file)
                            <a href="{{ $file->url }}" target="_blank" rel="noopener" class="btn-download btn-download-club">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                {{ $file->title ?: ($file->file_type ? strtoupper($file->file_type) : 'Скачать') }}
                            </a>
                        @endforeach
                    @else
                        <div class="download-locked">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            @if($isTargetProgram)
                                {{ $clubFiles->count() }} {{ trans_choice('файл|файла|файлов', $clubFiles->count()) }} доступно по Целевой программе
                            @else
                                {{ $clubFiles->count() }} {{ trans_choice('файл|файла|файлов', $clubFiles->count()) }} доступно членам клуба
                            @endif
                        </div>
                        <a href="{{ route('messages.create', ['to' => 1, 'subject' => 'Запрос файлов: ' . $book->title]) }}" class="file-info-link">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            Информация о файлах
                        </a>
                    @endif
                </div>
            @endif

            {{-- Aide files --}}
            @if($aideFiles->isNotEmpty())
                <div class="download-section download-section-aide">
                    <div class="download-section-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        Для помощников
                    </div>
                    @if($canAccessAide)
                        @foreach($aideFiles as $file)
                            <a href="{{ $file->url }}" target="_blank" rel="noopener" class="btn-download btn-download-aide">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                {{ $file->title ?: ($file->file_type ? strtoupper($file->file_type) : 'Скачать') }}
                            </a>
                        @endforeach
                    @else
                        <div class="download-locked">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            {{ $aideFiles->count() }} {{ trans_choice('файл|файла|файлов', $aideFiles->count()) }} доступно помощникам
                        </div>
                    @endif
                </div>
            @endif

            {{-- Target Program notice (when no club files yet) --}}
            @if($isTargetProgram && $clubFiles->isEmpty())
                <div class="download-section download-section-target">
                    <div class="download-section-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Целевая программа
                    </div>
                    <div class="target-program-info">
                        Книга доступна по Целевой программе
                    </div>
                    <a href="{{ route('messages.create', ['to' => 1, 'subject' => 'Запрос файлов: ' . $book->title]) }}" class="file-info-link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        Запросить файлы
                    </a>
                </div>
            @endif
        </div>

        {{-- Column 2: Content (Meta + Description) --}}
        <div class="book-content-column">
            {{-- Meta Info (horizontal) --}}
            <div class="book-meta-row">
                <div class="meta-item">
                    <span class="meta-label">Категория</span>
                    <span class="meta-value">
                        @if($book->categories->isNotEmpty())
                            @foreach($book->categories as $category)
                                <a href="{{ route('category.show', $category->slug) }}" class="category-link">{{ $category->name }}</a>@if(!$loop->last), @endif
                            @endforeach
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

            {{-- Description --}}
            <div class="book-description-column">
                @if($book->description)
                    <div class="book-description">
                        {!! $book->description !!}
                    </div>
                @else
                    <p class="text-muted">Описание отсутствует</p>
                @endif
            </div>
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
                @foreach($relatedBooks->take(5) as $relatedBook)
                    @include('components.book-card-modern', ['book' => $relatedBook])
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
    .book-layout { display: grid; grid-template-columns: 220px 1fr; gap: 2rem; margin-bottom: 3rem; }
    .book-cover-column { position: sticky; top: 2rem; align-self: start; }
    .book-cover-wrapper { border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px var(--shadow); }
    .book-cover { width: 100%; height: auto; display: block; }
    .book-cover-placeholder { aspect-ratio: 2/3; background: var(--border); display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
    .download-section { display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem; }
    .download-section-title { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem; }
    .download-section-club { margin-top: 1.25rem; padding-top: 1rem; border-top: 1px dashed var(--border); }
    .download-section-aide { margin-top: 1.25rem; padding-top: 1rem; border-top: 1px dashed var(--border); }
    .download-section-target { margin-top: 1.25rem; padding-top: 1rem; border-top: 1px dashed var(--border); }
    .target-program-info { padding: 0.75rem; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1)); border-radius: 8px; color: #b45309; font-size: 0.85rem; border: 1px solid rgba(245, 158, 11, 0.3); }
    .btn-download { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; background: var(--primary); color: white; padding: 0.6rem 0.875rem; border-radius: 8px; font-weight: 600; font-size: 0.8rem; transition: background 0.2s; text-align: left; }
    .btn-download:hover { background: var(--primary-hover); color: white; }
    .btn-download-club { background: var(--primary); }
    .btn-download-club:hover { background: var(--primary-hover); color: white; }
    .btn-download-aide { background: #dc2626; }
    .btn-download-aide:hover { background: #b91c1c; color: white; }
    .download-locked { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; background: var(--bg-secondary); border-radius: 8px; color: var(--text-muted); font-size: 0.8rem; border: 1px dashed var(--border); }
    .target-program-badge { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; font-size: 0.65rem; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 600; text-transform: none; letter-spacing: 0; }
    .file-info-link { display: inline-flex; align-items: center; gap: 0.4rem; color: var(--primary); font-size: 0.8rem; margin-top: 0.5rem; transition: color 0.2s; }
    .file-info-link:hover { color: var(--primary-hover); text-decoration: underline; }
    .book-content-column { min-width: 0; }
    .book-meta-row { display: flex; flex-wrap: wrap; gap: 2rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border); }
    .meta-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .meta-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
    .meta-value { font-size: 0.95rem; color: var(--text-main); font-weight: 500; }
    .category-link { color: var(--text-main); transition: color 0.2s, text-decoration 0.2s; }
    .category-link:hover { color: var(--primary); text-decoration: underline; }
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
    .related-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.5rem; }
    .text-muted { color: var(--text-muted); }
    @media (max-width: 1024px) { .book-layout { grid-template-columns: 200px 1fr; gap: 1.5rem; } .related-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .book-title { font-size: 1.5rem; } .book-layout { grid-template-columns: 1fr; } .book-cover-column { position: static; max-width: 200px; margin: 0 auto; } .book-meta-row { justify-content: center; text-align: center; } .meta-item { align-items: center; } .related-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .book-meta-row { gap: 1rem; } .related-grid { grid-template-columns: 1fr; max-width: 280px; margin: 0 auto; } }
</style>
@endpush
