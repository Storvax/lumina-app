<?php

namespace App\Policies;

use App\Models\SelfAssessment;
use App\Models\User;

/**
 * Avaliações clínicas (PHQ-9, GAD-7) — dados de saúde protegidos.
 *
 * O utilizador acede sempre aos seus próprios resultados.
 * O terapeuta atribuído pode ver os resultados do seu paciente para
 * acompanhamento clínico — o acesso é explicitamente limitado à relação terapêutica.
 */
class SelfAssessmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SelfAssessment $selfAssessment): bool
    {
        if ($user->id === $selfAssessment->user_id) {
            return true;
        }

        // Terapeuta atribuído pode ver os resultados do seu paciente.
        if ($user->role === 'therapist') {
            $therapist = $user->therapistProfile;
            if ($therapist) {
                return $selfAssessment->user->therapists()
                    ->where('therapists.id', $therapist->id)
                    ->exists();
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        return !$user->isBanned();
    }

    public function delete(User $user, SelfAssessment $selfAssessment): bool
    {
        return $user->id === $selfAssessment->user_id;
    }
}
