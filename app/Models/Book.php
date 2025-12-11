<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'drupal_nid',
        'title',
        'slug',
        'description',
        'description_plain',
        'cover_image',
        'cover_alt',
        'user_id',
        'is_published',
        'views_count',
        'downloads_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'views_count' => 'integer',
            'downloads_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'book_category');
    }

    public function files(): HasMany
    {
        return $this->hasMany(BookFile::class)->orderBy('sort_order');
    }

    public function getUrlAttribute(): string
    {
        return route('book.show', $this->slug);
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }

        // If it's already a full URL
        if (str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }

        // For local files
        return asset('storage/' . $this->cover_image);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->whereRaw(
            "MATCH(title, description_plain) AGAINST(? IN NATURAL LANGUAGE MODE)",
            [$term]
        );
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
