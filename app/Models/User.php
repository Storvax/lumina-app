<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'shadowbanned_until',
        'role',
        'read_receipts_enabled',
        'chat_view_mode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'banned_at' => 'datetime',
            'shadowbanned_until' => 'datetime',
            'read_receipts_enabled' => 'boolean',
        ];
    }

    // --- LÓGICA DE ACESSO AO FILAMENT (ADMIN) ---
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    // --- MÉTODOS DE PAPÉIS (ROLES) ---
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    // --- MÉTODOS DE BANIMENTO E SEGURANÇA ---
    
    /**
     * Verifica se o utilizador está banido permanentemente.
     */
    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    /**
     * Verifica se o utilizador está em shadowban (invisível para outros).
     */
    public function isShadowbanned()
    {
        return $this->shadowbanned_until && $this->shadowbanned_until->isFuture();
    }

    // --- RELAÇÕES ---

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function savedPosts()
    {
        return $this->belongsToMany(Post::class, 'saved_posts')->withTimestamps();
    }

    public function subscribedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_subscriptions');
    }

    public function presenceSubscriptions()
    {
        return $this->hasMany(PresenceSubscription::class);
    }
}