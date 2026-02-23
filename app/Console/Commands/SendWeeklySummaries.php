<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\WeeklyEmotionalSummary;
use Carbon\Carbon;

/**
 * Compila e envia um resumo semanal positivo aos utilizadores.
 * Executar via cron job semanalmente (ex: Domingos às 10:00).
 */
class SendWeeklySummaries extends Command
{
    protected $signature = 'lumina:weekly-summary';
    protected $description = 'Envia a retrospetiva gentil da semana para os utilizadores que aderiram.';

    public function handle(): void
    {
        $this->info('A gerar resumos semanais terapêuticos...');

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Apenas para quem fez opt-in e não está com a conta hibernada
        User::where('wants_weekly_summary', true)
            ->whereNull('hibernated_at')
            ->chunkById(100, function ($users) use ($startOfWeek, $endOfWeek) {
                foreach ($users as $user) {
                    
                    // Recolha de métricas positivas da semana
                    $logsCount = $user->dailyLogs()
                                      ->whereBetween('log_date', [$startOfWeek, $endOfWeek])
                                      ->count();

                    $hugsReceived = \App\Models\PostReaction::whereHas('post', function($q) use ($user) {
                                        $q->where('user_id', $user->id);
                                    })
                                    ->where('type', 'hug')
                                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                                    ->count();

                    // Só envia se houve alguma interação (evita spam de emails vazios que causam ansiedade)
                    if ($logsCount > 0 || $hugsReceived > 0) {
                        $stats = [
                            'logs_count' => $logsCount,
                            'hugs_received' => $hugsReceived,
                            'current_streak' => $user->current_streak
                        ];

                        $user->notify(new WeeklyEmotionalSummary($stats));
                    }
                }
            });

        $this->info('Resumos enviados com sucesso.');
    }
}