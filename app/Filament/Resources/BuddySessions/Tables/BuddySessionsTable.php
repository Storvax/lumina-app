<?php

namespace App\Filament\Resources\BuddySessions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BuddySessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Utilizador')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('buddy.name')
                    ->label('Buddy')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('room.name')
                    ->label('Sala')
                    ->default('—'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Pendente',
                        'active' => 'Ativa',
                        'completed' => 'Concluida',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    }),
                TextColumn::make('rating')
                    ->label('Avaliacao')
                    ->formatStateUsing(fn ($state) => $state ? $state . '/5' : '—')
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Fim')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendente',
                        'active' => 'Ativa',
                        'completed' => 'Concluida',
                        'cancelled' => 'Cancelada',
                    ]),
            ]);
    }
}
