<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garante que o utilizador concluiu o onboarding antes de aceder à plataforma.
 * Redireciona para o fluxo de boas-vindas caso o campo `onboarding_completed_at` esteja vazio.
 */
class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && is_null(Auth::user()->onboarding_completed_at)) {
            // Evita loop de redirect — permite aceder às rotas do próprio onboarding e ao logout
            if (! $request->routeIs('onboarding.*') && ! $request->routeIs('logout')) {
                return redirect()->route('onboarding.index');
            }
        }

        return $next($request);
    }
}
