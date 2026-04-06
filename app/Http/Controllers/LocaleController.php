<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Gere a troca de idioma da interface.
 * Persiste na conta se autenticado, caso contrário usa apenas a sessão.
 */
class LocaleController extends Controller
{
    private const SUPPORTED = ['pt', 'en', 'es'];

    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, self::SUPPORTED, true)) {
            abort(404);
        }

        session(['locale' => $locale]);

        if (Auth::check()) {
            Auth::user()->update(['preferred_locale' => $locale]);
        }

        return back();
    }
}
