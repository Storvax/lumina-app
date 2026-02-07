<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Mostrar a sala de chat
    public function show(Room $room)
    {
        // Alteração: Carregar apenas mensagens das últimas 24 horas
        // E no máximo 50 mensagens (para não sobrecarregar)
        $messages = Message::with(['user', 'reactions'])
            ->where('room_id', $room->id)
            ->where('created_at', '>=', now()->subHours(24)) // <--- AQUI: Só as últimas 24h
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        $allRooms = Room::all(); 

        return view('chat.show', compact('room', 'messages', 'allRooms'));
    }

    // Receber mensagem nova (AJAX)
    public function send(Request $request, Room $room)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'is_sensitive' => 'boolean' // Validação nova
        ]);

        $message = Message::create([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'content' => $request->content,
            'is_sensitive' => $request->is_sensitive ?? false, // Gravar na BD
        ]);

        // Importante: Envia o is_sensitive no evento broadcast
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message Sent!', 'message' => $message]);
    }

    // 2. Adiciona esta NOVA função para as reações
    public function react(Request $request, Room $room, Message $message)
    {
        $request->validate(['type' => 'required|in:hug,candle,ear']);

        // Lógica "Toggle": Se já existe, remove. Se não, cria.
        $existing = \App\Models\MessageReaction::where('message_id', $message->id)
            ->where('user_id', Auth::id())
            ->where('type', $request->type)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            \App\Models\MessageReaction::create([
                'message_id' => $message->id,
                'user_id' => Auth::id(),
                'type' => $request->type
            ]);
            $action = 'added';
        }

        // Contar total atualizado para este tipo
        $count = \App\Models\MessageReaction::where('message_id', $message->id)
            ->where('type', $request->type)
            ->count();

        // Dados para o Frontend
        $payload = [
            'message_id' => $message->id,
            'message_owner_id' => $message->user_id,
            'type' => $request->type,
            'count' => $count,
            'action' => $action
        ];

        broadcast(new \App\Events\MessageReacted($room->id, $payload))->toOthers();

        return response()->json($payload);
    }
}