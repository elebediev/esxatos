<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointCategory;
use App\Models\User;
use App\Models\UserPointTransaction;
use App\Services\PointsService;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function __construct(
        private PointsService $pointsService
    ) {}

    /**
     * Show all users with points
     */
    public function index(Request $request)
    {
        $query = User::query()->with('pointBalances.category');

        // Filter by minimum points
        if ($request->filled('min_points')) {
            $query->where('total_points', '>=', (int) $request->get('min_points'));
        }

        // Search by name/email
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Only show users with points or all
        if (!$request->filled('show_all')) {
            $query->where('total_points', '!=', 0);
        }

        $users = $query->orderBy('total_points', 'desc')
            ->paginate(30)
            ->withQueryString();

        $categories = PointCategory::where('is_active', true)->get();

        // Statistics
        $stats = [
            'total_users' => User::where('total_points', '>', 0)->count(),
            'total_points' => User::sum('total_points'),
            'active_subscriptions' => UserPointTransaction::where('status', 'approved')
                ->where('is_expired', false)
                ->whereNotNull('expires_at')
                ->where('expires_at', '>', now())
                ->distinct('user_id')
                ->count('user_id'),
        ];

        return view('admin.points.index', compact('users', 'categories', 'stats'));
    }

    /**
     * Show user's point history
     */
    public function userHistory(User $user, Request $request)
    {
        $query = $user->pointTransactions()
            ->with(['approver', 'category']);

        if ($categoryId = $request->get('category')) {
            $query->where('category_id', $categoryId);
        }

        if ($operation = $request->get('operation')) {
            $query->where('operation', $operation);
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        $categories = PointCategory::where('is_active', true)->get();
        $user->load('pointBalances.category');

        return view('admin.points.user-history', compact('user', 'transactions', 'categories'));
    }

    /**
     * Show form to add/deduct points
     */
    public function createTransaction(User $user)
    {
        $categories = PointCategory::where('is_active', true)
            ->where('slug', '!=', 'uncategorized')
            ->get();
        $user->load('pointBalances.category');

        return view('admin.points.create-transaction', compact('user', 'categories'));
    }

    /**
     * Store new transaction
     */
    public function storeTransaction(Request $request, User $user)
    {
        $validated = $request->validate([
            'points' => 'required|integer|not_in:0',
            'description' => 'required|string|max:1000',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // Default to "Пожертвование" category
        $donationCategory = PointCategory::where('slug', 'donation')->first();

        $this->pointsService->addPoints(
            user: $user,
            points: $validated['points'],
            approver: auth()->user(),
            categoryId: $donationCategory?->id,
            operation: UserPointTransaction::OPERATION_ADMIN,
            description: $validated['description'],
            expiresAt: $validated['expires_at'] ?? null
        );

        $action = $validated['points'] > 0 ? 'нараховано' : 'списано';
        $amount = abs($validated['points']);

        return redirect()
            ->route('admin.points.user-history', $user)
            ->with('success', "Успішно {$action} {$amount} балів");
    }

    /**
     * Cancel a transaction
     */
    public function cancelTransaction(Request $request, UserPointTransaction $transaction)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->pointsService->cancelTransaction(
            $transaction,
            auth()->user(),
            $validated['reason']
        );

        return back()->with('success', 'Транзакцію скасовано');
    }

    /**
     * Show edit form for a transaction
     */
    public function editTransaction(UserPointTransaction $transaction)
    {
        $transaction->load(['user', 'category', 'approver']);
        $categories = PointCategory::where('is_active', true)->get();

        return view('admin.points.edit-transaction', compact('transaction', 'categories'));
    }

    /**
     * Update a transaction
     */
    public function updateTransaction(Request $request, UserPointTransaction $transaction)
    {
        $validated = $request->validate([
            'points' => 'required|integer|not_in:0',
            'category_id' => 'nullable|exists:point_categories,id',
            'description' => 'required|string|max:1000',
            'expires_at' => 'nullable|date',
        ]);

        $oldPoints = $transaction->points;
        $oldCategoryId = $transaction->category_id;

        $transaction->update([
            'points' => $validated['points'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'expires_at' => $validated['expires_at'],
        ]);

        // Recalculate user points if points or category changed
        if ($oldPoints !== $validated['points'] || $oldCategoryId !== $validated['category_id']) {
            $this->pointsService->recalculateUserBalance($transaction->user);
        }

        return redirect()
            ->route('admin.points.user-history', $transaction->user)
            ->with('success', 'Транзакцію оновлено');
    }

    /**
     * All transactions list
     */
    public function transactions(Request $request)
    {
        $query = UserPointTransaction::with(['user', 'approver', 'category']);

        if ($search = $request->get('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($operation = $request->get('operation')) {
            $query->where('operation', $operation);
        }

        if ($categoryId = $request->get('category')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->get('type') === 'credit') {
            $query->where('points', '>', 0);
        } elseif ($request->get('type') === 'debit') {
            $query->where('points', '<', 0);
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        $categories = PointCategory::where('is_active', true)->get();

        return view('admin.points.transactions', compact('transactions', 'categories'));
    }

    /**
     * Manage categories
     */
    public function categories()
    {
        $categories = PointCategory::withCount(['userPoints', 'transactions'])->get();

        return view('admin.points.categories', compact('categories'));
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, PointCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return back()->with('success', 'Категорію оновлено');
    }
}
