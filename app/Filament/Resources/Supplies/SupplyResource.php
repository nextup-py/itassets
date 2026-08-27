<?php

namespace App\Filament\Resources\Supplies;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Shared\ActivityRelationManager;
use App\Filament\Resources\Supplies\Pages\CreateSupply;
use App\Filament\Resources\Supplies\Pages\EditSupply;
use App\Filament\Resources\Supplies\Pages\ListSupplies;
use App\Filament\Resources\Supplies\Pages\ViewSupply;
use App\Filament\Resources\Supplies\RelationManagers\AdjustmentsRelationManager;
use App\Filament\Resources\Supplies\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\Supplies\Schemas\SupplyForm;
use App\Filament\Resources\Supplies\Schemas\SupplyInfolist;
use App\Filament\Resources\Supplies\Tables\SuppliesTable;
use App\Models\Supply;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplyResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionName(): string
    {
        return 'supply';
    }

    protected static ?string $model = Supply::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Insumo';

    protected static ?string $pluralModelLabel = 'Insumos';

    protected static ?string $navigationLabel = 'Insumos';

    protected static \UnitEnum|string|null $navigationGroup = 'Inventario';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return SupplyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SupplyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuppliesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MovementsRelationManager::class,
            AdjustmentsRelationManager::class,
            ActivityRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSupplies::route('/'),
            'create' => CreateSupply::route('/create'),
            'view'   => ViewSupply::route('/{record}'),
            'edit'   => EditSupply::route('/{record}/edit'),
        ];
    }
}
