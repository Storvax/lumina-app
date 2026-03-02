<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informacao Basica')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verificado em'),
                    ]),

                Section::make('Papel e Permissoes')
                    ->columns(2)
                    ->schema([
                        Select::make('role')
                            ->label('Papel')
                            ->options([
                                'user' => 'Utilizador',
                                'moderator' => 'Moderador',
                                'admin' => 'Administrador',
                            ])
                            ->default('user')
                            ->required(),
                        Toggle::make('is_buddy')
                            ->label('Buddy Ativo')
                            ->helperText('Permite que este utilizador seja buddy de apoio.'),
                        Toggle::make('is_buddy_available')
                            ->label('Buddy Disponivel')
                            ->helperText('Aparece como disponivel para novas sessoes buddy.'),
                    ]),

                Section::make('Moderacao')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('banned_at')
                            ->label('Banido em')
                            ->helperText('Definir uma data bane o utilizador. Limpar para desbanir.'),
                        DateTimePicker::make('shadowbanned_until')
                            ->label('Shadowban ate')
                            ->helperText('O utilizador nao sabe que esta shadowbanido. As suas mensagens ficam invisiveis.'),
                    ]),

                Section::make('Gamificacao')
                    ->columns(3)
                    ->schema([
                        TextInput::make('flames')
                            ->label('Chamas')
                            ->numeric()
                            ->default(0),
                        TextInput::make('current_streak')
                            ->label('Streak Atual')
                            ->numeric()
                            ->default(0),
                        TextInput::make('energy_level')
                            ->label('Nivel de Energia')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5),
                    ]),
            ]);
    }
}
