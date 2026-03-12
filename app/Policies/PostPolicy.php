<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Admins passam por todas as verificações sem restrições.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Apenas utilizadores autenticados e não banidos podem ver o feed.
     */
    public function viewAny(User $user): bool
    {
        return !$user->isBanned();
    }

    /**
     * Posts sensíveis só são visíveis pelo autor ou moderadores.
     */
    public function view(User $user, Post $post): bool
    {
        if ($post->is_sensitive && !$user->isModerator()) {
            return $user->id === $post->user_id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return !$user->isBanned() && !$user->isShadowbanned();
    }

    /**
     * Apenas o autor pode editar o seu post. Posts bloqueados não são editáveis.
     */
    public function update(User $user, Post $post): bool
    {
        if ($post->is_locked) {
            return false;
        }

        return $user->id === $post->user_id;
    }

    /**
     * Autor apaga o seu próprio post. Moderadores podem apagar qualquer um.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->isModerator();
    }

    /**
     * Fixar e bloquear posts é exclusivo a moderadores.
     */
    public function pin(User $user, Post $post): bool
    {
        return $user->isModerator();
    }

    public function lock(User $user, Post $post): bool
    {
        return $user->isModerator();
    }

    /**
     * Sumarização por IA disponível a moderadores e ao próprio autor.
     */
    public function summarize(User $user, Post $post): bool
    {
        return $user->isModerator() || $user->id === $post->user_id;
    }
}
