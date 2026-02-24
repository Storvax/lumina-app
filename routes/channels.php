<?php

use App\Models\Room;
use App\Models\BuddySession;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal da Sala de Chat (Presença)
// Valida que o utilizador tem acesso à sala antes de permitir a subscrição.
// Para salas privadas (buddy sessions), apenas os participantes podem aceder.
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    $room = Room::find($roomId);

    if (!$room) {
        return false;
    }

    // Para salas privadas, verificar que o utilizador é participante da sessão associada.
    if ($room->is_private) {
        $isParticipant = BuddySession::where('room_id', $room->id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('buddy_id', $user->id);
            })
            ->exists();

        // Moderadores e admins podem aceder para supervisão em caso de escalação.
        if (!$isParticipant && !$user->isModerator()) {
            return false;
        }
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
