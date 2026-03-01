<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\GentleReEngagement;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Detecta utilizadores com padrões de desligamento progressivo
 * e envia uma notificação calorosa de re-engagement.
 *
 * Regras heurísticas (sem ML):
 *  - Inactivo há 3+ dias tendo histórico de uso regular.
 *  - Não hibernado, não banido.
 *  - Máximo 1 notificação de re-engagement por semana.
 */
class DetectDisengagement extends Command
{
    protected $signature = 'lumina:detect-disengagement';
    protected $description = 'Detecta utilizadores a desligar-se e envia notificações de re-engagement.';

    public function handle(): void
    {
        $this->info('A analisar padrões de engagement...');

        $threshold = Carbon::now()->subDays(3);
        $oneWeekAgo = Carbon::now()->subWeek();
        $notified = 0;

        User::whereNotNull('last_activity_at')
            ->where('last_activity_at', '<', $threshold)
            ->where('last_activity_at', '>', Carbon::now()->subMonths(3)) // Activo nos últimos 3 meses
            ->whereNull('hibernated_at')
            ->whereNull('banned_at')
            ->chunkById(100, function ($users) use ($oneWeekAgo, &$notified) {
                foreach ($users as $user) {
                    // Verificar se já recebeu esta notificação na última semana
                    $alreadyNotified = $user->notifications()
                        ->where('type', GentleReEngagement::class)
                        ->where('created_at', '>=', $oneWeekAgo)
                        ->exists();

                    if ($alreadyNotified) {
                        continue;
                    }

                    $daysSince = (int) $user->last_activity_at->diffInDays(now());
                    $user->notify(new GentleReEngagement($daysSince));
                    $notified++;
                }
            });

        Log::info("Disengagement detection: {$notified} utilizadores notificados.");
        $this->info("Concluído. {$notified} notificações enviadas.");
    }
}
