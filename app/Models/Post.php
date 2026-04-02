<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\Scopes\ShadowbanScope;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'audio_path',
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

        static::creating(function (Post $post): void {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title) . '-' . Str::random(6);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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

    /** Filtra publicações que ainda não foram trancadas. */
    public function scopeUnlocked(Builder $query): Builder
    {
        return $query->where('is_locked', false);
    }
}
