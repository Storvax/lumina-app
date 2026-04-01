<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Therapist extends Model
{
    protected $fillable = ['user_id', 'name', 'specialty', 'approach', 'avatar'];

    /**
     * Conta de utilizador associada a este perfil de terapeuta.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
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
}
