<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use App\Services\AI\CBTAnalysisService;
use Illuminate\Support\Facades\Cache;

/**
 * Encapsula a lógica de negócio do chat: controlo de acesso à escrita,
 * slow mode e criação de mensagens com deteção de crise integrada.
 */
class ChatService
{
    public function __construct(
        private CBTAnalysisService $cbtService,
        private ModerationService $moderationService,
    ) {}

    /**
     * Verifica se um utilizador pode enviar uma mensagem numa sala.
     * Retorna null se permitido, ou um array ['error' => string, 'status' => int] se bloqueado.
     */
    public function checkSendPermission(User $user, Room $room): ?array
    {
        if (Cache::has("mute:room:{$room->id}:user:{$user->id}")) {
            return ['error' => 'Encontra-se silenciado temporariamente nesta sala.', 'status' => 403];
        }

        // Shadowban silencioso: o cliente recebe sucesso mas a mensagem nunca persiste nem é difundida.
        if ($user->isShadowbanned()) {
            return ['silent' => true];
        }

        // Slow mode: 15s em modo crise para reduzir escalada emocional, 3s em modo normal.
        $delay = $room->is_crisis_mode ? 15 : 3;
        $lastMessage = Message::where('user_id', $user->id)
            ->where('room_id', $room->id)
            ->latest()
            ->first();

        if ($lastMessage && $lastMessage->created_at->diffInSeconds(now()) < $delay) {
            $msg = $room->is_crisis_mode
                ? 'Modo Crise ativo. O envio está limitado a cada 15 segundos.'
                : 'Está a escrever demasiado rápido. Respire fundo.';
            return ['error' => $msg, 'status' => 429];
        }

        return null;
    }

    /**
     * Cria a mensagem, analisa crise e difunde via WebSocket.
     * Retorna a mensagem criada com relações carregadas e o resultado da deteção.
     */
    public function createAndBroadcast(User $user, Room $room, array $data): array
    {
        $crisisResult = $this->cbtService->detectCrisis($data['content']);

        $message = Message::create([
            'user_id'      => $user->id,
            'room_id'      => $room->id,
            'content'      => $data['content'],
            'is_sensitive' => ($data['is_sensitive'] ?? false) || $crisisResult['detected'],
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'reply_to_id'  => $data['reply_to_id'] ?? null,
        ]);

        // Carregar as relações que o frontend espera para evitar erros de "undefined" no appendMessage.
        $message->load(['user', 'replyTo.user', 'reactions', 'reads']);

        if ($crisisResult['detected']) {
            $this->moderationService->notifyCrisis($message, $room, $crisisResult);
        }

        broadcast(new MessageSent($message))->toOthers();

        return ['message' => $message, 'crisis_detected' => $crisisResult['detected']];
    }
}
