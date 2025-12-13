<?php

namespace App\Services;

use App\Models\PointCategory;
use App\Models\User;
use App\Models\UserPoint;
use App\Models\UserPointTransaction;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * Add points to a user
     */
    public function addPoints(
        User $user,
        int $points,
        ?User $approver = null,
        ?int $categoryId = null,
        string $operation = UserPointTransaction::OPERATION_ADMIN,
        ?string $description = null,
        ?\DateTimeInterface $expiresAt = null,
        ?string $reference = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): UserPointTransaction {
        return DB::transaction(function () use (
            $user, $points, $approver, $categoryId, $operation,
            $description, $expiresAt, $reference, $entityType, $entityId
        ) {
            $transaction = UserPointTransaction::create([
                'user_id' => $user->id,
                'approver_id' => $approver?->id,
                'category_id' => $categoryId,
                'points' => $points,
                'operation' => $operation,
                'description' => $description,
                'reference' => $reference,
                'status' => UserPointTransaction::STATUS_APPROVED,
                'expires_at' => $expiresAt,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);

            return $transaction;
        });
    }

    /**
     * Deduct points from a user
     */
    public function deductPoints(
        User $user,
        int $points,
        ?User $approver = null,
        ?int $categoryId = null,
        string $operation = UserPointTransaction::OPERATION_ADMIN,
        ?string $description = null
    ): UserPointTransaction {
        return $this->addPoints(
            $user,
            -abs($points),
            $approver,
            $categoryId,
            $operation,
            $description
        );
    }

    /**
     * Charge points for downloading a book
     */
    public function chargeForDownload(User $user, int $cost, int $bookId): ?UserPointTransaction
    {
        if (!$user->hasPoints($cost)) {
            return null;
        }

        $category = PointCategory::where('slug', 'donation')->first();

        return $this->addPoints(
            $user,
            -$cost,
            null,
            $category?->id,
            UserPointTransaction::OPERATION_DOWNLOAD,
            'Завантаження книги',
            null,
            null,
            'book',
            $bookId
        );
    }

    /**
     * Check and expire old transactions
     */
    public function processExpiredTransactions(): int
    {
        $expired = 0;

        UserPointTransaction::query()
            ->where('status', UserPointTransaction::STATUS_APPROVED)
            ->where('is_expired', false)
            ->where('points', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->chunk(100, function ($transactions) use (&$expired) {
                foreach ($transactions as $transaction) {
                    $transaction->expire();
                    $expired++;
                }
            });

        return $expired;
    }

    /**
     * Get user's transaction history
     */
    public function getUserTransactions(User $user, int $limit = 50, ?int $categoryId = null)
    {
        $query = $user->pointTransactions()
            ->with(['approver', 'category']);

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        return $query->paginate($limit);
    }

    /**
     * Get users with points sorted by balance
     */
    public function getUsersWithPoints(int $limit = 50)
    {
        return User::where('total_points', '>', 0)
            ->orderBy('total_points', 'desc')
            ->paginate($limit);
    }

    /**
     * Recalculate a single user's point balances
     */
    public function recalculateUserBalance(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Get all unique category combinations for this user
            $categories = UserPointTransaction::query()
                ->select('category_id')
                ->where('user_id', $user->id)
                ->where('status', UserPointTransaction::STATUS_APPROVED)
                ->groupBy('category_id')
                ->pluck('category_id');

            foreach ($categories as $categoryId) {
                $userPoint = UserPoint::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'category_id' => $categoryId,
                    ],
                    [
                        'points' => 0,
                        'max_points' => 0,
                    ]
                );

                $userPoint->recalculateFromTransactions();
            }

            // Update total points for user
            $totalPoints = UserPoint::where('user_id', $user->id)->sum('points');
            $user->update(['total_points' => $totalPoints]);
        });
    }

    /**
     * Recalculate all user point balances
     */
    public function recalculateAllBalances(): void
    {
        DB::transaction(function () {
            // Get all unique user/category combinations from transactions
            $combinations = UserPointTransaction::query()
                ->select('user_id', 'category_id')
                ->where('status', UserPointTransaction::STATUS_APPROVED)
                ->groupBy('user_id', 'category_id')
                ->get();

            foreach ($combinations as $combo) {
                $userPoint = UserPoint::firstOrCreate(
                    [
                        'user_id' => $combo->user_id,
                        'category_id' => $combo->category_id,
                    ],
                    [
                        'points' => 0,
                        'max_points' => 0,
                    ]
                );

                $userPoint->recalculateFromTransactions();
            }

            // Update total points for all users
            User::query()
                ->update([
                    'total_points' => DB::raw('(SELECT COALESCE(SUM(points), 0) FROM user_points WHERE user_points.user_id = users.id)')
                ]);
        });
    }

    /**
     * Cancel a transaction
     */
    public function cancelTransaction(
        UserPointTransaction $transaction,
        ?User $approver = null,
        ?string $reason = null
    ): UserPointTransaction {
        return DB::transaction(function () use ($transaction, $approver, $reason) {
            // Create reversal transaction
            $reversal = UserPointTransaction::create([
                'user_id' => $transaction->user_id,
                'approver_id' => $approver?->id,
                'category_id' => $transaction->category_id,
                'points' => -$transaction->points,
                'operation' => UserPointTransaction::OPERATION_REFUND,
                'description' => $reason ?? 'Скасування транзакції #' . $transaction->id,
                'status' => UserPointTransaction::STATUS_APPROVED,
                'parent_transaction_id' => $transaction->id,
            ]);

            // Mark original as cancelled
            $transaction->update(['status' => UserPointTransaction::STATUS_CANCELLED]);

            return $reversal;
        });
    }
}
