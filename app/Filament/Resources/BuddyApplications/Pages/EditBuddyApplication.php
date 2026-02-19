<?php

namespace App\Filament\Resources\BuddyApplications\Pages;

use App\Filament\Resources\BuddyApplications\BuddyApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBuddyApplication extends EditRecord
{
    protected static string $resource = BuddyApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
