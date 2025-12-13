<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'drupal_tid',
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function userPoints(): HasMany
    {
        return $this->hasMany(UserPoint::class, 'category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(UserPointTransaction::class, 'category_id');
    }

    public static function getDefaultCategory(): ?self
    {
        return self::where('slug', 'donation')->first();
    }
}
