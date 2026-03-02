<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

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

        $channels = ['database', 'broadcast'];

        if ($notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /**
     * Payload enviado ao Service Worker do browser.
     */
    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Lumina — Desafio de Bem-estar')
            ->body("{$this->senderPseudonym} enviou-te um desafio: {$this->missionText}")
            ->action('Ver', 'view')
            ->tag('challenge')
            ->data(['url' => url('/dashboard')]);
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