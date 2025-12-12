<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ΕΣΧΑΤΟΣ - Богословская библиотека')</title>
    <meta name="description" content="@yield('description', 'ΕΣΧΑΤΟΣ - крупнейшая богословская библиотека.')">

    @yield('meta')

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        // Apply theme before page renders to prevent flash
        (function() {
            const theme = localStorage.getItem('theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <style>
        /* Light theme (default) */
        :root, [data-theme="light"] {
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --primary-dark: #2563eb;
            --text-main: #111827;
            --text-secondary: #4b5563;
            --text-muted: #9ca3af;
            --bg-body: #f1f3f5;
            --bg-secondary: #f3f4f6;
            --bg-card: #ffffff;
            --bg-header: #ffffff;
            --border: #e5e7eb;
            --shadow: rgba(0,0,0,0.1);
            --logo-color: #274E87;
        }

        /* Dark theme */
        [data-theme="dark"] {
            --primary: #60a5fa;
            --primary-hover: #93c5fd;
            --primary-dark: #3b82f6;
            --text-main: #f3f4f6;
            --text-secondary: #d1d5db;
            --text-muted: #6b7280;
            --bg-body: #111827;
            --bg-secondary: #1f2937;
            --bg-card: #1f2937;
            --bg-header: #1f2937;
            --border: #374151;
            --shadow: rgba(0,0,0,0.3);
            --logo-color: #60a5fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        a { text-decoration: none; transition: 0.2s; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-4 { gap: 1rem; }

        /* HEADER */
        .header {
            background: var(--bg-header);
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 0;
        }

        .logo {
            display: flex;
            align-items: center;
            color: var(--logo-color);
        }

        .logo:hover {
            color: var(--primary);
        }

        .logo svg {
            height: 20px;
            width: auto;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            font-family: 'Inter', sans-serif;
        }

        .nav-link {
            font-size: 0.95rem;
            color: var(--text-secondary);
            font-weight: 500;
            font-family: 'Inter', sans-serif;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .search-trigger {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
        }

        .search-trigger:hover {
            color: var(--primary);
        }

        /* Theme Toggle */
        .theme-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .theme-toggle:hover {
            background: var(--border);
            color: var(--text-main);
        }

        .theme-toggle .icon-sun,
        .theme-toggle .icon-moon {
            width: 20px;
            height: 20px;
        }

        [data-theme="light"] .theme-toggle .icon-sun { display: none; }
        [data-theme="light"] .theme-toggle .icon-moon { display: block; }
        [data-theme="dark"] .theme-toggle .icon-sun { display: block; }
        [data-theme="dark"] .theme-toggle .icon-moon { display: none; }

        .btn-login {
            background-color: var(--primary);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 9999px;
            font-weight: 500;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-login:hover {
            background-color: var(--primary-dark);
        }

        /* MAIN CONTENT */
        .main {
            flex: 1;
            padding: 3rem 0;
        }

        /* FOOTER */
        .footer {
            background: var(--bg-header);
            padding: 4rem 0 2rem;
            border-top: 1px solid var(--border);
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 4rem;
            margin-bottom: 3rem;
        }

        .footer-logo {
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--logo-color);
            margin-bottom: 1rem;
            display: block;
            text-transform: uppercase;
        }

        .footer-desc {
            color: var(--text-secondary);
            line-height: 1.7;
            max-width: 600px;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            text-align: right;
        }

        .footer-links a {
            color: var(--text-main);
            font-weight: 500;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .footer-bottom {
            border-top: 1px solid var(--border);
            padding-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Utilities */
        .text-muted { color: var(--text-secondary); }

        /* Grid */
        .grid { display: grid; gap: 1.5rem; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        @media (max-width: 1024px) {
            .grid-4 { grid-template-columns: repeat(3, 1fr); }
            .nav-links { display: none; }
        }

        @media (max-width: 768px) {
            .grid-3, .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { grid-template-columns: 1fr; gap: 2rem; }
            .footer-links { text-align: left; }
        }

        @media (max-width: 480px) {
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
        }
    </style>

    @stack('styles')
</head>
<body>

    <header class="header">
        <div class="container flex items-center justify-between">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="logo">
                <svg width="113" height="18" viewBox="0 0 113 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M-2.38419e-07 0.312H11.328V3.288H3.72V7.104H10.416V10.032H3.72V14.112H11.544V17.112H-2.38419e-07V0.312ZM14.4536 13.368C15.7976 12.088 16.9736 11.12 17.9816 10.464C19.0056 9.792 20.0456 9.312 21.1016 9.024V8.184C20.1256 7.928 19.1416 7.488 18.1496 6.864C17.1736 6.24 16.0136 5.328 14.6696 4.128V0.312H26.3096V3.624C24.2616 3.64 22.7416 3.576 21.7496 3.432C20.7576 3.272 19.7336 2.96 18.6776 2.496L18.2216 3.576C19.2776 4.072 20.1656 4.552 20.8856 5.016C21.6216 5.464 22.5896 6.168 23.7896 7.128V10.08C22.4776 11.136 21.4456 11.912 20.6936 12.408C19.9416 12.888 19.0616 13.36 18.0536 13.824L18.5336 14.904C19.2696 14.568 19.9576 14.328 20.5976 14.184C21.2536 14.024 22.0216 13.912 22.9016 13.848C23.7816 13.784 24.9896 13.76 26.5256 13.776V17.112H14.4536V13.368ZM34.4691 8.52L28.9011 0.312H33.3891C34.5571 2.152 35.3491 3.512 35.7651 4.392C36.1971 5.272 36.4531 6.096 36.5331 6.864H37.5651C37.6451 6.096 37.8931 5.272 38.3091 4.392C38.7411 3.496 39.5411 2.136 40.7091 0.312H45.1731L39.6051 8.52L45.4611 17.112H40.8771C39.7091 15.304 38.8931 13.92 38.4291 12.96C37.9811 11.984 37.7011 11.072 37.5891 10.224H36.5091C36.3971 11.072 36.1091 11.984 35.6451 12.96C35.1971 13.92 34.3891 15.304 33.2211 17.112H28.6131L34.4691 8.52ZM46.9155 17.112L53.6835 0.312H57.8355L64.6035 17.112H60.6195L59.4675 14.184H52.0515L50.9235 17.112H46.9155ZM58.4355 11.256C57.6035 9.16 57.0595 7.64 56.8035 6.696C56.5475 5.736 56.3875 4.68 56.3235 3.528H55.2195C55.1555 4.68 54.9955 5.736 54.7395 6.696C54.4835 7.64 53.9395 9.16 53.1075 11.256H58.4355ZM69.2591 3.672H64.0031V0.312H78.2591V3.672H73.0031V17.112H69.2591V3.672ZM88.4173 17.424C86.7053 17.424 85.1453 17.048 83.7373 16.296C82.3453 15.528 81.2493 14.48 80.4493 13.152C79.6653 11.824 79.2733 10.344 79.2733 8.712C79.2733 7.08 79.6653 5.6 80.4493 4.272C81.2493 2.944 82.3453 1.904 83.7373 1.152C85.1453 0.384 86.7053 0 88.4173 0C90.1293 0 91.6813 0.384 93.0733 1.152C94.4813 1.904 95.5853 2.944 96.3853 4.272C97.1853 5.6 97.5853 7.08 97.5853 8.712C97.5853 10.344 97.1853 11.824 96.3853 13.152C95.5853 14.48 94.4813 15.528 93.0733 16.296C91.6813 17.048 90.1293 17.424 88.4173 17.424ZM88.4173 13.992C89.4413 13.992 90.3533 13.768 91.1533 13.32C91.9693 12.872 92.6013 12.248 93.0493 11.448C93.4973 10.648 93.7213 9.736 93.7213 8.712C93.7213 7.688 93.4973 6.776 93.0493 5.976C92.6013 5.176 91.9693 4.552 91.1533 4.104C90.3533 3.656 89.4413 3.432 88.4173 3.432C87.3933 3.432 86.4733 3.656 85.6573 4.104C84.8573 4.552 84.2333 5.176 83.7853 5.976C83.3373 6.776 83.1133 7.688 83.1133 8.712C83.1133 9.736 83.3373 10.648 83.7853 11.448C84.2333 12.248 84.8573 12.872 85.6573 13.32C86.4733 13.768 87.3933 13.992 88.4173 13.992ZM100.071 13.368C101.415 12.088 102.591 11.12 103.599 10.464C104.623 9.792 105.663 9.312 106.719 9.024V8.184C105.743 7.928 104.759 7.488 103.767 6.864C102.791 6.24 101.631 5.328 100.287 4.128V0.312H111.927V3.624C109.879 3.64 108.359 3.576 107.367 3.432C106.375 3.272 105.351 2.96 104.295 2.496L103.839 3.576C104.895 4.072 105.783 4.552 106.503 5.016C107.239 5.464 108.207 6.168 109.407 7.128V10.08C108.095 11.136 107.063 11.912 106.311 12.408C105.559 12.888 104.679 13.36 103.671 13.824L104.151 14.904C104.887 14.568 105.575 14.328 106.215 14.184C106.871 14.024 107.639 13.912 108.519 13.848C109.399 13.784 110.607 13.76 112.143 13.776V17.112H100.071V13.368Z" fill="currentColor"/>
                </svg>
            </a>

            <!-- Navigation Center -->
            <nav class="nav-links">
                <a href="{{ route('home') }}" class="nav-link">Главная</a>
                <a href="{{ route('books.index') }}" class="nav-link">Книги</a>
                <a href="{{ route('modules.index') }}" class="nav-link">Модули BQ</a>
                <a href="{{ route('software.index') }}" class="nav-link">Софт</a>
                <a href="{{ route('audio.index') }}" class="nav-link">Аудио</a>
                <a href="#" class="nav-link">Статьи</a>
            </nav>

            <!-- Right Actions -->
            <div class="header-actions">
                <a href="{{ route('search') }}" class="search-trigger">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Поиск
                </a>

                <!-- Theme Toggle -->
                <button type="button" class="theme-toggle" id="themeToggle" title="Переключить тему">
                    <!-- Sun icon (shown in dark mode) -->
                    <svg class="icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <!-- Moon icon (shown in light mode) -->
                    <svg class="icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                @auth
                    <a href="{{ route('dashboard') }}" class="btn-login">Кабинет</a>
                @else
                    <a href="{{ route('login') }}" class="btn-login">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Войти
                    </a>
                @endauth
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
            <div class="footer-grid">
                <div>
                    <a href="{{ route('home') }}" class="footer-logo">ESXATOS</a>
                    <p class="footer-desc">
                        Эсхатос: Доступ к Многообразию Богословия. Наш сайт не является коммерческой организацией, мы предлагаем широкий спектр богословских работ и мнений. Откройте для себя уникальные точки зрения, которые могут не всегда совпадать с нашими, но обогащают понимание. Эксклюзивный доступ к клубным материалам предоставляется после регистрации, подчеркивая личное богословское путешествие каждого члена. Мы уважаем авторские права: на сайте нет скачиваемых файлов, только ценный контент для обдумывания и личного использования.
                    </p>
                </div>
                <div class="footer-links">
                    <a href="{{ route('home') }}">Главная</a>
                    <a href="{{ route('books.index') }}">Книги</a>
                    <a href="{{ route('modules.index') }}">Модули BQ</a>
                    <a href="{{ route('software.index') }}">Софт</a>
                    <a href="{{ route('audio.index') }}">Аудио</a>
                    <a href="#">Статьи</a>
                </div>
            </div>
            <div class="footer-bottom">
                <div>
                    <div style="font-weight: 600; color: var(--text-main);">ESXATOS</div>
                    <div>&copy; 2012-{{ date('Y') }}</div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Theme toggle functionality
        document.getElementById('themeToggle').addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    </script>

    @stack('scripts')
</body>
</html>
