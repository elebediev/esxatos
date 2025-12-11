<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Esxatos - Богословская библиотека')</title>
    <meta name="description" content="@yield('description', 'Esxatos - крупнейшая богословская библиотека. Книги, модули BibleQuote, статьи по библеистике, богословию, истории церкви.')">

    @yield('meta')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1e40af;
            --primary-dark: #1e3a8a;
            --secondary: #64748b;
            --accent: #f59e0b;
            --bg: #f8fafc;
            --bg-card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 8px;
            --shadow: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        a {
            color: var(--primary);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Header */
        .header {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            gap: 1rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo:hover {
            text-decoration: none;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav a {
            color: var(--text);
            font-weight: 500;
            padding: 0.5rem 0;
        }

        .nav a:hover {
            color: var(--primary);
            text-decoration: none;
        }

        /* Search */
        .search-form {
            flex: 1;
            max-width: 500px;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        /* Main */
        .main {
            padding: 2rem 0;
            min-height: calc(100vh - 200px);
        }

        /* Footer */
        .footer {
            background: var(--bg-card);
            border-top: 1px solid var(--border);
            padding: 2rem 0;
            margin-top: 2rem;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-text {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            text-decoration: none;
        }

        .btn-secondary {
            background: var(--bg);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--border);
            text-decoration: none;
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* Grid */
        .grid {
            display: grid;
            gap: 1.5rem;
        }

        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        .grid-6 { grid-template-columns: repeat(6, 1fr); }

        @media (max-width: 1024px) {
            .grid-4, .grid-6 { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 768px) {
            .grid-3, .grid-4, .grid-6 { grid-template-columns: repeat(2, 1fr); }
            .nav { display: none; }
            .header-inner { flex-wrap: wrap; }
            .search-form { order: 3; max-width: 100%; width: 100%; }
        }

        @media (max-width: 480px) {
            .grid-2, .grid-3, .grid-4, .grid-6 { grid-template-columns: 1fr; }
        }

        /* Utilities */
        .text-center { text-align: center; }
        .text-muted { color: var(--text-muted); }
        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 1.5rem; }
        .mb-4 { margin-bottom: 2rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-4 { margin-top: 2rem; }

        /* Page Title */
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.875rem;
        }

        .pagination a:hover {
            background: var(--bg);
            text-decoration: none;
        }

        .pagination .active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>

    @stack('styles')
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="{{ route('home') }}" class="logo">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                    Esxatos
                </a>

                <form action="{{ route('search') }}" method="GET" class="search-form">
                    <div class="search-input-wrapper">
                        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input type="search" name="q" class="search-input" placeholder="Поиск книг..." value="{{ request('q') }}" autocomplete="off">
                    </div>
                </form>

                <nav class="nav">
                    <a href="{{ route('books.index') }}">Библиотека</a>
                    <a href="{{ route('categories.index') }}">Категории</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-text">
                    &copy; {{ date('Y') }} Esxatos - Богословская библиотека
                </div>
                <div class="footer-text">
                    {{ \App\Models\Book::count() }} книг в библиотеке
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
