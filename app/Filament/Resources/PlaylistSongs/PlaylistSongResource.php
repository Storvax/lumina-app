<?php

namespace App\Filament\Resources\PlaylistSongs;

use App\Filament\Resources\PlaylistSongs\Pages\CreatePlaylistSong;
use App\Filament\Resources\PlaylistSongs\Pages\EditPlaylistSong;
use App\Filament\Resources\PlaylistSongs\Pages\ListPlaylistSongs;
use App\Filament\Resources\PlaylistSongs\Schemas\PlaylistSongForm;
use App\Filament\Resources\PlaylistSongs\Tables\PlaylistSongsTable;
use App\Models\PlaylistSong;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlaylistSongResource extends Resource
{
    protected static ?string $model = PlaylistSong::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMusicalNote;

    protected static ?string $navigationLabel = 'Playlist';

    protected static ?string $modelLabel = 'Musica';

    protected static ?string $pluralModelLabel = 'Playlist';

    protected static string|UnitEnum|null $navigationGroup = 'Conteudo';

    public static function form(Schema $schema): Schema
    {
        return PlaylistSongForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlaylistSongsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlaylistSongs::route('/'),
            'create' => CreatePlaylistSong::route('/create'),
            'edit' => EditPlaylistSong::route('/{record}/edit'),
        ];
    }
}
