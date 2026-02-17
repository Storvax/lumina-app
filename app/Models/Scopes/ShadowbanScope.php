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

        // Se for utilizador normal ou visitante:
        $builder->whereHas('user', function ($query) {
            $query->whereNull('shadowbanned_until')
                  ->orWhere('shadowbanned_until', '<', now());
        });

        // MAS: O próprio utilizador deve ver os seus posts (para achar que não foi banido)
        if (Auth::check()) {
            $builder->orWhere('user_id', Auth::id());
        }
    }
}