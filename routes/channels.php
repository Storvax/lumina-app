<?php

use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal de Chat da Sala (Presence Channel)
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    if ($user) {
        // Retorna os dados que os outros vão ver (apenas nome e ID para segurança)
        return ['id' => $user->id, 'name' => $user->name];
    }
});