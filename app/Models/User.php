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
        'flames', 
        'current_streak', 
        'last_activity_at',
        'wants_weekly_summary', 
        'quiet_hours_start', 
        'quiet_hours_end'
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
            'last_activity_at' => 'datetime',
            'emotional_tags' => 'array',
            'hibernated_at' => 'datetime',
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

    // Relação com Conquistas
    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->orderBy('pivot_unlocked_at', 'desc');
    }

    // Helper para adicionar chamas
    public function addFlames(int $amount)
    {
        $this->increment('flames', $amount);
    }
    
    // Nível da Fogueira (Metáfora Visual)
    // Nível 1: Faísca (0-50), Nível 2: Chama (50-200), Nível 3: Fogueira (200+)
    public function getBonfireLevelAttribute()
    {
        if ($this->flames < 50) return 'spark';
        if ($this->flames < 200) return 'flame';
        if ($this->flames < 500) return 'bonfire';
        return 'beacon'; // Farol
    }

    public function buddySessions()
    {
        return $this->hasMany(BuddySession::class, 'buddy_id');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class)->orderBy('date', 'desc');
    }

    public function isHibernated(): bool
    {
        return !is_null($this->hibernated_at);
    }

    /**
     * Missões diárias atribuídas ao utilizador (Gamificação)
     */
    public function missions()
    {
        return $this->belongsToMany(\App\Models\Mission::class)
                    ->withPivot('progress', 'assigned_date', 'completed_at')
                    ->withTimestamps();
    }

    // Adiciona o método para verificar se está na hora de silêncio
    public function isInQuietHours(): bool
    {
        if (!$this->quiet_hours_start || !$this->quiet_hours_end) return false;

        $now = now()->format('H:i');
        $start = \Carbon\Carbon::parse($this->quiet_hours_start)->format('H:i');
        $end = \Carbon\Carbon::parse($this->quiet_hours_end)->format('H:i');

        if ($start > $end) { // Ex: 22:00 às 08:00 (cruza a meia-noite)
            return $now >= $start || $now <= $end;
        }

        return $now >= $start && $now <= $end;
    }
}