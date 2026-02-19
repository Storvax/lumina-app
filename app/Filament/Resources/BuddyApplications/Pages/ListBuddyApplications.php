<?php

namespace App\Filament\Resources\BuddyApplications\Pages;

use App\Filament\Resources\BuddyApplications\BuddyApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBuddyApplications extends ListRecords
{
    protected static string $resource = BuddyApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
