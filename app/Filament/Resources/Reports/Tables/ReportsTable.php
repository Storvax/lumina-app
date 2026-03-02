<?php

namespace App\Filament\Resources\Reports\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Denunciado por')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Risco' => 'danger',
                        'Spam' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'resolved' => 'success',
                        'dismissed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Pendente',
                        'resolved' => 'Resolvida',
                        'dismissed' => 'Descartada',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendente',
                        'resolved' => 'Resolvida',
                        'dismissed' => 'Descartada',
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
