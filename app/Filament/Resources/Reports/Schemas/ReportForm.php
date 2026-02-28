<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Denuncia')
                    ->schema([
                        Placeholder::make('reporter')
                            ->label('Denunciado por')
                            ->content(fn ($record) => $record->user?->name ?? 'N/A'),
                        Placeholder::make('post_title')
                            ->label('Post')
                            ->content(fn ($record) => $record->post?->title ?? 'Post removido'),
                        Placeholder::make('reason_display')
                            ->label('Motivo')
                            ->content(fn ($record) => $record->reason),
                        Placeholder::make('details_display')
                            ->label('Detalhes')
                            ->content(fn ($record) => $record->details ?? 'Sem detalhes adicionais.'),
                        Placeholder::make('created')
                            ->label('Data da denuncia')
                            ->content(fn ($record) => $record->created_at?->format('d/m/Y H:i')),
                    ]),

                Section::make('Resolucao')
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendente',
                                'resolved' => 'Resolvida',
                                'dismissed' => 'Descartada',
                            ])
                            ->required(),
                    ]),
            ]);
    }
}
