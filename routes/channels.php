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

// Canal da Sala Silenciosa (Presença)
// Permite que os utilizadores vejam quem está presente sem interação verbal.
Broadcast::channel('silent-room', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->pseudonym,
        'avatar' => $user->avatar,
    ];
});

// Canal da sessão terapêutica (co-regulação somática).
// Qualquer terapeuta com role 'therapist' estava a ter acesso a QUALQUER sessão,
// independentemente de ser o terapeuta atribuído. Corrigido: um terapeuta só
// pode subscrever sessões de pacientes que lhe foram explicitamente atribuídos.
Broadcast::channel('session.{sessionId}', function ($user, $sessionId) {
    $session = BuddySession::find($sessionId);
    if (!$session) return false;

    // Participantes diretos têm sempre acesso garantido.
    if ($user->id === $session->user_id || $user->id === $session->buddy_id) {
        return true;
    }

    // Terapeutas só acedem se estiverem atribuídos ao paciente desta sessão,
    // evitando que escutem sessões de pacientes que não são seus.
    if ($user->role === 'therapist') {
        $therapist = \App\Models\Therapist::where('name', 'like', '%' . $user->name . '%')->first();

        if (!$therapist) {
            return false;
        }

        return $session->user->therapists()->where('therapists.id', $therapist->id)->exists();
    }

    return false;
});
