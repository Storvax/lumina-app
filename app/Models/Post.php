<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    // Relação com o Autor
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relação com Comentários
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Relação com Reações (Faltava isto!)
    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }
    public function isSavedBy(User $user)
    {
        return $this->reactions()->where('user_id', $user->id)->exists(); // (Isto é para reactions, ignora)
        // Usa antes isto na View ou Controller: $user->savedPosts->contains($post->id)
    }

    // Ativar o Scope automaticamente
    protected static function booted()
    {
        static::addGlobalScope(new ShadowbanScope);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(PostCheckin::class);
    }

    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'post_subscriptions');
    }

    // Helper: O user atual subscreveu?
    public function isSubscribedBy(User $user = null)
    {
        if (!$user) return false;
        return $this->subscribers()->where('user_id', $user->id)->exists();
    }
}