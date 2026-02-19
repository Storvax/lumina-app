<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\WeeklyEmotionalSummary;
use Carbon\Carbon;

class SendWeeklySummaries extends Command
{
    protected $signature = 'lumina:weekly-summary';
    protected $description = 'Envia o resumo semanal aos utilizadores que ativaram a opção';

    public function handle()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Apenas utilizadores que pediram o resumo
        $users = User::where('wants_weekly_summary', true)->get();

        foreach ($users as $user) {
            $logsCount = $user->dailyLogs()->whereBetween('log_date', [$startOfWeek, $endOfWeek])->count();
            $reactionsReceived = \App\Models\PostReaction::whereHas('post', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

            // Só envia se ele fez ALGUMA coisa na plataforma (evita spam vazio)
            if ($logsCount > 0 || $reactionsReceived > 0) {
                $stats = [
                    'logs_count' => $logsCount,
                    'hugs_received' => $reactionsReceived,
                    'flames_earned' => $user->flames // Total até agora
                ];

                $user->notify(new WeeklyEmotionalSummary($stats));
            }
        }
    }
}