<?php

namespace App\Filament\Resources\BuddyApplications\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BuddyApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Candidatura')
                    ->schema([
                        Textarea::make('motivation')
                            ->label('Motivação')
                            ->rows(5)
                            ->disabled(),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendente',
                                'approved' => 'Aprovado',
                                'rejected' => 'Rejeitado',
                            ])
                            ->required(),
                    ]),
            ]);
    }
}
