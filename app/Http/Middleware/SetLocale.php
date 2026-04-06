<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Define o idioma da aplicação por pedido.
 *
 * Ordem de precedência: utilizador autenticado > sessão > Accept-Language > 'pt'.
 * A sessão é usada para utilizadores não autenticados (landing page, auth).
 */
class SetLocale
{
    private const SUPPORTED = ['pt', 'en', 'es'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // Preferência guardada na conta do utilizador
        if ($request->user() && in_array($request->user()->preferred_locale, self::SUPPORTED, true)) {
            return $request->user()->preferred_locale;
        }

        // Preferência guardada na sessão (utilizadores não autenticados ou após logout)
        $sessionLocale = session('locale');
        if ($sessionLocale && in_array($sessionLocale, self::SUPPORTED, true)) {
            return $sessionLocale;
        }

        // Primeiro idioma aceite pelo browser que seja suportado
        $browserLocale = substr($request->getPreferredLanguage(self::SUPPORTED) ?? 'pt', 0, 2);
        if (in_array($browserLocale, self::SUPPORTED, true)) {
            return $browserLocale;
        }

        return 'pt';
    }
}
