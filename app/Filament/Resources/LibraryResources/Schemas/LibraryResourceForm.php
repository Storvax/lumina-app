<?php

namespace App\Filament\Resources\LibraryResources\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LibraryResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('author')
                    ->label('Autor')
                    ->maxLength(255),
                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'book' => 'Livro',
                        'podcast' => 'Podcast',
                        'video' => 'Video',
                        'article' => 'Artigo',
                    ])
                    ->required(),
                Textarea::make('description')
                    ->label('Descricao')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('url')
                    ->label('URL')
                    ->url()
                    ->maxLength(500),
                TextInput::make('thumbnail')
                    ->label('URL da Imagem')
                    ->url()
                    ->maxLength(500),
                Toggle::make('is_approved')
                    ->label('Aprovado')
                    ->helperText('Recursos nao aprovados nao aparecem na biblioteca publica.')
                    ->default(true),
            ]);
    }
}
