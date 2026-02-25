<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tarefas Agendadas (Cron)
| Para ativar, adicionar ao crontab do servidor:
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|--------------------------------------------------------------------------
*/

// Resumo emocional semanal — enviado aos Domingos às 10h (UTC)
// Só envia utilizadores com `wants_weekly_summary = true` e atividade na semana
Schedule::command('lumina:weekly-summary')->weeklyOn(0, '10:00');

// Purga de dados com retenção expirada — corre diariamente às 03h (UTC)
// Processa em chunks para não bloquear o servidor; respeita a configuração de cada utilizador
Schedule::command('lumina:purge-data')->dailyAt('03:00');
