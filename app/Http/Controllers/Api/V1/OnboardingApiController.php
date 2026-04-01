<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingApiController extends Controller
{
    /**
     * Guardar respostas do onboarding e marcar como concluído.
     * POST /api/v1/onboarding
     *
     * Replica a lógica do OnboardingController web mas para API móvel,
     * sem redirecionamentos — devolve o utilizador atualizado.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'intent'     => ['required', 'in:crisis,talk,write,learn,explore'],
            'mood'       => ['required', 'in:1,2,3,4,5'],
            'preference' => ['required', 'in:read,listen,talk,create'],
        ]);

        $user = $request->user('sanctum');

        $user->update([
            'onboarding_intent'       => $validated['intent'],
            'onboarding_mood'         => $validated['mood'],
            'onboarding_preference'   => $validated['preference'],
            'onboarding_completed_at' => now(),
        ]);

        // Devolve o destino sugerido com base na intenção do utilizador
        $redirect = match ($validated['intent']) {
            'crisis'  => 'calm_zone',
            'talk'    => 'community',
            'write'   => 'diary',
            'learn'   => 'library',
            default   => 'dashboard',
        };

        return response()->json([
            'redirect' => $redirect,
            'user'     => new UserResource($user->fresh()),
        ], 200);
    }
}
