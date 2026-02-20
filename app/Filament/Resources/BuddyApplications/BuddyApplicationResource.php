<?php

namespace App\Filament\Resources\BuddyApplications;

use App\Filament\Resources\BuddyApplications\Pages\CreateBuddyApplication;
use App\Filament\Resources\BuddyApplications\Pages\EditBuddyApplication;
use App\Filament\Resources\BuddyApplications\Pages\ListBuddyApplications;
use App\Models\BuddyApplication;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class BuddyApplicationResource extends Resource
{
    protected static ?string $model = BuddyApplication::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-heart';
    protected static \UnitEnum|string|null $navigationGroup = 'Comunidade';
    
    protected static ?string $modelLabel = 'Candidatura de Ouvinte';
    protected static ?string $pluralModelLabel = 'Candidaturas de Ouvintes';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\BuddyApplications\Schemas\BuddyApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Candidato')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match($state) {
                        'pending' => 'Pendente',
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                        default => $state,
                    }),
                    
                TextColumn::make('created_at')
                    ->label('Data de Submissão')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendentes',
                        'approved' => 'Aprovados',
                        'rejected' => 'Rejeitados',
                    ])
                    ->label('Filtrar por Estado'),
            ])
            ->actions([
                // 1. Ver a motivação
                Action::make('ver_motivacao')
                    ->label('Ler Motivação')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->form([
                        Textarea::make('motivation')
                            ->label('Porque quer ser ouvinte?')
                            ->disabled()
                            ->rows(5),
                    ])
                    ->fillForm(fn (BuddyApplication $record): array => [
                        'motivation' => $record->motivation,
                    ])
                    ->modalSubmitAction(false) 
                    ->modalCancelActionLabel('Fechar'),

                // 2. Aprovar
                Action::make('aprovar')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'approved']);
                        $record->user->update(['is_buddy' => true]);
                        
                        Notification::make()
                            ->title('Ouvinte Aprovado!')
                            ->body('O utilizador já tem acesso às salas de crise.')
                            ->success()
                            ->send();
                    }),

                // 3. Rejeitar
                Action::make('rejeitar')
                    ->label('Rejeitar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBuddyApplications::route('/'),
            'create' => CreateBuddyApplication::route('/create'),
            'edit' => EditBuddyApplication::route('/{record}/edit'),
        ];
    }
}