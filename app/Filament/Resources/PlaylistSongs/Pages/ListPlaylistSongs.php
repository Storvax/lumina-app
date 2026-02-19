<?php

namespace App\Filament\Resources\PlaylistSongs\Pages;

use App\Filament\Resources\PlaylistSongs\PlaylistSongResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlaylistSongs extends ListRecords
{
    protected static string $resource = PlaylistSongResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
