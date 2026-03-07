<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe acesso ao dashboard corporativo.
 * Apenas utilizadores com role `hr_admin` e empresa associada.
 */
class CorporateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'hr_admin' || !$user->company_id) {
            abort(403, 'Acesso restrito a administradores de RH.');
        }

        return $next($request);
    }
}
