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

    public const TYPE_BOOK = 'book';
    public const TYPE_MODULE = 'module';
    public const TYPE_SOFTWARE = 'software';
    public const TYPE_AUDIO = 'audio';

    protected $fillable = [
        'drupal_nid',
        'title',
        'slug',
        'content_type',
        'description',
        'description_plain',
        'cover_image',
        'cover_alt',
        'user_id',
        'is_published',
        'views_count',
        'downloads_count',
        'rating_average',
        'rating_count',
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

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->approved()->roots()->orderBy('created_at', 'desc');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
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

    public function scopeOfType($query, string $type)
    {
        return $query->where('content_type', $type);
    }

    public function scopeBooks($query)
    {
        return $query->where('content_type', self::TYPE_BOOK);
    }

    public function scopeModules($query)
    {
        return $query->where('content_type', self::TYPE_MODULE);
    }

    public function scopeSoftware($query)
    {
        return $query->where('content_type', self::TYPE_SOFTWARE);
    }

    public function scopeAudio($query)
    {
        return $query->where('content_type', self::TYPE_AUDIO);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function updateRatingStats(): void
    {
        $this->update([
            'rating_average' => $this->ratings()->avg('value') ?? 0,
            'rating_count' => $this->ratings()->count(),
        ]);
    }

    public function getRatingStarsAttribute(): float
    {
        // Convert 0-100 scale to 0-5 stars
        return round($this->rating_average / 20, 1);
    }
}
