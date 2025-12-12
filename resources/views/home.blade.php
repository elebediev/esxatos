@extends('layouts.app')

@section('content')

    {{-- Books Section --}}
    <section class="home-section">
        <div class="section-header">
            <h2 class="section-title">Книги</h2>
            <a href="{{ route('books.index') }}" class="see-all-link">
                Все книги
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <div class="home-books-grid">
            @foreach($latestBooks->take(4) as $book)
                @include('components.book-card-modern', ['book' => $book])
            @endforeach
        </div>
    </section>


    {{-- Categories Section --}}
    <section class="home-section">
        <div class="section-header">
            <h2 class="section-title">Категории</h2>
        </div>

        <div class="categories-grid">
            @foreach($categories->take(12) as $category)
                <a href="{{ route('category.show', $category->slug) }}" class="category-card">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </section>


    {{-- Articles Section --}}
    <section class="home-section">
        <div class="section-header">
            <h2 class="section-title">Статьи</h2>
            <a href="#" class="see-all-link">
                Все статьи
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <div class="articles-grid">
            {{-- Article 1 --}}
            <article class="article-card">
                <div class="article-image">
                    <img src="https://images.unsplash.com/photo-1544928147-79a77456216d?auto=format&fit=crop&w=600&q=80" alt="Article">
                </div>
                <div class="article-content">
                    <div class="article-date">20 января 2024</div>
                    <h3 class="article-title">Иисус в Ежедневной Жизни: Как Найти Мир в Суете</h3>
                </div>
            </article>

            {{-- Article 2 --}}
            <article class="article-card">
                <div class="article-image">
                    <img src="https://images.unsplash.com/photo-1491841550275-ad7854e35ca6?auto=format&fit=crop&w=600&q=80" alt="Article">
                </div>
                <div class="article-content">
                    <div class="article-date">18 января 2024</div>
                    <h3 class="article-title">Апостолы Сегодня: Истории Настоящих Христианских Миссионеров</h3>
                </div>
            </article>

            {{-- Article 3 --}}
            <article class="article-card">
                <div class="article-image">
                    <img src="https://images.unsplash.com/photo-1506097425191-7ad538b29cef?auto=format&fit=crop&w=600&q=80" alt="Article">
                </div>
                <div class="article-content">
                    <div class="article-date">18 января 2024</div>
                    <h3 class="article-title">Современные Исааки: Вера в 21-м Веке</h3>
                </div>
            </article>
        </div>
    </section>

@endsection

@push('styles')
<style>
    .home-section { margin-bottom: 4rem; }
    .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
    .section-title { font-size: 2rem; font-weight: 700; color: var(--text-main); }
    .see-all-link { color: var(--text-secondary); font-weight: 500; font-size: 0.95rem; display: flex; align-items: center; gap: 4px; }
    .see-all-link:hover { color: var(--primary); }
    .home-books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 2rem; }
    .categories-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
    .category-card { background: var(--bg-card); border-radius: 8px; padding: 1.25rem; text-align: center; color: var(--text-main); font-weight: 600; box-shadow: 0 1px 3px var(--shadow); transition: 0.2s; }
    .category-card:hover { box-shadow: 0 4px 6px var(--shadow); transform: translateY(-2px); }
    .articles-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
    .article-card { background: var(--bg-card); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px var(--shadow); }
    .article-image { height: 200px; background: var(--border); }
    .article-image img { width: 100%; height: 100%; object-fit: cover; }
    .article-content { padding: 1.5rem; }
    .article-date { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem; }
    .article-title { font-weight: 700; font-size: 1.1rem; line-height: 1.4; color: var(--text-main); }
    @media (max-width: 1024px) { .categories-grid { grid-template-columns: repeat(3, 1fr); } .articles-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) { .categories-grid { grid-template-columns: repeat(2, 1fr); } .articles-grid { grid-template-columns: 1fr; } .section-title { font-size: 1.5rem; } }
    @media (max-width: 480px) { .categories-grid { grid-template-columns: 1fr; } }
</style>
@endpush
