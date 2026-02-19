<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Post;
use App\Models\User;

class ForumInteraction extends Notification
{
    use Queueable;

    public $post;
    public $user;
    public $type; // 'reaction', 'comment'

    public function __construct(Post $post, User $user, string $type)
    {
        $this->post = $post;
        $this->user = $user;
        $this->type = $type;
    }

    public function via(object $notifiable): array
    {
        // Se o utilizador estiver em Hora de Silêncio, recebe APENAS na base de dados (visível quando abrir a app).
        // Se não estiver, poderia receber 'broadcast' (som) ou 'mail'.
        if ($notifiable->isInQuietHours()) {
            return ['database']; 
        }

        return ['database', 'broadcast']; // Adiciona 'mail' aqui se quiseres no futuro (opt-in)
    }

    public function toArray(object $notifiable): array
    {
        // Copywriting Empático em vez de genérico
        if ($this->type === 'reaction') {
            $message = "Alguém deixou um abraço virtual na tua história.";
            $icon = "ri-heart-fill";
            $color = "text-rose-500 bg-rose-100";
        } else {
            $message = "Alguém tirou um momento para te ouvir e responder.";
            $icon = "ri-chat-1-fill";
            $color = "text-indigo-500 bg-indigo-100";
        }

        return [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
        ];
    }
}