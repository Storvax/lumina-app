<?php

namespace App\Filament\Resources\ModerationLogs;

use App\Filament\Resources\ModerationLogs\Pages\ListModerationLogs;
use App\Filament\Resources\ModerationLogs\Tables\ModerationLogsTable;
use App\Models\ModerationLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ModerationLogResource extends Resource
{
    protected static ?string $model = ModerationLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Logs de Moderacao';

    protected static ?string $modelLabel = 'Log';

    protected static ?string $pluralModelLabel = 'Logs de Moderacao';

    protected static ?string $navigationGroup = 'Moderacao';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ModerationLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModerationLogs::route('/'),
        ];
    }
}
