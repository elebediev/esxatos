@extends('layouts.app')

@section('title', 'Вход - Esxatos')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">Вход</h1>

        <!-- Session Status -->
        @if (session('status'))
            <div class="auth-status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Login (Email or Username) -->
            <div class="form-group">
                <label for="login" class="form-label">Email или логин</label>
                <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" class="form-input @error('login') error @enderror">
                @error('login')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" class="form-input @error('password') error @enderror">
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="remember" id="remember_me">
                    <span>Запомнить меня</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Войти</button>
            </div>

            <div class="auth-links">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Забыли пароль?</a>
                @endif
                <a href="{{ route('register') }}">Регистрация</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .auth-page { display: flex; justify-content: center; padding: 2rem 0; }
    .auth-card { background: var(--bg-card); border-radius: 12px; padding: 2.5rem; width: 100%; max-width: 420px; box-shadow: 0 4px 6px -1px var(--shadow); }
    .auth-title { font-size: 1.75rem; font-weight: 700; color: var(--text-main); margin-bottom: 2rem; text-align: center; }
    .auth-status { background: #dcfce7; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; }
    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 0.9rem; font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; }
    .form-input { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem; background: var(--bg-card); color: var(--text-main); transition: border-color 0.2s, box-shadow 0.2s; }
    .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .form-input.error { border-color: #ef4444; }
    .form-error { color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; }
    .form-checkbox { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; color: var(--text-secondary); }
    .form-checkbox input { width: 1rem; height: 1rem; accent-color: var(--primary); }
    .form-actions { margin-top: 1.5rem; }
    .btn-primary { width: 100%; padding: 0.875rem 1.5rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .btn-primary:hover { background: var(--primary-hover); }
    .auth-links { display: flex; justify-content: space-between; margin-top: 1.5rem; font-size: 0.9rem; }
    .auth-links a { color: var(--primary); }
    .auth-links a:hover { text-decoration: underline; }
    @media (max-width: 480px) { .auth-card { padding: 1.5rem; } }
</style>
@endpush
