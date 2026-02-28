<?php

namespace App\Filament\Resources\Rooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->color('gray'),
                TextColumn::make('color')
                    ->label('Cor')
                    ->badge(),
                IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_private')
                    ->label('Privada')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_crisis_mode')
                    ->label('Crise')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('messages_count')
                    ->label('Mensagens')
                    ->counts('messages')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Ativa'),
                TernaryFilter::make('is_private')
                    ->label('Privada'),
            ])
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
