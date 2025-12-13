@extends('layouts.app')

@section('title', 'Управління кешем - Адмін-панель')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <h1 class="dashboard-title">Управління кешем</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="cache-actions">
            <form method="POST" action="{{ route('admin.cache.clear-all') }}" class="inline-form">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('Очистити весь кеш?')">
                    Очистити весь кеш
                </button>
            </form>
        </div>

        <div class="cache-table-wrap">
            <table class="cache-table">
                <thead>
                    <tr>
                        <th>Ключ кешу</th>
                        <th>Опис</th>
                        <th>Статус</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cacheItems as $key => $item)
                        <tr>
                            <td><code>{{ $key }}</code></td>
                            <td>{{ $item['description'] }}</td>
                            <td>
                                @if($item['exists'])
                                    <span class="status-badge status-active">Закешовано</span>
                                @else
                                    <span class="status-badge status-inactive">Порожньо</span>
                                @endif
                            </td>
                            <td>
                                @if($item['exists'])
                                    <form method="POST" action="{{ route('admin.cache.clear', $key) }}" class="inline-form">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary">Очистити</button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="cache-info">
            <p><strong>Примітка:</strong> Кеш автоматично оновлюється при зміні категорій. Використовуйте ручне очищення тільки якщо потрібно негайно побачити зміни.</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('partials.dashboard-styles')
<style>
    .cache-actions { margin-bottom: 2rem; }
    .inline-form { display: inline; }
    .cache-table-wrap { overflow-x: auto; margin-bottom: 2rem; }
    .cache-table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 8px; overflow: hidden; }
    .cache-table th, .cache-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); }
    .cache-table th { background: var(--bg-secondary); font-weight: 600; color: var(--text-main); }
    .cache-table td { color: var(--text-secondary); }
    .cache-table code { background: var(--bg-secondary); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem; }
    .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
    .status-active { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
    .status-inactive { background: rgba(107, 114, 128, 0.1); color: #6b7280; }
    .btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.9rem; font-weight: 500; cursor: pointer; border: none; transition: all 0.2s; }
    .btn-danger { background: #ef4444; color: white; }
    .btn-danger:hover { background: #dc2626; }
    .btn-secondary { background: var(--bg-secondary); color: var(--text-main); border: 1px solid var(--border); }
    .btn-secondary:hover { background: var(--border); }
    .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
    .cache-info { padding: 1rem; background: var(--bg-secondary); border-radius: 8px; color: var(--text-secondary); font-size: 0.9rem; }
    .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
    .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
    .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    .text-muted { color: var(--text-muted); }
</style>
@endpush
