<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Therapist extends Model
{
    protected $fillable = ['user_id', 'name', 'specialty', 'approach', 'avatar'];

    /**
     * Conta de utilizador associada a este perfil de terapeuta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pacientes atribuídos a este terapeuta via tabela pivot.
     */
    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'patient_therapist')
            ->withTimestamps();
    }

    /**
     * Horários de disponibilidade configurados pelo terapeuta.
     */
    public function availability(): HasMany
    {
        return $this->hasMany(TherapistAvailability::class);
    }

    /**
     * Sessões terapêuticas agendadas com pacientes.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(TherapySession::class);
    }
}
