<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Gere as subscrições Web Push do utilizador.
 *
 * O browser envia o objecto PushSubscription (endpoint + keys) que é
 * persistido na tabela push_subscriptions via HasPushSubscriptions trait.
 * Estes endpoints são consumidos pelo Alpine.js no perfil do utilizador.
 */
class PushSubscriptionController extends Controller
{
    /**
     * Armazena ou actualiza a subscrição push do browser atual.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint'    => 'required|url|max:500',
            'keys.auth'   => 'required|string',
            'keys.p256dh' => 'required|string',
        ]);

        $user = Auth::user();

        $user->updatePushSubscription(
            $request->input('endpoint'),
            $request->input('keys.p256dh'),
            $request->input('keys.auth')
        );

        return response()->json(['status' => 'subscribed']);
    }

    /**
     * Remove a subscrição push do browser actual.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|url|max:500',
        ]);

        Auth::user()->deletePushSubscription($request->input('endpoint'));

        return response()->json(['status' => 'unsubscribed']);
    }
}
