<?php

namespace App\Filament\Resources\PlaylistSongs\Pages;

use App\Filament\Resources\PlaylistSongs\PlaylistSongResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlaylistSong extends EditRecord
{
    protected static string $resource = PlaylistSongResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
