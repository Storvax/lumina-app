<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notificação recebida quando outro membro da comunidade envia uma "Oferta de Apoio".
 */
class GentleChallengeReceived extends Notification
{
    use Queueable;

    public string $senderPseudonym;
    public string $missionText;

    public function __construct(string $senderPseudonym, string $missionText)
    {
        $this->senderPseudonym = $senderPseudonym;
        $this->missionText = $missionText;
    }

    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'isInQuietHours') && $notifiable->isInQuietHours()) {
            return ['database']; 
        }
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'ri-gift-line',
            'color' => 'text-emerald-600 bg-emerald-100',
            'message' => "{$this->senderPseudonym} enviou-te um desafio de bem-estar: {$this->missionText}",
            'action_url' => '/dashboard', 
        ];
    }
}