<?php

namespace App\Filament\Resources\DailyLogs\Pages;

use App\Filament\Resources\DailyLogs\DailyLogResource;
use Filament\Resources\Pages\ViewRecord;
use App\Models\DataAccessLog;
use Illuminate\Support\Facades\Auth;

class ViewDailyLog extends ViewRecord
{
    protected static string $resource = DailyLogResource::class;

    /**
     * Interceta a visualização do registo para fins de auditoria (RGPD).
     */
    public function mount(int | string $record): void
    {
        parent::mount($record);

        // O registo já foi carregado neste ponto
        $log = $this->getRecord();

        // Regista o acesso na tabela de auditoria
        DataAccessLog::create([
            'user_id' => $log->user_id, // O dono do diário
            'accessed_by' => Auth::id(), // O moderador/admin
            'data_type' => 'daily_log',
            'purpose' => 'Revisão de conteúdo sensível a partir do painel de administração',
            'ip_address' => request()->ip(),
        ]);
    }
}