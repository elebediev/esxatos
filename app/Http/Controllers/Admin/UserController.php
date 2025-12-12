<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Http\Request;

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
        $user->load('roles');

        // Get user's message threads count
        $messageThreadsCount = MessageThread::whereHas('participants', fn($q) => $q->where('user_id', $user->id))->count();

        // Get user's uploaded books count
        $booksCount = $user->books()->count();

        // Get recent login logs
        $loginLogs = $user->loginLogs()->limit(20)->get();

        return view('admin.users.show', compact('user', 'messageThreadsCount', 'booksCount', 'loginLogs'));
    }
}
