<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
             ->columns([
                ImageColumn::make('image')
                    ->label('Imagem')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-category.png')),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.name')
                    ->label('Categoria Pai')
                    ->sortable()
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('products_count')
                    ->label('Produtos')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_featured')
                    ->label('Destaque')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'archived' => 'Arquivado',
                    ])
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Ordem')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}
