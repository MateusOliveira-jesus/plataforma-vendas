<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                
                
                        Section::make('Informações Básicas')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true),

                             
                            TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->disabled(fn ($operation) => $operation === 'edit'),
                                Textarea::make('description')
                                    ->label('Descrição')
                                    ->rows(3)
                                    ->maxLength(1000)
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)->columnSpanFull(),

                        Section::make('Configurações')
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'active' => 'Ativo',
                                        'inactive' => 'Inativo',
                                        'archived' => 'Arquivado',
                                    ])
                                    ->default('active')
                                    ->required(),

                                Toggle::make('is_featured')
                                    ->label('Destaque')
                                    ->default(false),

                                Select::make('display_layout')
                                    ->label('Layout de Exibição')
                                    ->options([
                                        'grid' => 'Grid',
                                        'list' => 'Lista',
                                        'carousel' => 'Carrossel',
                                    ])
                                    ->default('grid'),

                                TextInput::make('sort_order')
                                    ->label('Ordem')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ])
                            ->columns(1)->columnSpanFull(),

                        // Section::make('Imagens')
                        //     ->schema([
                        //         FileUpload::make('image')
                        //             ->label('Imagem da Categoria')
                        //             ->image()
                        //             ->directory('categories')
                        //             ->imageResizeMode('cover')
                        //             ->imageCropAspectRatio('1:1')
                        //             ->imageResizeTargetWidth('400')
                        //             ->imageResizeTargetHeight('400')
                        //             ->maxSize(2048)
                        //             ->helperText('Recomendado: 400x400px'),

                        //         FileUpload::make('banner_image')
                        //             ->label('Banner')
                        //             ->image()
                        //             ->directory('categories/banners')
                        //             ->imageResizeMode('cover')
                        //             ->imageCropAspectRatio('16:9')
                        //             ->imageResizeTargetWidth('1200')
                        //             ->imageResizeTargetHeight('400')
                        //             ->maxSize(2048)
                        //             ->helperText('Recomendado: 1200x400px'),

                        //         FileUpload::make('icon')
                        //             ->label('Ícone')
                        //             ->image()
                        //             ->directory('categories/icons')
                        //             ->imageResizeMode('contain')
                        //             ->imageCropAspectRatio('1:1')
                        //             ->imageResizeTargetWidth('100')
                        //             ->imageResizeTargetHeight('100')
                        //             ->maxSize(512)
                        //             ->helperText('Recomendado: 100x100px'),
                        //     ])
                        //     ->columns(1)->columnSpanFull(),

                        Section::make('SEO')
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label('Meta Título')
                                    ->maxLength(60)
                                    ->helperText('Máximo 60 caracteres'),

                                Textarea::make('meta_description')
                                    ->label('Meta Descrição')
                                    ->rows(2)
                                    ->maxLength(160)
                                    ->helperText('Máximo 160 caracteres'),

                                TagsInput::make('meta_keywords')
                                    ->label('Palavras-chave')
                                    ->separator(','),

                                KeyValue::make('attributes')
                                    ->label('Atributos Adicionais')
                                    ->keyLabel('Chave')
                                    ->valueLabel('Valor')
                                    ->addable(true)
                                
                                    ->reorderable(true),

                                KeyValue::make('filters')
                                    ->label('Filtros')
                                    ->keyLabel('Filtro')
                                    ->valueLabel('Opções (separadas por vírgula)')
                                    ->addable(true)
                                
                                    ->reorderable(true),
                            ])
                            ->columns(1)->columnSpanFull(),
                            ]);
    }
}
