@extends('layouts.app')

@section('title', $query ? "Поиск: {$query} - Esxatos" : 'Поиск - Esxatos')

@section('content')
    <div class="search-page">
        <h1 class="page-title">
            @if($query)
                Результаты поиска: «{{ $query }}»
            @else
                Поиск книг
            @endif
        </h1>

        <form action="{{ route('search') }}" method="GET" class="search-form-large mb-3">
            <input type="search" name="q" value="{{ $query }}" placeholder="Введите название книги или автора..." autofocus>
            <button type="submit" class="btn btn-primary">Искать</button>
        </form>

        @if($query)
            @if($books instanceof \Illuminate\Pagination\LengthAwarePaginator && $books->total() > 0)
                <p class="text-muted mb-2">Найдено: {{ $books->total() }} {{ trans_choice('книга|книги|книг', $books->total()) }}</p>

                <div class="grid grid-4">
                    @foreach($books as $book)
                        @include('components.book-card', ['book' => $book])
                    @endforeach
                </div>

                {{ $books->links('pagination.simple') }}
            @elseif($books->isEmpty())
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <h2>Ничего не найдено</h2>
                    <p>Попробуйте изменить запрос или использовать другие ключевые слова</p>
                </div>
            @endif
        @else
            <div class="search-tips">
                <h2>Советы по поиску</h2>
                <ul>
                    <li>Введите название книги или имя автора</li>
                    <li>Используйте ключевые слова: «библеистика», «богословие», «история церкви»</li>
                    <li>Для модулей BibleQuote добавьте «модуль» или «BibleQuote»</li>
                </ul>
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .search-page {
        max-width: 900px;
        margin: 0 auto;
    }

    .search-form-large {
        display: flex;
        gap: 0.75rem;
    }

    .search-form-large input {
        flex: 1;
        padding: 1rem 1.25rem;
        font-size: 1.125rem;
        border: 2px solid var(--border);
        border-radius: var(--radius);
        transition: border-color 0.2s;
    }

    .search-form-large input:focus {
        outline: none;
        border-color: var(--primary);
    }

    .search-form-large button {
        padding: 1rem 2rem;
        font-size: 1rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }

    .empty-state svg {
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h2 {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
        color: var(--text);
    }

    .search-tips {
        background: var(--bg-card);
        border-radius: var(--radius);
        padding: 2rem;
        margin-top: 2rem;
    }

    .search-tips h2 {
        font-size: 1.125rem;
        margin-bottom: 1rem;
    }

    .search-tips ul {
        list-style: none;
    }

    .search-tips li {
        padding: 0.5rem 0;
        padding-left: 1.5rem;
        position: relative;
    }

    .search-tips li::before {
        content: "→";
        position: absolute;
        left: 0;
        color: var(--primary);
    }

    @media (max-width: 640px) {
        .search-form-large {
            flex-direction: column;
        }

        .search-form-large button {
            width: 100%;
        }
    }
</style>
@endpush
