<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome da Fogueira')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),

                Select::make('icon')
                    ->label('Ícone')
                    ->options([
                        'ri-thunderstorms-line' => 'Tempestade (Ansiedade)',
                        'ri-candle-line' => 'Vela (Luto)',
                        'ri-rainy-line' => 'Chuva (Depressão)',
                        'ri-briefcase-line' => 'Trabalho (Stress)',
                        'ri-emotion-laugh-line' => 'Riso (Off-Topic)',
                        'ri-heart-pulse-fill' => 'Coração (Saúde)',
                        'ri-leaf-fill' => 'Folha (Natureza)',
                        'ri-fire-fill' => 'Fogo',
                        'ri-moon-clear-fill' => 'Lua (Noite)',
                        'ri-drop-fill' => 'Gota (Tristeza)',
                        'ri-windy-fill' => 'Vento (Calma)',
                        'ri-discuss-fill' => 'Conversa (Geral)',
                        'ri-mental-health-line' => 'Saúde Mental',
                        'ri-empathize-line' => 'Empatia',
                        'ri-user-heart-line' => 'Amor Próprio',
                        'ri-group-line' => 'Grupo',
                    ])
                    ->searchable()
                    ->default('ri-discuss-fill'),

                Select::make('color')
                    ->label('Cor')
                    ->options([
                        'indigo' => 'Indigo',
                        'rose' => 'Rosa',
                        'emerald' => 'Verde',
                        'blue' => 'Azul',
                        'amber' => 'Âmbar',
                        'orange' => 'Laranja',
                        'teal' => 'Teal',
                        'violet' => 'Violeta',
                        'slate' => 'Cinza',
                    ])
                    ->default('indigo'),

                Toggle::make('is_active')
                    ->label('Ativa')
                    ->helperText('Desativa para esconder a sala sem a apagar.')
                    ->default(true),

                Toggle::make('is_private')
                    ->label('Privada')
                    ->helperText('Salas privadas são usadas para buddy sessions e não aparecem na listagem pública.')
                    ->default(false),

                Toggle::make('is_crisis_mode')
                    ->label('Modo Crise')
                    ->helperText('Ativa slow mode reforçado (15s entre mensagens).')
                    ->default(false),

                Textarea::make('pinned_message')
                    ->label('Mensagem Fixada')
                    ->rows(2)
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }
}
