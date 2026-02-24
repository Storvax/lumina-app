<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessageReacted;
use App\Events\MessageDeleted;
use App\Events\MessageUpdated;
use App\Events\MessageRead;
use App\Events\RoomStatusUpdated;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Apresenta a sala de chat, histórico e o Painel de Moderação.
     */
    public function show(Room $room)
    {
        // 1. Registo de Visita (Boas-vindas e Presença)
        $visit = DB::table('room_visits')
            ->where('room_id', $room->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$visit) {
            DB::table('room_visits')->insert([
                'room_id' => $room->id,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            session()->flash('first_visit', true);
        } else {
            // BUG CORRIGIDO: Se já visitou antes, atualiza a timestamp para marcar que está online AGORA
            DB::table('room_visits')
                ->where('room_id', $room->id)
                ->where('user_id', Auth::id())
                ->update(['updated_at' => now()]);
        }

        // 2. Lista de Seguidores (Alertas de Presença)
        $followingIds = DB::table('chat_presence_subscriptions')
            ->where('user_id', Auth::id())
            ->where('room_id', $room->id)
            ->pluck('target_user_id')
            ->toArray();

        // 3. Carregar Mensagens (Com relações necessárias para UI)
        $messages = Message::with(['user', 'reactions', 'replyTo.user', 'reads'])
            ->where('room_id', $room->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        // 4. Dados para Painel de Moderação
        $modStats = [];
        $modLogs = [];
        
        if (Auth::user()->isModerator()) {
            $modStats = [
                'messages_24h' => Message::where('room_id', $room->id)->where('created_at', '>=', now()->subHours(24))->count(),
                'pending_reports' => DB::table('message_reports')
                    ->join('messages', 'message_reports.message_id', '=', 'messages.id')
                    ->where('messages.room_id', $room->id)
                    ->count(),
            ];

            $modLogs = DB::table('moderation_logs')
                ->join('users', 'moderation_logs.user_id', '=', 'users.id')
                ->leftJoin('users as targets', 'moderation_logs.target_user_id', '=', 'targets.id')
                ->where('room_id', $room->id)
                ->select('moderation_logs.*', 'users.name as moderator_name', 'targets.name as target_name')
                ->latest()
                ->take(10)
                ->get();
        }

        $allRooms = Room::all(); 

        return view('chat.show', compact('room', 'messages', 'allRooms', 'followingIds', 'modStats', 'modLogs'));
    }

    /**
     * Processa o envio de mensagens (com Slow Mode e Deteção de Crise).
     */
    public function send(Request $request, Room $room)
    {
        $user = Auth::user();

        // 1. Validação de Mute
        if (Cache::has("mute:room:{$room->id}:user:{$user->id}")) {
            return response()->json(['error' => 'Encontra-se silenciado temporariamente nesta sala.'], 403);
        }

        // 2. Slow Mode
        $delay = $room->is_crisis_mode ? 15 : 3;
        $lastMessage = Message::where('user_id', $user->id)
            ->where('room_id', $room->id)
            ->latest()
            ->first();
        
        if ($lastMessage && $lastMessage->created_at->diffInSeconds(now()) < $delay) {
            $msg = $room->is_crisis_mode 
                ? 'Modo Crise ativo. O envio está limitado a cada 15 segundos.' 
                : 'Está a escrever demasiado rápido. Respire fundo.';
            return response()->json(['error' => $msg], 429);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
            'is_sensitive' => 'boolean',
            'is_anonymous' => 'boolean',
            'reply_to_id' => 'nullable|exists:messages,id'
        ]);

        // 3. Deteção de Crise
        $hasCrisisKeywords = Str::contains(Str::lower($request->input('content')), ['suicidio', 'morrer', 'acabar com tudo', 'matar-me']);

        // 4. Criação
        $message = Message::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'content' => $request->input('content'),
            'is_sensitive' => $request->boolean('is_sensitive'),
            'is_anonymous' => $request->boolean('is_anonymous'),
            'reply_to_id' => $request->input('reply_to_id'),
        ]);

        // IMPORTANTE: Carregar TODAS as relações que o frontend espera (user, replyTo, reactions, reads)
        // Isto previne erros de JavaScript "undefined" na função appendMessage
        $message->load(['user', 'replyTo.user', 'reactions', 'reads']);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => 'Message Sent!',
            'message' => $message,
            'crisis_detected' => $hasCrisisKeywords
        ]);
    }

    /**
     * Atualiza uma mensagem existente (Edição).
     */
    public function updateMessage(Request $request, Room $room, Message $message)
    {
        if (Auth::id() !== $message->user_id) abort(403);

        if ($message->created_at->diffInMinutes(now()) > 5) {
            return response()->json(['error' => 'O tempo limite para edição expirou.'], 422);
        }

        $request->validate(['content' => 'required|string|max:1000']);

        $message->update([
            'content' => $request->input('content'),
            'edited_at' => now()
        ]);

        broadcast(new MessageUpdated($message))->toOthers();

        return response()->json(['status' => 'Atualizada', 'message' => $message]);
    }

    /**
     * Marca mensagens como lidas.
     */
    public function markAsRead(Request $request, Room $room)
    {
        $user = Auth::user();
        if (!$user->read_receipts_enabled) return response()->json(['status' => 'disabled']);

        $ids = Message::where('room_id', $room->id)
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn($q) => $q->where('user_id', $user->id))
            ->pluck('id');

        if ($ids->isEmpty()) return response()->json(['status' => 'uptodate']);

        DB::table('message_reads')->insert(
            $ids->map(fn($id) => [
                'message_id' => $id, 
                'user_id' => $user->id, 
                'read_at' => now()
            ])->toArray()
        );
        
        broadcast(new MessageRead($room->id, $ids->toArray(), $user->id))->toOthers();

        return response()->json(['status' => 'marked']);
    }

    // --- FERRAMENTAS DE MODERAÇÃO ---

    public function toggleCrisisMode(Request $request, Room $room)
    {
        if (!Auth::user()->isModerator()) abort(403);

        $newState = !$room->is_crisis_mode;
        $room->update(['is_crisis_mode' => $newState]);

        $this->logAction($room->id, $newState ? 'crisis_on' : 'crisis_off', null, 'Alterou estado de crise.');
        broadcast(new RoomStatusUpdated($room));

        return response()->json(['status' => $newState ? 'active' : 'inactive']);
    }

    public function muteUser(Request $request, Room $room, User $targetUser)
    {
        if (!Auth::user()->isModerator()) abort(403);
        
        Cache::put("mute:room:{$room->id}:user:{$targetUser->id}", true, now()->addMinutes(10));
        $this->logAction($room->id, 'mute', $targetUser->id, 'Silenciado por 10m.');
        
        return response()->json(['message' => "Utilizador silenciado."]);
    }

    public function destroyMessage(Message $message)
    {
        if (!Auth::user()->isModerator() && Auth::id() !== $message->user_id) abort(403);
        
        if (Auth::user()->isModerator() && Auth::id() !== $message->user_id) {
            $this->logAction($message->room_id, 'delete_msg', $message->user_id, 'Apagou msg.');
        }

        $roomId = $message->room_id;
        $messageId = $message->id;
        $message->delete();
        
        broadcast(new MessageDeleted($roomId, $messageId))->toOthers();
        
        return request()->expectsJson() ? response()->json(['status' => 'Deleted']) : back();
    }

    public function pinMessage(Request $request, Room $room)
    {
        if (!Auth::user()->isModerator()) abort(403);
        
        $request->validate(['message' => 'nullable|string|max:500']);
        $room->update(['pinned_message' => $request->input('message')]);
        $this->logAction($room->id, 'pin', null, 'Atualizou mensagem fixada.');
        
        return back();
    }

    public function reportMessage(Request $request, Message $message)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        
        // Verifica duplicados antes de inserir
        if (!DB::table('message_reports')->where('message_id', $message->id)->where('reporter_id', Auth::id())->exists()) {
            DB::table('message_reports')->insert([
                'message_id' => $message->id, 
                'reporter_id' => Auth::id(), 
                'reason' => $request->reason, 
                'created_at' => now(), 
                'updated_at' => now()
            ]);
        }
        return response()->json(['message' => 'Reportado.']);
    }

    // --- COMUNIDADE ---

    public function react(Request $request, Room $room, Message $message)
    {
        $request->validate(['type' => 'required|in:hug,candle,ear']);
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

        $count = MessageReaction::where('message_id', $message->id)->where('type', $request->type)->count();
        
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

    public function togglePresenceAlert(Request $request, Room $room, User $targetUser)
    {
        $userId = Auth::id();
        $exists = DB::table('chat_presence_subscriptions')
            ->where('user_id', $userId)
            ->where('target_user_id', $targetUser->id)
            ->where('room_id', $room->id)
            ->exists();
        
        if ($exists) {
            DB::table('chat_presence_subscriptions')
                ->where('user_id', $userId)
                ->where('target_user_id', $targetUser->id)
                ->where('room_id', $room->id)
                ->delete();
            $status = 'removed';
        } else {
            DB::table('chat_presence_subscriptions')->insert([
                'user_id' => $userId, 
                'target_user_id' => $targetUser->id, 
                'room_id' => $room->id, 
                'created_at' => now(), 
                'updated_at' => now()
            ]);
            $status = 'added';
        }
        return response()->json(['status' => $status]);
    }

    /**
     * Helper privado para registar ações no log.
     */
    private function logAction($roomId, $action, $targetId = null, $details = null)
    {
        DB::table('moderation_logs')->insert([
            'user_id' => Auth::id(),
            'room_id' => $roomId,
            'action' => $action,
            'target_user_id' => $targetId,
            'details' => $details,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // --- PREFERÊNCIAS DE UI ---

    /**
     * Alterna entre modo Compacto e Confortável.
     */
    public function toggleViewMode(Request $request)
    {
        $user = Auth::user();
        $newMode = $user->chat_view_mode === 'comfortable' ? 'compact' : 'comfortable';
        
        $user->update(['chat_view_mode' => $newMode]);

        return response()->json(['mode' => $newMode]);
    }
}