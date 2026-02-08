<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Imagem')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-category.png')),

                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('parent.name')
                    ->label('Categoria Pai')
                    ->sortable()
                    ->searchable()
                    ->placeholder('—'),

                \Filament\Tables\Columns\TextColumn::make('products_count')
                    ->label('Produtos')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                \Filament\Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destaque')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                \Filament\Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'archived' => 'Arquivado',
                    ])
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordem')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
