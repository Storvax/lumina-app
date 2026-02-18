<?php

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
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    if (Auth::check()) {
        // Retorna array com dados para a sidebar "Quem está online"
        return [
            'id' => $user->id,
            'name' => $user->name,
            // Podes adicionar 'avatar' aqui se tiveres coluna na BD
        ];
    }
    return false;
});