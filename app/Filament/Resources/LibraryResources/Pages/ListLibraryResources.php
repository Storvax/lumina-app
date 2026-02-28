<?php

namespace App\Filament\Resources\LibraryResources\Pages;

use App\Filament\Resources\LibraryResources\LibraryResourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLibraryResources extends ListRecords
{
    protected static string $resource = LibraryResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo Recurso'),
        ];
    }
}
