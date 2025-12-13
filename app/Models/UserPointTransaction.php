<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPointTransaction extends Model
{
    use HasFactory;

    public const STATUS_APPROVED = 'approved';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';

    public const OPERATION_ADMIN = 'admin';
    public const OPERATION_EXPIRY = 'expiry';
    public const OPERATION_DOWNLOAD = 'download';
    public const OPERATION_REFUND = 'refund';

    protected $fillable = [
        'drupal_txn_id',
        'user_id',
        'approver_id',
        'category_id',
        'points',
        'operation',
        'description',
        'reference',
        'status',
        'expires_at',
        'is_expired',
        'parent_transaction_id',
        'entity_type',
        'entity_id',
        'drupal_created_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'is_expired' => 'boolean',
            'expires_at' => 'datetime',
            'drupal_created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PointCategory::class, 'category_id');
    }

    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(UserPointTransaction::class, 'parent_transaction_id');
    }

    public function childTransactions(): HasMany
    {
        return $this->hasMany(UserPointTransaction::class, 'parent_transaction_id');
    }

    /**
     * Scope for active (not expired) transactions
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->where('is_expired', false);
    }

    /**
     * Scope for expired transactions
     */
    public function scopeExpired($query)
    {
        return $query->where('is_expired', true);
    }

    /**
     * Check if transaction should be expired
     */
    public function shouldExpire(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && !$this->is_expired;
    }

    /**
     * Mark transaction as expired and create negative transaction
     */
    public function expire(): void
    {
        if ($this->is_expired) {
            return;
        }

        $this->is_expired = true;
        $this->save();

        // Only create reversal if points were positive
        if ($this->points > 0) {
            self::create([
                'user_id' => $this->user_id,
                'category_id' => $this->category_id,
                'points' => -$this->points,
                'operation' => self::OPERATION_EXPIRY,
                'status' => self::STATUS_APPROVED,
                'parent_transaction_id' => $this->id,
                'description' => 'Автоматичне закінчення терміну дії балів',
            ]);
        }
    }

    protected static function booted(): void
    {
        static::created(function (UserPointTransaction $transaction) {
            if ($transaction->status === self::STATUS_APPROVED) {
                $transaction->updateUserPoints();
            }
        });

        static::updated(function (UserPointTransaction $transaction) {
            if ($transaction->wasChanged('status') || $transaction->wasChanged('is_expired')) {
                $transaction->updateUserPoints();
            }
        });
    }

    /**
     * Update user's point balance after transaction
     */
    protected function updateUserPoints(): void
    {
        // Update category-specific points
        $userPoint = UserPoint::firstOrCreate(
            [
                'user_id' => $this->user_id,
                'category_id' => $this->category_id,
            ],
            [
                'points' => 0,
                'max_points' => 0,
            ]
        );

        $userPoint->recalculateFromTransactions();

        // Update total points on user
        $totalPoints = UserPoint::where('user_id', $this->user_id)->sum('points');
        User::where('id', $this->user_id)->update(['total_points' => $totalPoints]);
    }
}
