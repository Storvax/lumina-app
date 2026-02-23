<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificação de email formatada como uma reflexão suave.
 */
class WeeklyEmotionalSummary extends Notification implements ShouldQueue
{
    use Queueable;

    public array $stats;

    public function __construct(array $stats)
    {
        $this->stats = $stats;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $firstName = explode(' ', trim($notifiable->name))[0];

        return (new MailMessage)
            ->subject("Um momento para ti, {$firstName} 🌻")
            ->greeting("Olá, {$firstName}.")
            ->line('Mais uma semana passou. O mundo lá fora continua acelerado, mas queríamos tirar um momento para celebrar os teus passos no teu próprio ritmo.')
            ->line("Esta semana:")
            ->line("📖 Tiraste tempo para escrever e cuidar de ti {$this->stats['logs_count']} vezes.")
            ->line("🫂 A comunidade enviou-te {$this->stats['hugs_received']} abraços de apoio.")
            ->line("🔥 A tua chama interior continua viva.")
            ->action('Visitar o meu Refúgio', url('/dashboard'))
            ->line('Pequenos passos são vitórias gigantes. Estamos aqui por ti na próxima semana.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'ri-sun-line',
            'color' => 'text-amber-500 bg-amber-50',
            'message' => 'O teu resumo de bem-estar desta semana está pronto. Obrigado por estares aqui.',
        ];
    }
}