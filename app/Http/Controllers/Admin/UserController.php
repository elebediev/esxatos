<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('roles');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->get('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $role));
        }

        // Filter by status
        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['roles', 'pointBalances.category']);

        // Get user's message threads count
        $messageThreadsCount = MessageThread::whereHas('participants', fn($q) => $q->where('user_id', $user->id))->count();

        // Get user's uploaded books count
        $booksCount = $user->books()->count();

        // Get recent login logs
        $loginLogs = $user->loginLogs()->limit(20)->get();

        // Get all available roles
        $allRoles = Role::orderBy('name')->pluck('name');

        return view('admin.users.show', compact('user', 'messageThreadsCount', 'booksCount', 'loginLogs', 'allRoles'));
    }

    public function updateRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user->syncRoles($validated['roles'] ?? []);

        return back()->with('success', 'Роли пользователя обновлены');
    }

    public function toggleStatus(User $user)
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Вы не можете деактивировать свой аккаунт');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'активирован' : 'деактивирован';

        return back()->with('success', "Аккаунт пользователя {$status}");
    }
}
