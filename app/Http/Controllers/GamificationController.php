<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\GentleChallengeReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GamificationController extends Controller
{
    /**
     * Permite a um utilizador enviar um desafio de autocuidado a outro,
     * fomentando suporte comunitário em vez de competição.
     */
    public function sendGentleChallenge(Request $request, User $targetUser)
    {
        $sender = Auth::user();

        // Proteção: O utilizador precisa de ter experiência na plataforma (Nível Chama Viva ou superior)
        if ($sender->flames < 50) {
            return back()->with('error', 'Precisas de nutrir mais a tua própria Fogueira (50 chamas) antes de poderes oferecer missões a outros.');
        }

        if ($sender->id === $targetUser->id) {
            return back()->with('error', 'Não podes enviar um desafio a ti mesmo.');
        }

        $validated = $request->validate([
            'mission_type' => 'required|in:breathe,write,read'
        ]);

        $missions = [
            'breathe' => 'Tirar 3 minutos para respirar fundo na Zona Calma.',
            'write'   => 'Despejar um pensamento no Diário Emocional hoje.',
            'read'    => 'Ler uma história no Mural da Esperança e deixar um abraço.'
        ];

        $missionText = $missions[$validated['mission_type']];

        // Envia a notificação ao utilizador alvo
        $targetUser->notify(new GentleChallengeReceived($sender->pseudonym, $missionText));

        // Recompensa quem enviou o apoio
        $sender->addFlames(5);

        return back()->with('success', 'Oferta de apoio enviada com carinho. Acabaste de iluminar o dia de alguém.');
    }
}