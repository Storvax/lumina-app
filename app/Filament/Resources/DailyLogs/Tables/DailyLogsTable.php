<?php

namespace App\Filament\Resources\DailyLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DailyLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Utilizador')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('log_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('mood_level')
                    ->label('Humor')
                    ->badge()
                    ->color(fn (int $state): string => match($state) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'gray',
                        4 => 'success',
                        5 => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('note')
                    ->label('Nota')
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Criado')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
