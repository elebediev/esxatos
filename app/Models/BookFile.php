<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookFile extends Model
{
    use HasFactory;

    // Access level constants
    public const ACCESS_PUBLIC = 'public';           // Available to everyone
    public const ACCESS_AUTHENTICATED = 'authenticated'; // Logged in users only
    public const ACCESS_CLUB = 'club';               // Club members and above
    public const ACCESS_AIDE = 'aide';               // Aide and admin only
    public const ACCESS_ADMIN = 'admin';             // Admin only

    protected $fillable = [
        'book_id',
        'title',
        'url',
        'file_type',
        'file_size',
        'downloads_count',
        'sort_order',
        'access_level',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'downloads_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return '';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function incrementDownloads(): void
    {
        $this->increment('downloads_count');
        $this->book->increment('downloads_count');
    }

    /**
     * Check if the given user can access this file
     */
    public function isAccessibleBy(?\App\Models\User $user): bool
    {
        return match ($this->access_level) {
            self::ACCESS_PUBLIC => true,
            self::ACCESS_AUTHENTICATED => $user !== null,
            self::ACCESS_CLUB => $user?->hasAnyRole(['club', 'aide', 'admin']) ?? false,
            self::ACCESS_AIDE => $user?->hasAnyRole(['aide', 'admin']) ?? false,
            self::ACCESS_ADMIN => $user?->hasRole('admin') ?? false,
            default => true,
        };
    }

    /**
     * Get human-readable access level label
     */
    public function getAccessLabelAttribute(): string
    {
        return match ($this->access_level) {
            self::ACCESS_PUBLIC => 'Для всех',
            self::ACCESS_AUTHENTICATED => 'Для зарегистрированных',
            self::ACCESS_CLUB => 'Для членов клуба',
            self::ACCESS_AIDE => 'Для помощников',
            self::ACCESS_ADMIN => 'Только для админов',
            default => 'Для всех',
        };
    }

    /**
     * Get all available access levels
     */
    public static function getAccessLevels(): array
    {
        return [
            self::ACCESS_PUBLIC => 'Для всех',
            self::ACCESS_AUTHENTICATED => 'Для зарегистрированных',
            self::ACCESS_CLUB => 'Для членов клуба',
            self::ACCESS_AIDE => 'Для помощников',
            self::ACCESS_ADMIN => 'Только для админов',
        ];
    }
}
