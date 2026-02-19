<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WeeklyEmotionalSummary extends Notification
{
    use Queueable;

    public $stats;

    public function __construct(array $stats)
    {
        $this->stats = $stats;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Guardamos na BD para ele ler com calma
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'weekly_summary',
            'message' => "O teu resumo semanal chegou. Cuidaste de ti {$this->stats['logs_count']} vezes esta semana.",
            'icon' => "ri-calendar-heart-fill",
            'color' => "text-amber-500 bg-amber-100",
            'details' => $this->stats // Podemos ler isto no Frontend depois
        ];
    }
}