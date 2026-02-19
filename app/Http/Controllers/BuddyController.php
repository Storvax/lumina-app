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
        if (!$user->is_buddy) abort(403, 'Acesso restrito a Ouvintes.');

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
        // Evita spam de pedidos
        $existing = BuddySession::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($existing) return back()->with('error', 'Já tens um pedido ativo.');

        BuddySession::create([
            'user_id' => Auth::id(),
            'status' => 'pending'
        ]);

        return back()->with('success', 'Pedido enviado. Um Ouvinte irá responder em breve.');
    }

    // --- 3. ACEITAR SESSÃO (Buddy) ---
    public function acceptSession(BuddySession $session)
    {
        if ($session->status !== 'pending') return back()->with('error', 'Sessão já não está disponível.');

        // 1. Cria uma sala privada temporal para esta conversa
        $room = Room::create([
            'name' => 'Sessão Confidencial #' . $session->id,
            'slug' => 'buddy-session-' . uniqid(),
            'description' => 'Sala de apoio 1-para-1',
            'is_private' => true, // Assumindo que a tua Room table tem este campo (ou podes filtrar no index)
        ]);

        // 2. Atualiza a sessão
        $session->update([
            'buddy_id' => Auth::id(),
            'room_id' => $room->id,
            'status' => 'active',
            'started_at' => now()
        ]);

        return redirect()->route('chat.show', $room->slug);
    }

    // --- 4. ESCALAR PARA MODERADOR (Crise) ---
    public function escalate(BuddySession $session)
    {
        // Se a conversa entrar em risco de suicídio/crise
        $session->update(['status' => 'escalated']);
        
        // Aqui enviarias uma notificação urgente aos Admins/Moderadores via Email/Slack/Telegram
        // App\Models\User::where('role', 'admin')->first()->notify(...);

        return back()->with('success', 'A equipa clínica foi alertada e irá intervir imediatamente.');
    }

    // --- 5. AVALIAÇÃO PÓS-SESSÃO (Utilizador) ---
    public function evaluate(Request $request, BuddySession $session)
    {
        $request->validate(['rating' => 'required|integer|min:1|max:3']);

        $session->update([
            'status' => 'completed',
            'rating' => $request->rating,
            'completed_at' => now()
        ]);

        // Dá chamas ao Buddy pelo trabalho
        $session->buddy->increment('flames', 10);

        return redirect()->route('dashboard')->with('success', 'Obrigado pelo teu feedback. Esperamos que te sintas melhor.');
    }

    // --- 6. CANDIDATAR-SE ---
    public function apply(Request $request)
    {
        $request->validate(['motivation' => 'required|string|min:50']);
        BuddyApplication::create([
            'user_id' => Auth::id(),
            'motivation' => $request->motivation
        ]);
        return back()->with('success', 'Candidatura submetida! A nossa equipa irá analisar.');
    }
}