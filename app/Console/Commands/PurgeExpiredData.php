<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Purgar dados sensíveis de acordo com a janela de retenção 
 * definida pelo utilizador. (Proteção by-design)
 */
class PurgeExpiredData extends Command
{
    protected $signature = 'lumina:purge-data';
    protected $description = 'Remove permanentemente dados expirados com base nas preferências dos utilizadores.';

    public function handle()
    {
        $this->info('Iniciando purga de dados expirados...');
        $purgedCount = 0;

        // Processa em blocos (chunks) para evitar sobrecarga de memória (OOM) no servidor
        User::whereNotNull('diary_retention_days')->chunkById(100, function ($users) use (&$purgedCount) {
            foreach ($users as $user) {
                $cutoffDate = Carbon::today()->subDays($user->diary_retention_days);
                
                $deleted = $user->dailyLogs()
                    ->where('log_date', '<', $cutoffDate)
                    ->delete();
                
                $purgedCount += $deleted;
            }
        });

        Log::info("Purga de Dados Concluída. {$purgedCount} registos de diário eliminados permanentemente.");
        $this->info("Concluído. {$purgedCount} registos apagados.");
    }
}