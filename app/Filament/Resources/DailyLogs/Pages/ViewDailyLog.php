<?php

namespace App\Filament\Resources\DailyLogs\Pages;

use App\Filament\Resources\DailyLogs\DailyLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDailyLog extends ViewRecord
{
    protected static string $resource = DailyLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
