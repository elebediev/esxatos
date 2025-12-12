@extends('layouts.app')

@section('title', 'Настройки профиля - Esxatos')

@section('content')
<div class="dashboard-page">
    <div class="dashboard-sidebar">
        <nav class="dashboard-nav">
            <a href="{{ route('dashboard') }}" class="dashboard-nav-link">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Главная
            </a>
            <a href="{{ route('profile.edit') }}" class="dashboard-nav-link active">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Профиль
            </a>
            <form method="POST" action="{{ route('logout') }}" class="dashboard-nav-form">
                @csrf
                <button type="submit" class="dashboard-nav-link logout">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Выйти
                </button>
            </form>
        </nav>
    </div>

    <div class="dashboard-content">
        <h1 class="dashboard-title">Настройки профиля</h1>

        <!-- Profile Information -->
        <div class="profile-section">
            <div class="profile-section-header">
                <h2>Информация профиля</h2>
                <p>Обновите имя и email вашего аккаунта.</p>
            </div>

            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}" class="profile-form">
                @csrf
                @method('patch')

                <div class="form-group">
                    <label for="name" class="form-label">Имя</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="form-input @error('name') error @enderror">
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="form-input @error('email') error @enderror">
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="verification-notice">
                            <p>Ваш email не подтвержден.
                                <button form="send-verification" class="verification-link">Нажмите, чтобы отправить письмо повторно.</button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="verification-sent">Новая ссылка для подтверждения отправлена на ваш email.</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Сохранить</button>
                    @if (session('status') === 'profile-updated')
                        <span class="save-notice">Сохранено!</span>
                    @endif
                </div>
            </form>
        </div>

        <!-- Update Password -->
        <div class="profile-section">
            <div class="profile-section-header">
                <h2>Изменить пароль</h2>
                <p>Используйте надежный пароль для защиты аккаунта.</p>
            </div>

            <form method="post" action="{{ route('password.update') }}" class="profile-form">
                @csrf
                @method('put')

                <div class="form-group">
                    <label for="update_password_current_password" class="form-label">Текущий пароль</label>
                    <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="form-input @error('current_password', 'updatePassword') error @enderror">
                    @error('current_password', 'updatePassword')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="update_password_password" class="form-label">Новый пароль</label>
                    <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="form-input @error('password', 'updatePassword') error @enderror">
                    @error('password', 'updatePassword')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="update_password_password_confirmation" class="form-label">Подтвердите пароль</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="form-input">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Обновить пароль</button>
                    @if (session('status') === 'password-updated')
                        <span class="save-notice">Пароль обновлен!</span>
                    @endif
                </div>
            </form>
        </div>

        <!-- Delete Account -->
        <div class="profile-section profile-section-danger">
            <div class="profile-section-header">
                <h2>Удалить аккаунт</h2>
                <p>После удаления аккаунта все данные будут безвозвратно удалены. Сохраните важную информацию перед удалением.</p>
            </div>

            <button type="button" class="btn-danger" onclick="document.getElementById('delete-modal').classList.add('active')">
                Удалить аккаунт
            </button>

            <!-- Delete Modal -->
            <div id="delete-modal" class="modal-overlay">
                <div class="modal-content">
                    <form method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')

                        <h3>Вы уверены, что хотите удалить аккаунт?</h3>
                        <p>После удаления все данные будут безвозвратно потеряны. Введите пароль для подтверждения.</p>

                        <div class="form-group">
                            <label for="delete_password" class="form-label">Пароль</label>
                            <input id="delete_password" name="password" type="password" placeholder="Введите пароль" class="form-input @error('password', 'userDeletion') error @enderror">
                            @error('password', 'userDeletion')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn-secondary" onclick="document.getElementById('delete-modal').classList.remove('active')">Отмена</button>
                            <button type="submit" class="btn-danger">Удалить аккаунт</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .dashboard-page { display: grid; grid-template-columns: 220px 1fr; gap: 2rem; }
    .dashboard-sidebar { }
    .dashboard-nav { display: flex; flex-direction: column; gap: 0.25rem; }
    .dashboard-nav-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 8px; color: var(--text-secondary); font-weight: 500; transition: all 0.2s; }
    .dashboard-nav-link:hover { background: var(--bg-secondary); color: var(--text-main); }
    .dashboard-nav-link.active { background: var(--primary); color: white; }
    .dashboard-nav-link.logout { width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-size: 1rem; font-family: inherit; }
    .dashboard-nav-link.logout:hover { background: #fee2e2; color: #dc2626; }
    .dashboard-nav-form { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); }
    .dashboard-content { }
    .dashboard-title { font-size: 1.75rem; font-weight: 700; color: var(--text-main); margin-bottom: 2rem; }

    .profile-section { background: var(--bg-card); padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; box-shadow: 0 1px 3px var(--shadow); }
    .profile-section-header { margin-bottom: 1.5rem; }
    .profile-section-header h2 { font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem; }
    .profile-section-header p { color: var(--text-secondary); font-size: 0.9rem; }
    .profile-section-danger { border: 1px solid #fecaca; }
    .profile-section-danger .profile-section-header h2 { color: #dc2626; }

    .profile-form { max-width: 400px; }
    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 0.9rem; font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; }
    .form-input { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem; background: var(--bg-card); color: var(--text-main); transition: border-color 0.2s, box-shadow 0.2s; }
    .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .form-input.error { border-color: #ef4444; }
    .form-error { color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; }

    .form-actions { display: flex; align-items: center; gap: 1rem; margin-top: 1.5rem; }
    .btn-primary { padding: 0.75rem 1.5rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .btn-primary:hover { background: var(--primary-hover); }
    .btn-secondary { padding: 0.75rem 1.5rem; background: var(--bg-secondary); color: var(--text-main); border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .btn-secondary:hover { background: var(--border); }
    .btn-danger { padding: 0.75rem 1.5rem; background: #dc2626; color: white; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .btn-danger:hover { background: #b91c1c; }

    .save-notice { color: var(--text-secondary); font-size: 0.9rem; }
    .verification-notice { margin-top: 0.75rem; padding: 0.75rem; background: #fef3c7; border-radius: 6px; font-size: 0.85rem; color: #92400e; }
    .verification-link { color: #92400e; text-decoration: underline; background: none; border: none; cursor: pointer; font-size: inherit; }
    .verification-sent { color: #166534; margin-top: 0.5rem; }

    /* Modal */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; }
    .modal-overlay.active { display: flex; }
    .modal-content { background: var(--bg-card); padding: 2rem; border-radius: 12px; max-width: 450px; width: 90%; }
    .modal-content h3 { font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; }
    .modal-content > p { color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }

    @media (max-width: 768px) {
        .dashboard-page { grid-template-columns: 1fr; }
        .dashboard-sidebar { order: 2; }
        .dashboard-nav { flex-direction: row; flex-wrap: wrap; }
        .dashboard-nav-form { margin: 0; padding: 0; border: none; }
        .profile-form { max-width: 100%; }
    }
</style>
@endpush

@if($errors->userDeletion->isNotEmpty())
@push('scripts')
<script>
    document.getElementById('delete-modal').classList.add('active');
</script>
@endpush
@endif
