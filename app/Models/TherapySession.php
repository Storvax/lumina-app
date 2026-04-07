<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TherapySession extends Model
{
    protected $fillable = [
        'therapist_id',
        'patient_id',
        'scheduled_at',
        'duration_minutes',
        'status',
        'session_type',
        'patient_notes',
        'video_room_token',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Sessões que ainda não aconteceram e não foram canceladas.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_at', '>=', now());
    }

    /**
     * Verifica se a janela de acesso à videochamada está aberta
     * (10 minutos antes até 30 minutos após o início).
     */
    public function isVideoCallAccessible(): bool
    {
        if ($this->session_type !== 'video' || ! $this->video_room_token) {
            return false;
        }

        $windowStart = $this->scheduled_at->copy()->subMinutes(10);
        $windowEnd = $this->scheduled_at->copy()->addMinutes(30);

        return now()->between($windowStart, $windowEnd);
    }
}
