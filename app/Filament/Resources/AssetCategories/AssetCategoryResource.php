<?php

namespace App\Filament\Resources\AssetCategories;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\AssetCategories\Pages\CreateAssetCategory;
use App\Filament\Resources\AssetCategories\Pages\EditAssetCategory;
use App\Filament\Resources\AssetCategories\Pages\ListAssetCategories;
use App\Filament\Resources\AssetCategories\Pages\ViewAssetCategory;
use App\Filament\Resources\AssetCategories\Schemas\AssetCategoryForm;
use App\Filament\Resources\Shared\ActivityRelationManager;
use App\Filament\Resources\AssetCategories\Schemas\AssetCategoryInfolist;
use App\Filament\Resources\AssetCategories\Tables\AssetCategoriesTable;
use App\Models\AssetCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssetCategoryResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionName(): string
    {
        return 'asset_category';
    }
    protected static ?string $model = AssetCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Categoría de Activo';

    protected static ?string $pluralModelLabel = 'Categorías de Activos';

    protected static ?string $navigationLabel = 'Categorías';

    protected static \UnitEnum|string|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AssetCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssetCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ActivityRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAssetCategories::route('/'),
            'create' => CreateAssetCategory::route('/create'),
            'view'   => ViewAssetCategory::route('/{record}'),
            'edit'   => EditAssetCategory::route('/{record}/edit'),
        ];
    }
}
