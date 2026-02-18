<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'role',
        'shadowbanned_until',
        'read_receipts_enabled', // <--- NOVO
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'banned_at' => 'datetime',
            'shadowbanned_until' => 'datetime',
            'read_receipts_enabled' => 'boolean', // <--- Importante
        ];
    }

    // Acesso ao Painel Admin
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isModerator(): bool { return in_array($this->role, ['admin', 'moderator']); }
    
    // Outras relações do teu projeto
    public function posts() { return $this->hasMany(Post::class); }
    public function isBanned(): bool { return $this->banned_at !== null; }
    public function savedPosts() { return $this->belongsToMany(Post::class, 'saved_posts')->withTimestamps(); }
    public function isShadowbanned() { return $this->shadowbanned_until && $this->shadowbanned_until->isFuture(); }
    public function subscribedPosts() { return $this->belongsToMany(Post::class, 'post_subscriptions'); }
}