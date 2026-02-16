<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Enums\FontWeight;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Avatar (DiceBear)
                ImageColumn::make('avatar')
                    ->defaultImageUrl(fn ($record) => 'https://api.dicebear.com/7.x/notionists/svg?seed=' . $record->name)
                    ->circular()
                    ->label('Foto'),

                // Nome e Email
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn ($record) => $record->email)
                    ->label('Utilizador'),

                // BATERIA SOCIAL (Visualização de Risco)
                TextColumn::make('energy_level')
                    ->label('Estado Emocional')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1', '2' => 'danger',  // Vermelho
                        '3' => 'warning',      // Amarelo
                        '4', '5' => 'success', // Verde
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        '1' => '🔴 Crítico',
                        '2' => '🟠 Baixo',
                        '3' => '🟡 Médio',
                        '4' => '🔵 Estável',
                        '5' => '🟢 Radiante',
                        default => 'N/A',
                    }),

                TextColumn::make('created_at')
                    ->date('d/m/Y')
                    ->label('Membro Desde')
                    ->sortable(),
            ])
            ->filters([
                // Filtro para encontrar pessoas em risco
                SelectFilter::make('energy_level')
                    ->label('Risco / Energia')
                    ->options([
                        '1' => '🔴 Crítico (Nível 1)',
                        '2' => '🟠 Baixo (Nível 2)',
                        '5' => '🟢 Estável (Nível 5)',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}