<?php

namespace App\Filament\Resources\DailyLogs\Pages;

use App\Filament\Resources\DailyLogs\DailyLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyLog extends EditRecord
{
    protected static string $resource = DailyLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
