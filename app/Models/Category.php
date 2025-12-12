<?php

namespace App\Models;

use App\Http\Controllers\Admin\CacheController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        $clearCache = function () {
            foreach (array_keys(CacheController::CACHE_KEYS) as $key) {
                Cache::forget($key);
            }
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    protected $fillable = [
        'drupal_tid',
        'name',
        'slug',
        'description',
        'parent_id',
        'weight',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'weight' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('weight');
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_category');
    }

    public function getUrlAttribute(): string
    {
        return route('category.show', $this->slug);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
