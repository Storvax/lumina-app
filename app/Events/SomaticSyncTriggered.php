<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento de co-regulação somática em tempo real.
 * Transmitido no canal privado da sessão terapêutica para sincronizar
 * exercícios de respiração/grounding entre terapeuta e paciente.
 */
class SomaticSyncTriggered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public string $exercise,
        public int $bpm,
        public int $triggeredBy,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("session.{$this->sessionId}"),
        ];
    }

    /**
     * Dados enviados ao frontend via WebSocket.
     */
    public function broadcastWith(): array
    {
        return [
            'exercise' => $this->exercise,
            'bpm' => $this->bpm,
            'triggered_by' => $this->triggeredBy,
        ];
    }
}
