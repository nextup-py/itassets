<?php

namespace App\Filament\Resources\Locations;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Locations\Pages\CreateLocation;
use App\Filament\Resources\Locations\Pages\EditLocation;
use App\Filament\Resources\Locations\Pages\ListLocations;
use App\Filament\Resources\Locations\Pages\ViewLocation;
use App\Filament\Resources\Locations\Schemas\LocationForm;
use App\Filament\Resources\Locations\Schemas\LocationInfolist;
use App\Filament\Resources\Locations\Tables\LocationsTable;
use App\Filament\Resources\Shared\ActivityRelationManager;
use App\Models\Location;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LocationResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionName(): string
    {
        return 'location';
    }
    protected static ?string $model = Location::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Ubicación';

    protected static ?string $pluralModelLabel = 'Ubicaciones';

    protected static ?string $navigationLabel = 'Ubicaciones';

    protected static \UnitEnum|string|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return LocationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LocationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LocationsTable::configure($table);
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
            'index'  => ListLocations::route('/'),
            'create' => CreateLocation::route('/create'),
            'view'   => ViewLocation::route('/{record}'),
            'edit'   => EditLocation::route('/{record}/edit'),
        ];
    }
}
