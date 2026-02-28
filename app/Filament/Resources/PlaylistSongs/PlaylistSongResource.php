<?php

namespace App\Filament\Resources\PlaylistSongs;

use App\Filament\Resources\PlaylistSongs\Pages\CreatePlaylistSong;
use App\Filament\Resources\PlaylistSongs\Pages\EditPlaylistSong;
use App\Filament\Resources\PlaylistSongs\Pages\ListPlaylistSongs;
use App\Filament\Resources\PlaylistSongs\Schemas\PlaylistSongForm;
use App\Filament\Resources\PlaylistSongs\Tables\PlaylistSongsTable;
use App\Models\PlaylistSong;
use BackedEnum;
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

    protected static ?string $navigationGroup = 'Conteudo';

    public static function form(Schema $schema): Schema
    {
        return PlaylistSongForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Música')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('artist')
                    ->label('Artista')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Sugerido por')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('votes_count')
                    ->label('Votos')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\IconColumn::make('is_weekly_winner')
                    ->label('Vencedora')
                    ->boolean()
                    ->color('warning'), // Fica amarelo/dourado se for verdade
            ])
            ->defaultSort('votes_count', 'desc') // Ordena pelas mais votadas por defeito
            ->filters([
                //
            ])
            ->actions([
                // Botão para tornar Música da Semana
                Tables\Actions\Action::make('coroar')
                    ->label('Tornar Vencedora')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Definir como Música da Semana?')
                    ->modalDescription('Esta ação vai remover o destaque da música anterior.')
                    ->visible(fn ($record) => !$record->is_weekly_winner)
                    ->action(function ($record) {
                        // 1. Tira o título a todas as músicas
                        \App\Models\PlaylistSong::query()->update(['is_weekly_winner' => false]);
                        
                        // 2. Dá o título a esta
                        $record->update(['is_weekly_winner' => true]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Música da semana atualizada!')
                            ->success()
                            ->send();
                    }),

                // Ouvir link
                Tables\Actions\Action::make('spotify')
                    ->label('Ouvir')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->url(fn ($record) => $record->spotify_url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->spotify_url)),

                // Apagar troll
                Tables\Actions\DeleteAction::make()
                    ->label('Apagar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
