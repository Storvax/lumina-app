<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Support\Enums\FontWeight;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->defaultImageUrl(fn ($record) => 'https://api.dicebear.com/7.x/notionists/svg?seed=' . $record->name)
                    ->circular()
                    ->label(''),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn ($record) => $record->email)
                    ->label('Utilizador'),

                TextColumn::make('role')
                    ->label('Papel')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'moderator' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'admin' => 'Admin',
                        'moderator' => 'Moderador',
                        default => 'Utilizador',
                    })
                    ->sortable(),

                TextColumn::make('energy_level')
                    ->label('Energia')
                    ->badge()
                    ->color(fn ($state): string => match ((string) $state) {
                        '1', '2' => 'danger',
                        '3' => 'warning',
                        '4', '5' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ((string) $state) {
                        '1' => 'Critico',
                        '2' => 'Baixo',
                        '3' => 'Medio',
                        '4' => 'Estavel',
                        '5' => 'Radiante',
                        default => 'N/A',
                    }),

                TextColumn::make('flames')
                    ->label('Chamas')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('banned_at')
                    ->label('Banido')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->banned_at !== null)
                    ->trueIcon('heroicon-o-no-symbol')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('created_at')
                    ->date('d/m/Y')
                    ->label('Registo')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->label('Papel')
                    ->options([
                        'admin' => 'Admin',
                        'moderator' => 'Moderador',
                        'user' => 'Utilizador',
                    ]),
                SelectFilter::make('energy_level')
                    ->label('Energia')
                    ->options([
                        '1' => 'Critico (1)',
                        '2' => 'Baixo (2)',
                        '3' => 'Medio (3)',
                        '4' => 'Estavel (4)',
                        '5' => 'Radiante (5)',
                    ]),
                TernaryFilter::make('banned')
                    ->label('Banido')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('banned_at'),
                        false: fn ($query) => $query->whereNull('banned_at'),
                    ),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
