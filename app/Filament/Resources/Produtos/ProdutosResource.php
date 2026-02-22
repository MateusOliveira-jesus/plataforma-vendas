<?php

namespace App\Filament\Resources\Produtos;

use App\Filament\Resources\Produtos\Pages\CreateProdutos;
use App\Filament\Resources\Produtos\Pages\EditProdutos;
use App\Filament\Resources\Produtos\Pages\ListProdutos;
use App\Filament\Resources\Produtos\Pages\ViewProdutos;
use App\Filament\Resources\Produtos\Schemas\ProdutosForm;
use App\Filament\Resources\Produtos\Schemas\ProdutosInfolist;
use App\Filament\Resources\Produtos\Tables\ProdutosTable;
use App\Models\Produtos;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdutosResource extends Resource
{
    protected static ?string $model = Produtos::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Produtos';

    public static function form(Schema $schema): Schema
    {
        return ProdutosForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProdutosInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProdutosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProdutos::route('/'),
            'create' => CreateProdutos::route('/create'),
            'view' => ViewProdutos::route('/{record}'),
            'edit' => EditProdutos::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
