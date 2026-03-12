<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function create(User $user): bool
    {
        return !$user->isBanned();
    }

    /**
     * Apenas o autor pode editar a sua mensagem e só dentro de uma janela razoável.
     * Mensagens apagadas (soft delete) não podem ser editadas.
     */
    public function update(User $user, Message $message): bool
    {
        if ($message->trashed()) {
            return false;
        }

        return $user->id === $message->user_id;
    }

    /**
     * Autor ou moderador podem apagar uma mensagem (soft delete).
     */
    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->user_id || $user->isModerator();
    }

    /**
     * Fixar mensagens numa sala é exclusivo a moderadores.
     */
    public function pin(User $user, Message $message): bool
    {
        return $user->isModerator();
    }
}
