<?php

namespace App\Filament\Resources\PlaylistSongs\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PlaylistSongForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalhes da Música')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('artist')
                            ->label('Artista')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('spotify_url')
                            ->label('Link Spotify')
                            ->url()
                            ->maxLength(500),

                        TextInput::make('cover_url')
                            ->label('URL da Capa')
                            ->url()
                            ->maxLength(500),

                        TextInput::make('votes_count')
                            ->label('Votos')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_weekly_winner')
                            ->label('Música da Semana')
                            ->helperText('Marcar como vencedora da semana.'),
                    ]),
            ]);
    }
}
