<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessageReacted;
use App\Events\MessageDeleted;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    // --- MOSTRAR A SALA ---
    public function show(Room $room)
    {
        // Carregar apenas mensagens das últimas 24 horas e max 50
        $messages = Message::with(['user', 'reactions'])
            ->where('room_id', $room->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        $allRooms = Room::all(); 

        return view('chat.show', compact('room', 'messages', 'allRooms'));
    }

    // --- ENVIAR MENSAGEM (COM SLOW MODE) ---
    public function send(Request $request, Room $room)
    {
        // 1. SLOW MODE: Verificar se o user escreveu há menos de 3 segundos
        $lastMessage = Message::where('user_id', Auth::id())
            ->where('room_id', $room->id)
            ->latest()
            ->first();

        if ($lastMessage && $lastMessage->created_at->diffInSeconds(now()) < 3) {
            return response()->json(['error' => 'Estás a escrever muito rápido. Respira fundo.'], 429);
        }

        // 2. Validação
        $request->validate([
            'content' => 'required|string|max:1000',
            'is_sensitive' => 'boolean'
        ]);

        // 3. Criar Mensagem
        $message = Message::create([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'content' => $request->content,
            'is_sensitive' => $request->is_sensitive ?? false,
            'is_anonymous' => $request->is_anonymous ?? false, // Suporte para anónimo se tiveres o campo
        ]);

        // 4. Broadcast para os outros (Reverb)
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message Sent!', 'message' => $message]);
    }

    // --- REAGIR A MENSAGEM ---
    public function react(Request $request, Room $room, Message $message)
    {
        $request->validate(['type' => 'required|in:hug,candle,ear']);

        // Toggle: Se existe remove, senão cria
        $existing = MessageReaction::where('message_id', $message->id)
            ->where('user_id', Auth::id())
            ->where('type', $request->type)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            MessageReaction::create([
                'message_id' => $message->id,
                'user_id' => Auth::id(),
                'type' => $request->type
            ]);
            $action = 'added';
        }

        // Contar total atualizado
        $count = MessageReaction::where('message_id', $message->id)
            ->where('type', $request->type)
            ->count();

        // Payload para frontend e broadcast
        $payload = [
            'message_id' => $message->id,
            'message_owner_id' => $message->user_id,
            'type' => $request->type,
            'count' => $count,
            'action' => $action
        ];

        broadcast(new MessageReacted($room->id, $payload))->toOthers();

        return response()->json($payload);
    }

    // --- APAGAR MENSAGEM ---
    public function destroyMessage(Message $message)
    {
        // Permite apagar se for MODERADOR ou se for o DONO da mensagem
        if (!auth()->user()->isModerator() && auth()->id() !== $message->user_id) {
            abort(403, 'Não tens permissão.');
        }

        $roomId = $message->room_id; // Guardar ID antes de apagar para o evento
        $messageId = $message->id;

        $message->delete();
        
        // Avisar os outros em tempo real para removerem do ecrã
        broadcast(new MessageDeleted($roomId, $messageId))->toOthers();
        
        return back()->with('status', 'Mensagem apagada.');
    }

    // --- NOVO: REPORTAR MENSAGEM ---
    public function reportMessage(Request $request, Message $message)
    {
        $request->validate(['reason' => 'required|string|max:50']);

        // Verifica se já reportou esta mensagem para evitar spam
        $exists = DB::table('message_reports')
            ->where('message_id', $message->id)
            ->where('reporter_id', Auth::id())
            ->exists();

        if (!$exists) {
            DB::table('message_reports')->insert([
                'message_id' => $message->id,
                'reporter_id' => Auth::id(),
                'reason' => $request->reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Opcional: Aqui poderias disparar uma notificação para os admins
        }

        return response()->json(['message' => 'Denúncia recebida. Obrigado por manteres a comunidade segura.']);
    }
}