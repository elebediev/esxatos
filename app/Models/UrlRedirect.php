<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlRedirect extends Model
{
    protected $fillable = [
        'old_path',
        'new_path',
        'status_code',
        'hits',
        'last_hit_at',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }

    public function recordHit(): void
    {
        $this->increment('hits');
        $this->update(['last_hit_at' => now()]);
    }

    public static function findByPath(string $path): ?self
    {
        $path = ltrim($path, '/');
        return static::where('old_path', $path)->first();
    }
}
