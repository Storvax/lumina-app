<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se está logado E se tem o método isBanned antes de o chamar
        // A verificação method_exists é uma segurança extra caso a cache falhe
        if (Auth::check()) {
            $user = Auth::user();
            
            if (method_exists($user, 'isBanned') && $user->isBanned()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'A tua conta foi suspensa por violar as regras da comunidade.',
                ]);
            }
        }

        return $next($request);
    }
}