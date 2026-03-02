<?php

namespace App\Filament\Resources\LibraryResources;

use App\Filament\Resources\LibraryResources\Pages\CreateLibraryResource;
use App\Filament\Resources\LibraryResources\Pages\EditLibraryResource;
use App\Filament\Resources\LibraryResources\Pages\ListLibraryResources;
use App\Filament\Resources\LibraryResources\Schemas\LibraryResourceForm;
use App\Filament\Resources\LibraryResources\Tables\LibraryResourcesTable;
use App\Models\Resource;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource as FilamentResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LibraryResourceResource extends FilamentResource
{
    protected static ?string $model = Resource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'Biblioteca';

    protected static ?string $modelLabel = 'Recurso';

    protected static ?string $pluralModelLabel = 'Recursos';

    protected static string|UnitEnum|null $navigationGroup = 'Conteudo';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return LibraryResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LibraryResourcesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLibraryResources::route('/'),
            'create' => CreateLibraryResource::route('/create'),
            'edit' => EditLibraryResource::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_approved', false)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
