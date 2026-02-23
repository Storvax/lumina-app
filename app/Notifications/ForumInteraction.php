<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Post;
use App\Models\User;

/**
 * Lida com as notificações do fórum de forma empática e silenciosa.
 */
class ForumInteraction extends Notification
{
    use Queueable;

    public Post $post;
    public User $user;
    public string $type;
    public ?string $customMessage;

    /**
     * @param Post $post
     * @param User $user
     * @param string $type O tipo de interação (ex: 'reaction', 'comment', 'milestone')
     * @param string|null $customMessage Mensagem opcional para sobrepor o texto padrão
     */
    public function __construct(Post $post, User $user, string $type, ?string $customMessage = null)
    {
        $this->post = $post;
        $this->user = $user;
        $this->type = $type;
        $this->customMessage = $customMessage;
    }

    /**
     * Define os canais de entrega da notificação.
     * Respeita o período de silêncio configurado pelo utilizador.
     */
    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'isInQuietHours') && $notifiable->isInQuietHours()) {
            return ['database']; 
        }

        return ['database', 'broadcast'];
    }

    /**
     * Prepara os dados da notificação para armazenamento.
     */
    public function toArray(object $notifiable): array
    {
        $icon = 'ri-notification-line';
        $color = 'text-slate-500 bg-slate-100';

        if ($this->type === 'reaction') {
            $icon = 'ri-heart-fill';
            $color = 'text-rose-500 bg-rose-100';
            $defaultMessage = 'Alguém deixou um abraço virtual na tua história.';
        } elseif ($this->type === 'comment') {
            $icon = 'ri-chat-1-fill';
            $color = 'text-indigo-500 bg-indigo-100';
            $defaultMessage = 'Alguém tirou um momento para te ouvir e responder.';
        } else {
            $defaultMessage = 'Alguém interagiu com o teu espaço.';
        }

        return [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'message' => $this->customMessage ?? $defaultMessage,
            'icon' => $icon,
            'color' => $color,
        ];
    }
}