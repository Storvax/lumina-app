<?php

namespace App\Filament\Resources\LibraryResources\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LibraryResourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('author')
                    ->label('Autor')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'book' => 'info',
                        'podcast' => 'danger',
                        'video' => 'warning',
                        'article' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'book' => 'Livro',
                        'podcast' => 'Podcast',
                        'video' => 'Video',
                        'article' => 'Artigo',
                        default => $state,
                    }),
                TextColumn::make('user.name')
                    ->label('Sugerido por')
                    ->default('Admin'),
                IconColumn::make('is_approved')
                    ->label('Aprovado')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('votes_count')
                    ->label('Votos')
                    ->counts('votes')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'book' => 'Livro',
                        'podcast' => 'Podcast',
                        'video' => 'Video',
                        'article' => 'Artigo',
                    ]),
                TernaryFilter::make('is_approved')
                    ->label('Aprovado'),
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
