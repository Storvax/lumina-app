<?php

namespace App\Filament\Resources\BuddySessions;

use App\Filament\Resources\BuddySessions\Pages\ListBuddySessions;
use App\Filament\Resources\BuddySessions\Tables\BuddySessionsTable;
use App\Models\BuddySession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BuddySessionResource extends Resource
{
    protected static ?string $model = BuddySession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static ?string $navigationLabel = 'Sessoes Buddy';

    protected static ?string $modelLabel = 'Sessao Buddy';

    protected static ?string $pluralModelLabel = 'Sessoes Buddy';

    protected static ?string $navigationGroup = 'Comunidade';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return BuddySessionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBuddySessions::route('/'),
        ];
    }
}
