<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Notificação calorosa enviada a utilizadores que mostram sinais
 * de desligamento progressivo. Nunca culpabiliza — apenas acolhe.
 */
class GentleReEngagement extends Notification implements ShouldQueue
{
    use Queueable;

    public int $daysSinceLastActivity;

    public function __construct(int $daysSinceLastActivity)
    {
        $this->daysSinceLastActivity = $daysSinceLastActivity;
    }

    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'isInQuietHours') && $notifiable->isInQuietHours()) {
            return ['database'];
        }

        $channels = ['database'];

        if ($notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Lumina')
            ->body('Sentimos a tua falta. Quando quiseres, estamos aqui.')
            ->action('Visitar', 'visit')
            ->tag('re-engagement')
            ->data(['url' => url('/dashboard')]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'    => 'ri-heart-pulse-line',
            'color'   => 'text-violet-500 bg-violet-50',
            'message' => 'Sentimos a tua falta. Quando quiseres, estamos aqui. Sem pressa.',
        ];
    }
}
