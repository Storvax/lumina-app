<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Notifications\ModeratorCrisisAlert;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Centraliza ações de moderação reutilizáveis entre Chat e Fórum.
 * Isola os efeitos secundários de auditoria e notificação dos controllers.
 */
class ModerationService
{
    /**
     * Persiste uma ação de moderação no log de auditoria.
     * Permite que conformidade e operações de segurança rastreiem todas as ações.
     */
    public function logAction(int $roomId, string $action, ?int $targetUserId = null, ?string $details = null): void
    {
        DB::table('moderation_logs')->insert([
            'user_id'        => auth()->id(),
            'room_id'        => $roomId,
            'action'         => $action,
            'target_user_id' => $targetUserId,
            'details'        => $details,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    /**
     * Devolve a lista de moderadores ativos com cache de 5 minutos.
     * Evita uma query por cada mensagem de crise detetada.
     */
    public function getModerators()
    {
        return Cache::remember('moderators_list', 300, fn () =>
            User::whereIn('role', ['admin', 'moderator'])->get()
        );
    }

    /**
     * Notifica moderadores quando uma crise é detetada numa mensagem.
     * Separa a lógica de alerta do fluxo principal de envio de mensagem.
     */
    public function notifyCrisis($message, $room, array $crisisResult): void
    {
        $moderators = $this->getModerators();
        if ($moderators->isNotEmpty()) {
            Notification::send($moderators, new ModeratorCrisisAlert($message, $room, $crisisResult));
        }
    }
}
