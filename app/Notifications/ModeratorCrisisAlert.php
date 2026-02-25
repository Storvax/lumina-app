<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Alerta enviado a moderadores e administradores quando o motor de deteção de crise
 * identifica conteúdo de risco numa mensagem de chat.
 *
 * Prioridade máxima: não respeita horas de silêncio (é um alerta de segurança).
 */
class ModeratorCrisisAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param Message $message  A mensagem que desencadeou o alerta.
     * @param Room    $room     A sala onde a mensagem foi publicada.
     * @param array   $crisis   Resultado do motor de deteção (['level', 'type']).
     */
    public function __construct(
        public readonly Message $message,
        public readonly Room $room,
        public readonly array $crisis,
    ) {}

    /**
     * Entrega sempre via base de dados e broadcast.
     * Não respeita horas de silêncio — a segurança tem prioridade absoluta.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Dados guardados na base de dados para visualização no painel de notificações.
     */
    public function toArray(object $notifiable): array
    {
        // Extrai um excerto da mensagem sem expor o conteúdo completo no payload
        $excerpt = mb_substr($this->message->content, 0, 80);
        if (mb_strlen($this->message->content) > 80) {
            $excerpt .= '…';
        }

        $levelLabel = match ($this->crisis['level']) {
            'critical' => 'Crítico',
            'high'     => 'Elevado',
            default    => 'Alerta',
        };

        return [
            'type'       => 'crisis_alert',
            'message'    => "Alerta de crise [{$levelLabel}] na sala «{$this->room->name}». Requer revisão imediata.",
            'excerpt'    => $excerpt,
            'room_id'    => $this->room->id,
            'room_slug'  => $this->room->slug,
            'message_id' => $this->message->id,
            'level'      => $this->crisis['level'],
            'icon'       => 'ri-alarm-warning-fill',
            'color'      => 'text-rose-500 bg-rose-100',
        ];
    }
}
