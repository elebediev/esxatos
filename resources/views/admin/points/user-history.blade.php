@extends('layouts.app')

@section('title', 'История баллов: ' . $user->name . ' - Админ-панель')

@section('content')
<div class="dashboard-page">
    @include('partials.dashboard-sidebar')

    <div class="dashboard-content">
        <div class="page-header">
            <div class="header-info">
                <a href="{{ route('admin.points.index') }}" class="back-link">&larr; К списку</a>
                <h1 class="dashboard-title">История баллов: {{ $user->name }}</h1>
            </div>
            <a href="{{ route('admin.points.create-transaction', $user) }}" class="action-btn">+ Начислить/Списать</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="user-balances">
            <div class="balance-card total">
                <div class="balance-label">Общий баланс</div>
                <div class="balance-value {{ $user->total_points >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($user->total_points, 0, '.', ' ') }} баллов
                </div>
            </div>
            @foreach($user->pointBalances as $balance)
            <div class="balance-card">
                <div class="balance-label">{{ $balance->category?->name ?? 'Без категории' }}</div>
                <div class="balance-value">{{ number_format($balance->points, 0, '.', ' ') }}</div>
            </div>
            @endforeach
        </div>

        <div class="filters-section">
            <form method="GET" action="{{ route('admin.points.user-history', $user) }}" class="filters-form">
                <div class="filter-group">
                    <select name="category" class="filter-select">
                        <option value="">Все категории</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <select name="operation" class="filter-select">
                        <option value="">Все операции</option>
                        <option value="admin" {{ request('operation') === 'admin' ? 'selected' : '' }}>Админ</option>
                        <option value="expiry" {{ request('operation') === 'expiry' ? 'selected' : '' }}>Истечение</option>
                        <option value="download" {{ request('operation') === 'download' ? 'selected' : '' }}>Скачивание</option>
                        <option value="refund" {{ request('operation') === 'refund' ? 'selected' : '' }}>Возврат</option>
                    </select>
                </div>
                <button type="submit" class="filter-btn">Фильтровать</button>
                @if(request()->hasAny(['category', 'operation']))
                    <a href="{{ route('admin.points.user-history', $user) }}" class="filter-reset">Сбросить</a>
                @endif
            </form>
        </div>

        <div class="transactions-table-wrap">
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Баллы</th>
                        <th>Категория</th>
                        <th>Операция</th>
                        <th>Описание</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                    <tr class="{{ $txn->status === 'cancelled' ? 'cancelled' : '' }}">
                        <td>{{ $txn->id }}</td>
                        <td>
                            @php $txnDate = $txn->created_at ?? $txn->drupal_created_at; @endphp
                            <div class="date-cell">
                                {{ $txnDate?->format('d.m.Y') ?? '—' }}
                                <span class="time">{{ $txnDate?->format('H:i') ?? '' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="points-value {{ $txn->points >= 0 ? 'positive' : 'negative' }}">
                                {{ $txn->points >= 0 ? '+' : '' }}{{ number_format($txn->points, 0, '.', ' ') }}
                            </span>
                        </td>
                        <td>{{ $txn->category?->name ?? '—' }}</td>
                        <td>
                            <span class="operation-badge operation-{{ $txn->operation }}">
                                {{ $txn->operation }}
                            </span>
                        </td>
                        <td class="description-cell">
                            {{ Str::limit($txn->description, 50) }}
                            @if($txn->expires_at)
                                <div class="expires-info {{ $txn->is_expired ? 'expired' : '' }}">
                                    {{ $txn->is_expired ? 'Истекло' : 'Истекает' }}: {{ $txn->expires_at->format('d.m.Y') }}
                                </div>
                            @endif
                            @if($txn->status === 'cancelled')
                                <div class="status-info cancelled">Отменено</div>
                            @elseif($txn->is_expired)
                                <div class="status-info expired">Истекло</div>
                            @endif
                        </td>
                        <td class="actions-cell">
                            @if($txn->status === 'approved' && !$txn->is_expired)
                                <a href="{{ route('admin.points.edit-transaction', $txn) }}" class="btn-icon" title="Редактировать">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                @if($txn->points != 0)
                                    <button type="button" class="btn-icon btn-icon-danger" title="Отменить" onclick="cancelTransaction({{ $txn->id }})">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="15" y1="9" x2="9" y2="15"/>
                                            <line x1="9" y1="9" x2="15" y2="15"/>
                                        </svg>
                                    </button>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="empty-message">Транзакции не найдены</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
        <nav class="pagination-wrapper">
            <div class="pagination">
                @if($transactions->onFirstPage())
                    <span class="pagination-link disabled">Назад</span>
                @else
                    <a href="{{ $transactions->previousPageUrl() }}" class="pagination-link">Назад</a>
                @endif

                @foreach($transactions->getUrlRange(max(1, $transactions->currentPage() - 2), min($transactions->lastPage(), $transactions->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}" class="pagination-link {{ $page == $transactions->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach

                @if($transactions->hasMorePages())
                    <a href="{{ $transactions->nextPageUrl() }}" class="pagination-link">Дальше</a>
                @else
                    <span class="pagination-link disabled">Дальше</span>
                @endif
            </div>
        </nav>
        @endif
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Отменить транзакцию</h3>
        <form id="cancelForm" method="POST">
            @csrf
            <div class="form-group">
                <label for="reason">Причина отмены:</label>
                <textarea name="reason" id="reason" required class="form-textarea" rows="3"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal()">Закрыть</button>
                <button type="submit" class="btn-danger">Отменить транзакцию</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
@include('partials.dashboard-styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }
    .back-link {
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        display: inline-block;
    }
    .back-link:hover { color: var(--primary); }
    .dashboard-title { margin: 0; }
    .action-btn {
        padding: 0.625rem 1.25rem;
        background: var(--primary);
        color: white;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .alert-success { background: #d1fae5; color: #065f46; }

    .user-balances {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .balance-card {
        background: var(--bg-card);
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 1px 3px var(--shadow);
    }
    .balance-card.total {
        background: var(--primary);
        color: white;
    }
    .balance-card.total .balance-label { color: rgba(255,255,255,0.8); }
    .balance-card.total .balance-value { color: white; }
    .balance-label { font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem; }
    .balance-value { font-size: 1.25rem; font-weight: 700; white-space: nowrap; }
    .balance-value.positive { color: #059669; }
    .balance-value.negative { color: #dc2626; }

    .filters-section { margin-bottom: 1.5rem; }
    .filters-form { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
    .filter-group { min-width: 150px; }
    .filter-select {
        padding: 0.625rem 1rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-main);
        font-size: 0.875rem;
    }
    .filter-btn {
        padding: 0.625rem 1.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }
    .filter-reset { color: var(--text-secondary); text-decoration: none; font-size: 0.875rem; }

    .transactions-table-wrap {
        background: var(--bg-card);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px var(--shadow);
    }
    .transactions-table { width: 100%; border-collapse: collapse; }
    .transactions-table th {
        text-align: left;
        padding: 1rem;
        background: var(--bg-secondary);
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
    }
    .transactions-table td {
        padding: 0.75rem 1rem;
        border-top: 1px solid var(--border);
        font-size: 0.875rem;
    }
    .transactions-table tr.cancelled { opacity: 0.6; }
    .transactions-table tbody tr:hover { background: var(--bg-secondary); }

    .date-cell .time { display: block; font-size: 0.75rem; color: var(--text-secondary); }
    .points-value { font-weight: 600; white-space: nowrap; }
    .points-value.positive { color: #059669; }
    .points-value.negative { color: #dc2626; }

    .operation-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .operation-admin { background: #dbeafe; color: #1e40af; }
    .operation-expiry { background: #fef3c7; color: #92400e; }
    .operation-download { background: #d1fae5; color: #065f46; }
    .operation-refund { background: #fee2e2; color: #991b1b; }

    .description-cell { max-width: 250px; }
    .expires-info { font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem; }
    .expires-info.expired { color: #dc2626; }
    .status-info { font-size: 0.75rem; margin-top: 0.25rem; font-weight: 500; }
    .status-info.cancelled { color: #991b1b; }
    .status-info.expired { color: #92400e; }

    .actions-cell {
        white-space: nowrap;
        text-align: right;
    }
    .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border);
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-icon:hover {
        background: var(--bg-secondary);
        color: var(--primary);
        border-color: var(--primary);
    }
    .btn-icon-danger:hover {
        background: #fee2e2;
        color: #dc2626;
        border-color: #dc2626;
    }

    .empty-message { text-align: center; color: var(--text-secondary); padding: 3rem !important; }

    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal-content {
        background: var(--bg-card);
        padding: 2rem;
        border-radius: 12px;
        width: 100%;
        max-width: 400px;
    }
    .modal-content h3 { margin: 0 0 1rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
    .form-textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-secondary);
        color: var(--text-main);
        resize: vertical;
    }
    .modal-actions { display: flex; gap: 0.75rem; justify-content: flex-end; }
    .btn-secondary {
        padding: 0.5rem 1rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border);
        border-radius: 8px;
        cursor: pointer;
    }
    .btn-danger {
        padding: 0.5rem 1rem;
        background: #dc2626;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }

    .pagination-wrapper { margin-top: 1.5rem; display: flex; justify-content: center; }
    .pagination { display: flex; align-items: center; gap: 0.5rem; }
    .pagination-link {
        display: flex; align-items: center; justify-content: center;
        min-width: 40px; height: 40px; padding: 0 0.75rem;
        border: 1px solid var(--border); border-radius: 8px;
        color: var(--text-secondary); font-size: 0.95rem; font-weight: 500;
        background: var(--bg-card); text-decoration: none;
    }
    .pagination-link:hover:not(.disabled):not(.active) { border-color: var(--primary); color: var(--primary); }
    .pagination-link.active { background: var(--primary); border-color: var(--primary); color: white; }
    .pagination-link.disabled { color: var(--text-muted); cursor: not-allowed; opacity: 0.5; }
</style>
@endpush

@push('scripts')
<script>
function cancelTransaction(id) {
    document.getElementById('cancelModal').style.display = 'flex';
    document.getElementById('cancelForm').action = '/admin/points/transactions/' + id + '/cancel';
}
function closeModal() {
    document.getElementById('cancelModal').style.display = 'none';
}
</script>
@endpush
