<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\ShadowbanScope;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'tag',
        'is_sensitive',
        'support_count',
        'is_pinned',
        'is_locked',
        'risk_level',
        'sentiment',
        'ai_summary',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ShadowbanScope);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(PostCheckin::class);
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_subscriptions');
    }

    public function isSubscribedBy(?User $user = null): bool
    {
        if (!$user) return false;
        return $this->subscribers()->where('user_id', $user->id)->exists();
    }
}
