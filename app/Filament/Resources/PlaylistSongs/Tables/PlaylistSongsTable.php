<?php

namespace App\Filament\Resources\PlaylistSongs\Tables;

use App\Models\PlaylistSong;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlaylistSongsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Música')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('artist')
                    ->label('Artista')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Sugerido por')
                    ->sortable(),

                TextColumn::make('votes_count')
                    ->label('Votos')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                IconColumn::make('is_weekly_winner')
                    ->label('Vencedora')
                    ->boolean()
                    ->color('warning'),
            ])
            ->defaultSort('votes_count', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Action::make('coroar')
                    ->label('Tornar Vencedora')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Definir como Música da Semana?')
                    ->modalDescription('Esta ação vai remover o destaque da música anterior.')
                    ->visible(fn ($record) => !$record->is_weekly_winner)
                    ->action(function ($record) {
                        PlaylistSong::query()->update(['is_weekly_winner' => false]);
                        $record->update(['is_weekly_winner' => true]);

                        Notification::make()
                            ->title('Música da semana atualizada!')
                            ->success()
                            ->send();
                    }),

                Action::make('spotify')
                    ->label('Ouvir')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->url(fn ($record) => $record->spotify_url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->spotify_url)),

                DeleteAction::make()
                    ->label('Apagar'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
