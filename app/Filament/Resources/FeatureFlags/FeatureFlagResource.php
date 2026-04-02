<?php

namespace App\Filament\Resources\FeatureFlags;

use App\Filament\Resources\FeatureFlags\Pages\ListFeatureFlags;
use App\Filament\Resources\FeatureFlags\Pages\CreateFeatureFlag;
use App\Filament\Resources\FeatureFlags\Pages\EditFeatureFlag;
use App\Models\FeatureFlag;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Tables;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class FeatureFlagResource extends Resource
{
    protected static ?string $model = FeatureFlag::class;

    protected static ?string $navigationLabel = 'Feature Flags';

    protected static ?string $modelLabel = 'Feature Flag';

    protected static ?string $pluralModelLabel = 'Feature Flags';

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 99;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('name')
                ->label('Nome da Feature')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(100),
            Forms\Components\TextInput::make('description')
                ->label('Descrição')
                ->maxLength(255),
            Forms\Components\Toggle::make('enabled')
                ->label('Ativa')
                ->default(false),
            Forms\Components\TextInput::make('rollout_percentage')
                ->label('Percentagem de Rollout')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->default(0)
                ->suffix('%'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Feature')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(40),
                Tables\Columns\IconColumn::make('enabled')
                    ->label('Ativa')
                    ->boolean(),
                Tables\Columns\TextColumn::make('rollout_percentage')
                    ->label('Rollout')
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizada')
                    ->since(),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeatureFlags::route('/'),
            'create' => CreateFeatureFlag::route('/create'),
            'edit' => EditFeatureFlag::route('/{record}/edit'),
        ];
    }
}
