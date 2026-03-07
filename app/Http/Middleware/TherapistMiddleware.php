<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe acesso a rotas exclusivas de terapeutas.
 * O role 'therapist' é atribuído após validação profissional.
 */
class TherapistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->role !== 'therapist') {
            abort(403, 'Acesso restrito a terapeutas credenciados.');
        }

        return $next($request);
    }
}
