<?php

namespace App\Filament\Resources\ServiceContracts;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\ServiceContracts\Pages\CreateServiceContract;
use App\Filament\Resources\ServiceContracts\Pages\EditServiceContract;
use App\Filament\Resources\ServiceContracts\Pages\ListServiceContracts;
use App\Filament\Resources\ServiceContracts\Pages\ViewServiceContract;
use App\Filament\Resources\ServiceContracts\Schemas\ServiceContractForm;
use App\Filament\Resources\ServiceContracts\Schemas\ServiceContractInfolist;
use App\Filament\Resources\ServiceContracts\Tables\ServiceContractsTable;
use App\Filament\Resources\Shared\ActivityRelationManager;
use App\Models\ServiceContract;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceContractResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionName(): string
    {
        return 'service_contract';
    }

    protected static ?string $model = ServiceContract::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Contrato de servicio';

    protected static ?string $pluralModelLabel = 'Contratos de Servicio';

    protected static ?string $navigationLabel = 'Contratos de Servicio';

    protected static \UnitEnum|string|null $navigationGroup = 'Inventario';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return ServiceContractForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServiceContractInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceContractsTable::configure($table);
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
            'index'  => ListServiceContracts::route('/'),
            'create' => CreateServiceContract::route('/create'),
            'view'   => ViewServiceContract::route('/{record}'),
            'edit'   => EditServiceContract::route('/{record}/edit'),
        ];
    }
}
