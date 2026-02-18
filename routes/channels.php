<?php

use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal de Chat da Sala (Presence Channel)
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    // Retorna os dados do user para a lista "Quem está aqui"
    return ['id' => $user->id, 'name' => $user->name, 'avatar' => substr($user->name, 0, 1)];
});