<?php

namespace App\Filament\Resources\Missions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'post' => 'info',
                        'comment' => 'success',
                        'reaction' => 'warning',
                        'diary' => 'primary',
                        'chat' => 'danger',
                        'buddy' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('goal_count')
                    ->label('Objetivo')
                    ->sortable(),
                TextColumn::make('flames_reward')
                    ->label('Chamas')
                    ->sortable(),
                TextColumn::make('available_from')
                    ->label('De')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('available_until')
                    ->label('Ate')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('Participantes')
                    ->counts('users')
                    ->sortable(),
            ])
            ->defaultSort('available_from', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
