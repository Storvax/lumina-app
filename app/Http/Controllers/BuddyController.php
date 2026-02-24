<?php

namespace App\Http\Controllers;

use App\Models\BuddyApplication;
use App\Models\BuddySession;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuddyController extends Controller
{
    // --- 1. PAINEL DO BUDDY ---
    public function dashboard()
    {
        $user = Auth::user();

        // Apenas utilizadores com estatuto de Ouvinte aprovado podem aceder ao painel.
        if (!$user->is_buddy) {
            abort(403, 'Acesso restrito a Ouvintes.');
        }

        $activeSessions = BuddySession::where('buddy_id', $user->id)
            ->where('status', 'active')
            ->with('user')
            ->get();

        $pendingRequests = BuddySession::where('status', 'pending')->get();

        $stats = [
            'total_helped' => BuddySession::where('buddy_id', $user->id)->where('status', 'completed')->count(),
            'avg_rating' => BuddySession::where('buddy_id', $user->id)->avg('rating') ?? 3,
        ];

        return view('buddy.dashboard', compact('activeSessions', 'pendingRequests', 'stats'));
    }

    // --- 2. PEDIR AJUDA (Utilizador normal) ---
    public function requestBuddy()
    {
        // Evita pedidos duplicados: só é permitido um pedido pendente ou ativo por utilizador.
        $existing = BuddySession::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($existing) {
            return back()->with('error', 'Já tens um pedido ativo.');
        }

        BuddySession::create([
            'user_id' => Auth::id(),
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Pedido enviado. Um Ouvinte irá responder em breve.');
    }

    // --- 3. ACEITAR SESSÃO (Buddy) ---
    public function acceptSession(BuddySession $session)
    {
        $user = Auth::user();

        // Apenas Ouvintes aprovados podem aceitar sessões.
        if (!$user->is_buddy) {
            abort(403, 'Apenas Ouvintes aprovados podem aceitar sessões.');
        }

        // Um Ouvinte não pode aceitar o seu próprio pedido de ajuda.
        if ($session->user_id === $user->id) {
            abort(403, 'Não podes aceitar o teu próprio pedido.');
        }

        if ($session->status !== 'pending') {
            return back()->with('error', 'Sessão já não está disponível.');
        }

        // Cria uma sala privada temporal para esta conversa de apoio.
        $room = Room::create([
            'name' => 'Sessão Confidencial #' . $session->id,
            'slug' => 'buddy-session-' . uniqid(),
            'description' => 'Sala de apoio 1-para-1',
            'is_private' => true,
        ]);

        $session->update([
            'buddy_id' => $user->id,
            'room_id' => $room->id,
            'status' => 'active',
            'started_at' => now()
        ]);

        return redirect()->route('chat.show', $room->slug);
    }

    // --- 4. ESCALAR PARA MODERADOR (Crise) ---
    public function escalate(BuddySession $session)
    {
        $user = Auth::user();

        // Apenas o Ouvinte atribuído à sessão pode escalá-la.
        if ($session->buddy_id !== $user->id) {
            abort(403, 'Não tens permissão para escalar esta sessão.');
        }

        // A sessão tem de estar ativa para poder ser escalada.
        if ($session->status !== 'active') {
            return back()->with('error', 'Apenas sessões ativas podem ser escaladas.');
        }

        $session->update(['status' => 'escalated']);

        // TODO: Enviar notificação urgente aos Admins/Moderadores (Email, Slack ou Telegram).

        return back()->with('success', 'A equipa clínica foi alertada e irá intervir imediatamente.');
    }

    // --- 5. AVALIAÇÃO PÓS-SESSÃO (Utilizador) ---
    public function evaluate(Request $request, BuddySession $session)
    {
        $user = Auth::user();

        // Apenas o utilizador que pediu ajuda pode avaliar a sessão.
        if ($session->user_id !== $user->id) {
            abort(403, 'Não tens permissão para avaliar esta sessão.');
        }

        // A sessão tem de estar ativa ou escalada para ser avaliada — evita avaliações duplicadas.
        if (!in_array($session->status, ['active', 'escalated'])) {
            return back()->with('error', 'Esta sessão já foi avaliada ou não está disponível para avaliação.');
        }

        $request->validate(['rating' => 'required|integer|min:1|max:3']);

        $session->update([
            'status' => 'completed',
            'rating' => $request->rating,
            'completed_at' => now()
        ]);

        // Recompensa o Ouvinte com chamas pelo trabalho prestado.
        if ($session->buddy) {
            $session->buddy->increment('flames', 10);
        }

        return redirect()->route('dashboard')->with('success', 'Obrigado pelo teu feedback. Esperamos que te sintas melhor.');
    }

    // --- 6. CANDIDATAR-SE A OUVINTE ---
    public function apply(Request $request)
    {
        $user = Auth::user();

        // Impede candidaturas duplicadas de utilizadores que já são Ouvintes ou já têm candidatura pendente.
        if ($user->is_buddy) {
            return back()->with('error', 'Já és um Ouvinte aprovado.');
        }

        $existingApplication = BuddyApplication::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingApplication) {
            return back()->with('error', 'Já tens uma candidatura pendente.');
        }

        $request->validate(['motivation' => 'required|string|min:50']);

        BuddyApplication::create([
            'user_id' => $user->id,
            'motivation' => $request->motivation
        ]);

        return back()->with('success', 'Candidatura submetida! A nossa equipa irá analisar.');
    }
}
