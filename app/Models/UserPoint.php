<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'drupal_pid',
        'user_id',
        'category_id',
        'points',
        'max_points',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'max_points' => 'integer',
            'last_updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PointCategory::class, 'category_id');
    }

    /**
     * Recalculate points from transactions
     */
    public function recalculateFromTransactions(): void
    {
        $totalPoints = UserPointTransaction::where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->where('status', 'approved')
            ->where('is_expired', false)
            ->sum('points');

        $this->points = $totalPoints;
        $this->max_points = max($this->max_points, $totalPoints);
        $this->last_updated_at = now();
        $this->save();
    }
}
