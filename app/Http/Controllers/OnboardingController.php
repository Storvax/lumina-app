<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Fluxo de boas-vindas pós-registo.
 * 3 perguntas breves para personalizar a experiência e redirecionar o utilizador
 * para o módulo mais adequado ao seu estado atual.
 */
class OnboardingController extends Controller
{
    /**
     * Apresenta o formulário de onboarding (fullscreen, sem navbar).
     */
    public function index(): View|RedirectResponse
    {
        // Se já completou o onboarding, redireciona para o dashboard
        if (Auth::user()->onboarding_completed_at) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.index');
    }

    /**
     * Persiste as respostas do onboarding e redireciona para o destino contextual.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'intent'     => 'required|in:crisis,talk,write,learn,explore',
            'mood'       => 'required|in:1,2,3,4,5',
            'preference' => 'required|in:read,listen,talk,create',
        ]);

        $request->user()->forceFill([
            'onboarding_intent'       => $validated['intent'],
            'onboarding_mood'         => $validated['mood'],
            'onboarding_preference'   => $validated['preference'],
            'onboarding_completed_at' => now(),
        ])->save();

        // Redireciona para o módulo mais relevante com base na intenção declarada
        $destination = match ($validated['intent']) {
            'crisis'  => route('calm.crisis'),
            'talk'    => route('rooms.index'),
            'write'   => route('diary.index'),
            'learn'   => route('library.index'),
            default   => route('dashboard'),
        };

        return redirect($destination)->with('success', 'Bem-vindo(a) à Lumina. Estamos contigo.');
    }
}
