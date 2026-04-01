<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ClinicalNote;
use App\Models\User;

class ClinicalNotePolicy
{
    /**
     * Apenas terapeutas podem criar notas clínicas.
     */
    public function create(User $user): bool
    {
        return $user->role === 'therapist' && $user->therapistProfile !== null;
    }

    /**
     * Apenas o terapeuta que escreveu a nota pode ver, editar ou apagar.
     * Não existe partilha de notas entre terapeutas para proteger a confidencialidade.
     */
    public function view(User $user, ClinicalNote $note): bool
    {
        return $user->therapistProfile?->id === $note->therapist_id;
    }

    public function update(User $user, ClinicalNote $note): bool
    {
        return $user->therapistProfile?->id === $note->therapist_id;
    }

    public function delete(User $user, ClinicalNote $note): bool
    {
        return $user->therapistProfile?->id === $note->therapist_id;
    }
}
