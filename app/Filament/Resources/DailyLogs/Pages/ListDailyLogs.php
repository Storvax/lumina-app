<?php

namespace App\Filament\Resources\DailyLogs\Pages;

use App\Filament\Resources\DailyLogs\DailyLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyLogs extends ListRecords
{
    protected static string $resource = DailyLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
