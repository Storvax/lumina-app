<?php

namespace App\Filament\Resources\Missions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Missao')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Titulo')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'post' => 'Criar post',
                                'comment' => 'Comentar',
                                'reaction' => 'Reagir',
                                'login' => 'Login diario',
                                'diary' => 'Escrever no diario',
                                'chat' => 'Mensagem no chat',
                                'buddy' => 'Sessao buddy',
                            ])
                            ->required(),
                        TextInput::make('goal_count')
                            ->label('Objetivo (quantidade)')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                        TextInput::make('flames_reward')
                            ->label('Recompensa (chamas)')
                            ->numeric()
                            ->default(5)
                            ->minValue(1)
                            ->required(),
                    ]),

                Section::make('Disponibilidade')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('available_from')
                            ->label('Disponivel de')
                            ->required(),
                        DatePicker::make('available_until')
                            ->label('Disponivel ate')
                            ->required()
                            ->afterOrEqual('available_from'),
                    ]),
            ]);
    }
}
