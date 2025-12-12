@extends('layouts.app')

@section('content')

    {{-- Books Section --}}
    <section class="section" style="margin-bottom: 4rem;">
        {{-- Header row --}}
        <div class="flex items-center justify-between" style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 2rem; font-weight: 700; color: #111827;">Книги</h2>
            <a href="{{ route('books.index') }}" style="color: #4b5563; font-weight: 500; font-size: 0.95rem; display: flex; align-items: center; gap: 4px;">
                Все книги
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        {{-- Tabs / Pills --}}
        <div class="flex items-center gap-4" style="margin-bottom: 2rem;">
            <a href="#" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 6px;">
                <span>🔥</span> Новинки
            </a>
            <a href="#" style="background: #f3f4f6; color: #4b5563; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 6px;">
                <span>👑</span> Популярное
            </a>
        </div>

        {{-- Books Grid --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 2rem;">
            @foreach($latestBooks->take(4) as $book)
                @include('components.book-card-modern', ['book' => $book])
            @endforeach
        </div>
    </section>


    {{-- Categories Section --}}
    <section class="section" style="margin-bottom: 4rem;">
        <div class="flex items-center justify-between" style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 2rem; font-weight: 700; color: #111827;">Категории</h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
            @foreach($categories->take(12) as $category)
                <a href="{{ route('category.show', $category->slug) }}" 
                   style="background: white; border-radius: 8px; padding: 1.25rem; text-align: center; color: #111827; font-weight: 600; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: 0.2s;"
                   onmouseover="this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'"
                   onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)'">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </section>


    {{-- Articles Section --}}
    <section class="section" style="margin-bottom: 4rem;">
        <div class="flex items-center justify-between" style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 2rem; font-weight: 700; color: #111827;">Статьи</h2>
            <a href="#" style="color: #4b5563; font-weight: 500; font-size: 0.95rem; display: flex; align-items: center; gap: 4px;">
                Все статьи
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
            {{-- Article 1 --}}
            <article style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="height: 200px; background: #e5e7eb; position: relative;">
                    <img src="https://images.unsplash.com/photo-1544928147-79a77456216d?auto=format&fit=crop&w=600&q=80" alt="Article" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="padding: 1.5rem;">
                    <div style="color: #9ca3af; font-size: 0.85rem; margin-bottom: 0.5rem;">20 января 2024</div>
                    <h3 style="font-weight: 700; font-size: 1.1rem; line-height: 1.4; color: #111827;">
                        Иисус в Ежедневной Жизни: Как Найти Мир в Суете
                    </h3>
                </div>
            </article>

            {{-- Article 2 --}}
            <article style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="height: 200px; background: #e5e7eb; position: relative;">
                    <img src="https://images.unsplash.com/photo-1491841550275-ad7854e35ca6?auto=format&fit=crop&w=600&q=80" alt="Article" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="padding: 1.5rem;">
                    <div style="color: #9ca3af; font-size: 0.85rem; margin-bottom: 0.5rem;">18 января 2024</div>
                    <h3 style="font-weight: 700; font-size: 1.1rem; line-height: 1.4; color: #111827;">
                        Апостолы Сегодня: Истории Настоящих Христианских Миссионеров
                    </h3>
                </div>
            </article>

            {{-- Article 3 --}}
            <article style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="height: 200px; background: #e5e7eb; position: relative;">
                    <img src="https://images.unsplash.com/photo-1506097425191-7ad538b29cef?auto=format&fit=crop&w=600&q=80" alt="Article" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="padding: 1.5rem;">
                    <div style="color: #9ca3af; font-size: 0.85rem; margin-bottom: 0.5rem;">18 января 2024</div>
                    <h3 style="font-weight: 700; font-size: 1.1rem; line-height: 1.4; color: #111827;">
                        Современные Исааки: Вера в 21-м Веке
                    </h3>
                </div>
            </article>
        </div>
    </section>

@endsection
