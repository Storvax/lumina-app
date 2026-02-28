<?php

namespace App\Filament\Resources\LibraryResources\Pages;

use App\Filament\Resources\LibraryResources\LibraryResourceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLibraryResource extends EditRecord
{
    protected static string $resource = LibraryResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
