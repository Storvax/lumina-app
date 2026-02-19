<?php

namespace App\Filament\Resources\BuddyApplications;

use App\Filament\Resources\BuddyApplications\Pages\CreateBuddyApplication;
use App\Filament\Resources\BuddyApplications\Pages\EditBuddyApplication;
use App\Filament\Resources\BuddyApplications\Pages\ListBuddyApplications;
use App\Filament\Resources\BuddyApplications\Schemas\BuddyApplicationForm;
use App\Models\BuddyApplication;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BuddyApplicationResource extends Resource
{
    protected static ?string $model = BuddyApplication::class;

    // Ícone e nomes no menu lateral em português
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $modelLabel = 'Candidatura de Ouvinte';
    protected static ?string $pluralModelLabel = 'Candidaturas de Ouvintes';
    protected static ?string $navigationGroup = 'Comunidade';

    // Mantive a tua estrutura customizada de Form se estiveres a usar uma classe separada
    public static function form(Form $form): Form
    {
        // Nota: Se usares a classe padrão do Filament, passa apenas o $form e os teus components
        return BuddyApplicationForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Candidato')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('status')
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
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Submissão')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // Mostra os mais recentes primeiro
            ->filters([
                // Filtro rápido para ver só os pendentes
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendentes',
                        'approved' => 'Aprovados',
                        'rejected' => 'Rejeitados',
                    ])
                    ->label('Filtrar por Estado'),
            ])
            ->actions([
                // 1. Ver a motivação num Modal
                Tables\Actions\Action::make('ver_motivacao')
                    ->label('Ler Motivação')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('motivation')
                            ->label('Porque quer ser ouvinte?')
                            ->disabled()
                            ->rows(5),
                    ])
                    // Formata a ação para preencher o formulário com o dado da BD e desativa o botão de submit (é só leitura)
                    ->fillForm(fn (BuddyApplication $record): array => [
                        'motivation' => $record->motivation,
                    ])
                    ->modalSubmitAction(false) 
                    ->modalCancelActionLabel('Fechar'),

                // 2. Botão Aprovar
                Tables\Actions\Action::make('aprovar')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'approved']);
                        // Dá a flag de ouvinte ao utilizador associado!
                        $record->user->update(['is_buddy' => true]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Ouvinte Aprovado!')
                            ->body('O utilizador já tem acesso às salas de crise.')
                            ->success()
                            ->send();
                    }),

                // 3. Botão Rejeitar
                Tables\Actions\Action::make('rejeitar')
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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