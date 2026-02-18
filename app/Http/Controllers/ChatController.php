<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessageReacted;
use App\Events\MessageDeleted;
use App\Events\MessageUpdated;
use App\Events\MessageRead;
use App\Events\RoomStatusUpdated; // <--- NOVO
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
        // 1. Boas-vindas
        $isFirstVisit = !DB::table('room_visits')->where('room_id', $room->id)->where('user_id', Auth::id())->exists();
        if ($isFirstVisit) {
            DB::table('room_visits')->insert(['room_id' => $room->id, 'user_id' => Auth::id(), 'created_at' => now(), 'updated_at' => now()]);
            session()->flash('first_visit', true);
        }

        // 2. Dados de Comunidade (Follow)
        $followingIds = DB::table('chat_presence_subscriptions')
            ->where('user_id', Auth::id())->where('room_id', $room->id)->pluck('target_user_id')->toArray();

        // 3. Mensagens
        $messages = Message::with(['user', 'reactions', 'replyTo.user', 'reads'])
            ->where('room_id', $room->id)->where('created_at', '>=', now()->subHours(24))
            ->latest()->take(50)->get()->reverse()->values();

        // 4. DADOS PARA O PAINEL DE MODERAÇÃO (Apenas Mods)
        $modStats = [];
        $modLogs = [];
        
        if (Auth::user()->isModerator()) {
            $modStats = [
                'messages_24h' => Message::where('room_id', $room->id)->where('created_at', '>=', now()->subHours(24))->count(),
                'pending_reports' => DB::table('message_reports')
                    ->join('messages', 'message_reports.message_id', '=', 'messages.id')
                    ->where('messages.room_id', $room->id)
                    // Assumindo que não tens coluna 'resolved', contamos todos por agora
                    ->count(),
            ];

            // Carregar últimos 10 logs desta sala
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
     * Processa envio com lógica reforçada para Modo Crise.
     */
    public function send(Request $request, Room $room)
    {
        $user = Auth::user();

        // 1. Mute Check
        if (Cache::has("mute:room:{$room->id}:user:{$user->id}")) {
            return response()->json(['error' => 'Encontra-se silenciado temporariamente.'], 403);
        }

        // 2. SLOW MODE ADAPTATIVO
        // Se estiver em MODO CRISE, o delay sobe para 15 segundos. Normal é 3s.
        $delay = $room->is_crisis_mode ? 15 : 3;
        
        $lastMessage = Message::where('user_id', $user->id)->where('room_id', $room->id)->latest()->first();
        
        if ($lastMessage && $lastMessage->created_at->diffInSeconds(now()) < $delay) {
            $msg = $room->is_crisis_mode 
                ? 'A sala está em Modo Crise. O envio está limitado a cada 15 segundos.' 
                : 'Está a escrever demasiado rápido.';
            return response()->json(['error' => $msg], 429);
        }

        $request->validate(['content' => 'required|string|max:1000', 'is_sensitive' => 'boolean', 'is_anonymous' => 'boolean', 'reply_to_id' => 'nullable|exists:messages,id']);

        // 3. Deteção de Crise
        $hasCrisisKeywords = Str::contains(Str::lower($request->input('content')), ['suicidio', 'morrer', 'acabar com tudo']);

        // 4. Criação
        $message = Message::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'content' => $request->input('content'),
            'is_sensitive' => $request->boolean('is_sensitive'),
            'is_anonymous' => $request->boolean('is_anonymous'),
            'reply_to_id' => $request->input('reply_to_id'),
        ]);

        $message->load('replyTo.user');
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message Sent!', 'message' => $message, 'crisis_detected' => $hasCrisisKeywords]);
    }

    // --- FERRAMENTAS DE MODERAÇÃO ---

    /**
     * Ativa/Desativa o Modo Crise.
     */
    public function toggleCrisisMode(Request $request, Room $room)
    {
        if (!Auth::user()->isModerator()) abort(403);

        $newState = !$room->is_crisis_mode;
        $room->update(['is_crisis_mode' => $newState]);

        // Registo no Log
        $this->logAction($room->id, $newState ? 'crisis_on' : 'crisis_off', null, 'Alterou o estado de segurança da sala.');

        // Avisar toda a gente
        broadcast(new RoomStatusUpdated($room));

        return response()->json(['status' => $newState ? 'active' : 'inactive', 'message' => 'Modo Crise atualizado.']);
    }

    public function muteUser(Request $request, Room $room, User $targetUser)
    {
        if (!Auth::user()->isModerator()) abort(403);
        Cache::put("mute:room:{$room->id}:user:{$targetUser->id}", true, now()->addMinutes(10));
        
        $this->logAction($room->id, 'mute', $targetUser->id, 'Silenciado por 10 minutos.'); // Log
        
        return response()->json(['message' => "Utilizador silenciado."]);
    }

    public function destroyMessage(Message $message)
    {
        if (!Auth::user()->isModerator() && Auth::id() !== $message->user_id) abort(403);
        
        if (Auth::user()->isModerator() && Auth::id() !== $message->user_id) {
            $this->logAction($message->room_id, 'delete_msg', $message->user_id, 'Apagou mensagem: ' . Str::limit($message->content, 20)); // Log
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
        $room->update(['pinned_message' => $request->input('message')]);
        
        $this->logAction($room->id, 'pin', null, 'Fixou nova mensagem.'); // Log
        
        return back()->with('status', 'Mensagem fixada.');
    }

    /**
     * Helper privado para registar ações na tabela de logs.
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

    // ... (Mantém métodos: updateMessage, markAsRead, react, reportMessage, togglePresenceAlert igual ao anterior) ...
    // Vou omiti-los aqui para poupar espaço, mas deves manter os do último update.
    
    // (Apenas para garantir que o código fica completo para ti, aqui vai o resto compactado)
    public function updateMessage(Request $request, Room $room, Message $message) {
        if (Auth::id() !== $message->user_id) abort(403);
        if ($message->created_at->diffInMinutes(now()) > 5) return response()->json(['error' => 'Tempo expirou.'], 422);
        $message->update(['content' => $request->input('content'), 'edited_at' => now()]);
        broadcast(new MessageUpdated($message))->toOthers();
        return response()->json(['status' => 'ok']);
    }
    public function markAsRead(Request $request, Room $room) {
        if (!Auth::user()->read_receipts_enabled) return response()->json(['status' => 'disabled']);
        $ids = Message::where('room_id', $room->id)->where('user_id', '!=', Auth::id())
            ->whereDoesntHave('reads', fn($q)=>$q->where('user_id', Auth::id()))->pluck('id');
        if($ids->isEmpty()) return response()->json(['status'=>'uptodate']);
        DB::table('message_reads')->insert($ids->map(fn($id)=>['message_id'=>$id,'user_id'=>Auth::id(),'read_at'=>now()])->toArray());
        broadcast(new MessageRead($room->id, $ids->toArray(), Auth::id()))->toOthers();
        return response()->json(['status'=>'marked']);
    }
    public function react(Request $request, Room $room, Message $message) {
        $existing = MessageReaction::where('message_id',$message->id)->where('user_id',Auth::id())->where('type',$request->type)->first();
        if($existing){ $existing->delete(); $a='removed'; } else { MessageReaction::create(['message_id'=>$message->id,'user_id'=>Auth::id(),'type'=>$request->type]); $a='added'; }
        $c = MessageReaction::where('message_id',$message->id)->where('type',$request->type)->count();
        $p = ['message_id'=>$message->id, 'message_owner_id'=>$message->user_id, 'type'=>$request->type, 'count'=>$c, 'action'=>$a];
        broadcast(new MessageReacted($room->id, $p))->toOthers(); return response()->json($p);
    }
    public function reportMessage(Request $request, Message $message) {
        if (!DB::table('message_reports')->where('message_id',$message->id)->where('reporter_id',Auth::id())->exists())
            DB::table('message_reports')->insert(['message_id'=>$message->id, 'reporter_id'=>Auth::id(), 'reason'=>$request->reason, 'created_at'=>now(), 'updated_at'=>now()]);
        return response()->json(['message'=>'Reportado']);
    }
    public function togglePresenceAlert(Request $request, Room $room, User $targetUser) {
        $uid=Auth::id(); $exists=DB::table('chat_presence_subscriptions')->where('user_id',$uid)->where('target_user_id',$targetUser->id)->where('room_id',$room->id)->exists();
        if($exists) { DB::table('chat_presence_subscriptions')->where('user_id',$uid)->where('target_user_id',$targetUser->id)->where('room_id',$room->id)->delete(); $s='removed'; }
        else { DB::table('chat_presence_subscriptions')->insert(['user_id'=>$uid,'target_user_id'=>$targetUser->id,'room_id'=>$room->id,'created_at'=>now(),'updated_at'=>now()]); $s='added'; }
        return response()->json(['status'=>$s]);
    }
}