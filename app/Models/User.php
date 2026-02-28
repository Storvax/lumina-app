<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Carbon\Carbon;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes, HasPushSubscriptions;

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
        'quiet_hours_end',
        'diary_retention_days',
        'a11y_dyslexic_font',
        'a11y_reduced_motion',
        'a11y_text_size'
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
            'read_receipts_enabled' => 'boolean',
            'last_activity_at' => 'datetime',
            'emotional_tags' => 'array',
            'hibernated_at' => 'datetime',
            'a11y_dyslexic_font' => 'boolean',
            'a11y_reduced_motion' => 'boolean',
        ];
    }

    /**
     * Gera um pseudónimo consistente e irreversível baseado no ID do utilizador.
     * Útil para o Fórum, garantindo continuidade nas interações sem expor a identidade.
     */
    public function getPseudonymAttribute(): string
    {
        $salt = config('app.key');
        $hash = substr(hash('sha256', $this->id . $salt), 0, 6);
        return 'Lumina-' . strtoupper($hash);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    public function isShadowbanned()
    {
        return $this->shadowbanned_until && $this->shadowbanned_until->isFuture();
    }

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

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->orderBy('pivot_unlocked_at', 'desc');
    }

    public function addFlames(int $amount)
    {
        $this->increment('flames', $amount);
    }
    
    public function getBonfireLevelAttribute()
    {
        if ($this->flames < 50) return 'spark';
        if ($this->flames < 200) return 'flame';
        if ($this->flames < 500) return 'bonfire';
        return 'beacon'; 
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

    public function missions()
    {
        return $this->belongsToMany(\App\Models\Mission::class)
                    ->withPivot('progress', 'assigned_date', 'completed_at')
                    ->withTimestamps();
    }

    public function dailyLogs()
    {
        return $this->hasMany(DailyLog::class);
    }

    public function isInQuietHours(): bool
    {
        if (!$this->quiet_hours_start || !$this->quiet_hours_end) return false;

        $now = now()->format('H:i');
        $start = Carbon::parse($this->quiet_hours_start)->format('H:i');
        $end = Carbon::parse($this->quiet_hours_end)->format('H:i');

        if ($start > $end) { 
            return $now >= $start || $now <= $end;
        }

        return $now >= $start && $now <= $end;
    }

    public function playlistSongs()
    {
        return $this->hasMany(PlaylistSong::class);
    }
}