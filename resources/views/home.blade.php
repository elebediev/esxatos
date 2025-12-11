@extends('layouts.app')

@section('title', 'Esxatos - Богословская библиотека')
@section('description', 'Esxatos - крупнейшая богословская библиотека. Более ' . number_format($stats['books']) . ' книг по библеистике, богословию, истории церкви.')

@section('content')
    {{-- Hero Section --}}
    <section class="hero mb-4">
        <h1>Богословская библиотека</h1>
        <p class="text-muted">Более {{ number_format($stats['books']) }} книг по библеистике, богословию, истории церкви и философии религии</p>
    </section>

    {{-- Categories --}}
    <section class="mb-4">
        <div class="section-header">
            <h2>Категории</h2>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Все категории</a>
        </div>
        <div class="categories-grid">
            @foreach($categories as $category)
                <a href="{{ route('category.show', $category->slug) }}" class="category-item">
                    <span class="category-name">{{ $category->name }}</span>
                    <span class="category-count">{{ $category->books_count }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Latest Books --}}
    <section class="mb-4">
        <div class="section-header">
            <h2>Новые поступления</h2>
            <a href="{{ route('books.index') }}?sort=newest" class="btn btn-secondary">Смотреть все</a>
        </div>
        <div class="grid grid-6">
            @foreach($latestBooks as $book)
                @include('components.book-card', ['book' => $book])
            @endforeach
        </div>
    </section>

    {{-- Popular Books --}}
    <section class="mb-4">
        <div class="section-header">
            <h2>Популярные книги</h2>
            <a href="{{ route('books.index') }}?sort=popular" class="btn btn-secondary">Смотреть все</a>
        </div>
        <div class="grid grid-6">
            @foreach($popularBooks as $book)
                @include('components.book-card', ['book' => $book])
            @endforeach
        </div>
    </section>
@endsection

@push('styles')
<style>
    .hero {
        text-align: center;
        padding: 3rem 1rem;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: var(--radius);
        color: white;
        margin-bottom: 2rem;
    }

    .hero h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    .hero p {
        color: rgba(255,255,255,0.9);
        font-size: 1.125rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-header h2 {
        font-size: 1.5rem;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
        margin-bottom: 2rem;
    }

    .category-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        background: var(--bg-card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        transition: all 0.2s;
        color: var(--text);
    }

    .category-item:hover {
        background: var(--primary);
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .category-name {
        font-weight: 500;
    }

    .category-count {
        font-size: 0.875rem;
        opacity: 0.7;
    }

    @media (max-width: 768px) {
        .hero h1 {
            font-size: 1.75rem;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }
</style>
@endpush
