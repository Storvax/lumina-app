<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // <--- Importante para performance
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ForumInteraction extends Notification implements ShouldQueue
{
    use Queueable;

    public $post;
    public $sender;
    public $type; // 'reaction' ou 'comment'

    public function __construct($post, $sender, $type)
    {
        $this->post = $post;
        $this->sender = $sender;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast']; // Guarda na BD e envia via Reverb
    }

    // Dados para a Base de Dados
    public function toArray($notifiable)
    {
        return [
            'post_id' => $this->post->id,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'type' => $this->type,
            'message' => $this->type === 'reaction' 
                ? "{$this->sender->name} reagiu ao teu post." 
                : "{$this->sender->name} comentou a tua história."
        ];
    }

    // Dados para o Websocket (Reverb)
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => $this->type === 'reaction' 
                ? "{$this->sender->name} enviou-te apoio." 
                : "{$this->sender->name} comentou.",
            'post_id' => $this->post->id,
        ]);
    }
}