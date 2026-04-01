<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

/**
 * Gere o ciclo de vida do 2FA para contas privilegiadas:
 * setup (gerar segredo + QR Code), confirmação de ativação e challenge pós-login.
 */
class TwoFactorController extends Controller
{
    public function __construct(private Google2FA $google2fa) {}

    /**
     * Ecrã de configuração inicial do 2FA.
     * Gera um novo segredo se o utilizador ainda não tiver um.
     */
    public function setup(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->two_factor_confirmed) {
            return redirect()->route('dashboard')->with('info', 'O 2FA já está ativo na tua conta.');
        }

        if (!$user->two_factor_secret) {
            $user->update(['two_factor_secret' => $this->google2fa->generateSecretKey()]);
        }

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret,
        );

        return view('auth.two-factor.setup', compact('qrCodeUrl'));
    }

    /**
     * Confirma a ativação do 2FA verificando o primeiro código TOTP introduzido.
     * Só após validação bem-sucedida o 2FA fica marcado como confirmado.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|digits:6']);

        $user = Auth::user();

        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->input('code'),
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'Código inválido. Verifica o teu autenticador e tenta novamente.']);
        }

        $user->update(['two_factor_confirmed' => true]);
        session(['two_factor_verified' => true]);

        return redirect()->route('dashboard')->with('success', 'Autenticação de dois fatores ativada com sucesso.');
    }

    /**
     * Desativa o 2FA. Requer confirmação de password para evitar desativação acidental.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|current_password']);

        Auth::user()->update([
            'two_factor_secret'    => null,
            'two_factor_confirmed' => false,
        ]);

        return back()->with('success', 'Autenticação de dois fatores desativada.');
    }

    /**
     * Ecrã de challenge pós-login: introdução do código TOTP.
     */
    public function challenge(): View|RedirectResponse
    {
        if (session('two_factor_verified')) {
            return redirect()->route('dashboard');
        }

        return view('auth.two-factor.challenge');
    }

    /**
     * Valida o código TOTP introduzido no challenge pós-login.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|digits:6']);

        $user = Auth::user();

        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->input('code'),
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'Código inválido. Tenta novamente.']);
        }

        session(['two_factor_verified' => true]);

        return redirect()->intended(route('dashboard'));
    }
}
