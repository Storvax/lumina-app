<?php

namespace App\Filament\Resources\ModerationLogs\Pages;

use App\Filament\Resources\ModerationLogs\ModerationLogResource;
use Filament\Resources\Pages\ListRecords;

class ListModerationLogs extends ListRecords
{
    protected static string $resource = ModerationLogResource::class;
}
