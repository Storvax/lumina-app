<?php

namespace App\Filament\Resources\DailyLogs\Pages;

use App\Filament\Resources\DailyLogs\DailyLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyLog extends CreateRecord
{
    protected static string $resource = DailyLogResource::class;
}
