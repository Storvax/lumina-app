<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ShadowbanScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Se for admin/moderador, vê tudo. Não aplica o filtro.
        if (Auth::check() && Auth::user()->isModerator()) {
            return;
        }

        $table = $model->getTable();

        // Se for utilizador normal ou visitante:
        // Qualifica user_id com o nome da tabela para evitar ambiguidade
        // em queries com JOIN (ex: savedPosts via pivot saved_posts).
        $builder->whereHas('user', function ($query) {
            $query->whereNull('shadowbanned_until')
                  ->orWhere('shadowbanned_until', '<', now());
        });

        // O próprio utilizador deve ver os seus posts (para achar que não foi banido)
        if (Auth::check()) {
            $builder->orWhere("{$table}.user_id", Auth::id());
        }
    }
}