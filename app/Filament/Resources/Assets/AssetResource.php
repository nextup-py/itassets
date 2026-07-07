<?php

namespace App\Filament\Resources\Assets;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Assets\Pages\CreateAsset;
use App\Filament\Resources\Assets\Pages\EditAsset;
use App\Filament\Resources\Assets\Pages\ListAssets;
use App\Filament\Resources\Assets\Pages\ViewAsset;
use App\Filament\Resources\Assets\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\Assets\RelationManagers\MaintenanceRecordsRelationManager;
use App\Filament\Resources\Shared\ActivityRelationManager;
use App\Filament\Resources\Assets\Schemas\AssetForm;
use App\Filament\Resources\Assets\Schemas\AssetInfolist;
use App\Filament\Resources\Assets\Tables\AssetsTable;
use App\Models\Asset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssetResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionName(): string
    {
        return 'asset';
    }
    protected static ?string $model = Asset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Activo';

    protected static ?string $pluralModelLabel = 'Activos';

    protected static ?string $navigationLabel = 'Activos';

    protected static \UnitEnum|string|null $navigationGroup = 'Inventario';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AssetForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssetInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AssignmentsRelationManager::class,
            MaintenanceRecordsRelationManager::class,
            ActivityRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAssets::route('/'),
            'create' => CreateAsset::route('/create'),
            'view'   => ViewAsset::route('/{record}'),
            'edit'   => EditAsset::route('/{record}/edit'),
        ];
    }
}
