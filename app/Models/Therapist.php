<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Therapist extends Model
{
    protected $fillable = ['name', 'specialty', 'approach', 'avatar'];

    /**
     * Pacientes atribuídos a este terapeuta via tabela pivot.
     */
    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'patient_therapist')
            ->withTimestamps();
    }
}
