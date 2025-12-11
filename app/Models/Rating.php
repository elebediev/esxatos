<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'drupal_vote_id',
        'book_id',
        'user_id',
        'value',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::saved(function (Rating $rating) {
            $rating->book->updateRatingStats();
        });

        static::deleted(function (Rating $rating) {
            $rating->book->updateRatingStats();
        });
    }
}
