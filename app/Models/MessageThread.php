<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MessageThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'drupal_thread_id',
        'subject',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'thread_id')->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'thread_id')->latestOfMany();
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_thread_participants', 'thread_id')
            ->withPivot(['is_read', 'is_deleted', 'last_read_at'])
            ->withTimestamps();
    }

    public function isUnreadFor(User $user): bool
    {
        $participant = $this->participants()->where('user_id', $user->id)->first();
        return $participant && !$participant->pivot->is_read;
    }

    public function markAsReadFor(User $user): void
    {
        $this->participants()->updateExistingPivot($user->id, [
            'is_read' => true,
            'last_read_at' => now(),
        ]);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('is_deleted', false);
        });
    }

    public function scopeWithUnread($query, User $user)
    {
        return $query->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('is_read', false);
        });
    }
}
