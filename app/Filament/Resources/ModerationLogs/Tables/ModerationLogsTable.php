<?php

namespace App\Filament\Resources\ModerationLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ModerationLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Moderador')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('action')
                    ->label('Acao')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mute' => 'warning',
                        'delete_msg' => 'danger',
                        'crisis_on' => 'danger',
                        'crisis_off' => 'success',
                        'pin' => 'info',
                        'shadowban' => 'danger',
                        'delete' => 'danger',
                        'lock' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'mute' => 'Silenciou',
                        'delete_msg' => 'Apagou Msg',
                        'crisis_on' => 'Crise ON',
                        'crisis_off' => 'Crise OFF',
                        'pin' => 'Fixou Msg',
                        'shadowban' => 'Shadowban',
                        'delete' => 'Eliminou',
                        'lock' => 'Trancou',
                        default => $state,
                    }),
                TextColumn::make('room.name')
                    ->label('Sala')
                    ->default('—')
                    ->sortable(),
                TextColumn::make('targetUser.name')
                    ->label('Alvo')
                    ->default('—'),
                TextColumn::make('details')
                    ->label('Detalhes')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->label('Acao')
                    ->options([
                        'mute' => 'Silenciou',
                        'delete_msg' => 'Apagou Msg',
                        'crisis_on' => 'Crise ON',
                        'crisis_off' => 'Crise OFF',
                        'pin' => 'Fixou Msg',
                        'shadowban' => 'Shadowban',
                    ]),
            ]);
    }
}
