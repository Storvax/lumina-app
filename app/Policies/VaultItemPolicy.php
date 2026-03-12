<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VaultItem;

/**
 * Cofre pessoal ("Caixinha de Luz") — acesso estritamente restrito ao dono.
 *
 * O cofre armazena memórias positivas e pensamentos privados em momentos de crise.
 * Nenhum outro utilizador, moderador ou terapeuta deve aceder a este conteúdo.
 */
class VaultItemPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, VaultItem $vaultItem): bool
    {
        return $user->id === $vaultItem->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, VaultItem $vaultItem): bool
    {
        return $user->id === $vaultItem->user_id;
    }

    public function delete(User $user, VaultItem $vaultItem): bool
    {
        return $user->id === $vaultItem->user_id;
    }
}
