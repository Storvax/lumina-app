<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Obriga utilizadores com roles privilegiadas (admin, moderador, terapeuta, hr_admin)
 * a concluir a configuração de 2FA antes de acederem a rotas protegidas.
 * Redireciona para o ecrã de configuração enquanto o 2FA não estiver confirmado.
 */
class RequiresTwoFactor
{
    private const PRIVILEGED_ROLES = ['admin', 'moderator', 'therapist', 'hr_admin'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role, self::PRIVILEGED_ROLES, true)) {
            return $next($request);
        }

        // Permitir acesso às próprias rotas de setup/verificação de 2FA para evitar loop.
        if ($request->routeIs('two-factor.*')) {
            return $next($request);
        }

        if (!$user->two_factor_confirmed) {
            return redirect()->route('two-factor.setup')
                ->with('warning', 'A tua conta requer autenticação de dois fatores. Por favor, configura o 2FA para continuar.');
        }

        // Verificar sessão de 2FA: após login, o utilizador ainda tem de introduzir o código TOTP.
        if (!session('two_factor_verified')) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
