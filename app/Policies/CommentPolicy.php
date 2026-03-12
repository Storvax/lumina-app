<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
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
        return !$user->isBanned() && !$user->isShadowbanned();
    }

    /**
     * Apenas o autor pode editar o seu comentário.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    /**
     * Autor ou moderador podem apagar um comentário.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id || $user->isModerator();
    }

    /**
     * Marcar como útil é reservado a moderadores ou ao autor do post pai.
     */
    public function markHelpful(User $user, Comment $comment): bool
    {
        return $user->isModerator()
            || $user->id === $comment->post->user_id;
    }
}
