<?php

namespace App\Filament\Widgets;

use App\Models\Message;
use App\Models\Post;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Widget de fila de moderação para o painel Filament.
 *
 * Lista posts recentes ordenados por nível de risco (high → medium → low)
 * para que os moderadores possam priorizar conteúdo potencialmente perigoso.
 */
class ModerationQueueWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Fila de Moderação — Posts por Risco';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Post::query()
                    ->with('user')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->orderByRaw("CASE risk_level WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Autor')
                    ->limit(20)
                    ->searchable(),

                TextColumn::make('content')
                    ->label('Conteúdo')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('risk_level')
                    ->label('Risco')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high'   => 'danger',
                        'medium' => 'warning',
                        default  => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'high'   => 'Alto',
                        'medium' => 'Médio',
                        default  => 'Baixo',
                    }),

                TextColumn::make('sentiment')
                    ->label('Sentimento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'distress' => 'danger',
                        'positive' => 'success',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'distress' => 'Sofrimento',
                        'positive' => 'Positivo',
                        default    => 'Neutro',
                    }),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Post $record) => $record->update(['risk_level' => 'low']))
                    ->requiresConfirmation(),

                Action::make('remove')
                    ->label('Remover')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(fn (Post $record) => $record->delete())
                    ->requiresConfirmation(),

                Action::make('shadowban')
                    ->label('Shadowban')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->action(function (Post $record) {
                        $record->user->update([
                            'shadowbanned_until' => now()->addDays(7),
                        ]);
                    })
                    ->requiresConfirmation(),
            ])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('Sem posts para moderar')
            ->emptyStateDescription('Nenhum post nos últimos 7 dias requer atenção.');
    }
}
