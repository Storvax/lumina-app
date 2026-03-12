<?php

namespace App\Policies;

use App\Models\DailyLog;
use App\Models\User;

/**
 * Diário emocional — dados de saúde altamente sensíveis.
 *
 * Regra base: ninguém além do próprio utilizador acede às suas entradas,
 * independentemente do role. Nem moderadores, nem terapeutas (a partilha
 * terapêutica é feita de forma explícita pelo utilizador, não automaticamente).
 */
class DailyLogPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // Administradores técnicos podem aceder apenas para suporte RGPD —
        // mas mesmo estes passam pela lógica de policy para consistência.
        return null;
    }

    public function viewAny(User $user): bool
    {
        // Utilizador só vê os seus próprios registos; filtrado na query do controller.
        return true;
    }

    public function view(User $user, DailyLog $dailyLog): bool
    {
        return $user->id === $dailyLog->user_id;
    }

    public function create(User $user): bool
    {
        return !$user->isBanned();
    }

    public function update(User $user, DailyLog $dailyLog): bool
    {
        return $user->id === $dailyLog->user_id;
    }

    public function delete(User $user, DailyLog $dailyLog): bool
    {
        return $user->id === $dailyLog->user_id;
    }
}
