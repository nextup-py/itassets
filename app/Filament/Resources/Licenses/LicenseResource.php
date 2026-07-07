<?php

namespace App\Filament\Resources\Licenses;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Licenses\Pages\CreateLicense;
use App\Filament\Resources\Licenses\Pages\EditLicense;
use App\Filament\Resources\Licenses\Pages\ListLicenses;
use App\Filament\Resources\Licenses\Pages\ViewLicense;
use App\Filament\Resources\Licenses\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\Licenses\Schemas\LicenseForm;
use App\Filament\Resources\Shared\ActivityRelationManager;
use App\Filament\Resources\Licenses\Schemas\LicenseInfolist;
use App\Filament\Resources\Licenses\Tables\LicensesTable;
use App\Models\License;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LicenseResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionName(): string
    {
        return 'license';
    }
    protected static ?string $model = License::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $recordTitleAttribute = 'product_name';

    protected static ?string $modelLabel = 'Licencia';

    protected static ?string $pluralModelLabel = 'Licencias';

    protected static ?string $navigationLabel = 'Licencias';

    protected static \UnitEnum|string|null $navigationGroup = 'Licencias';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return LicenseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LicenseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LicensesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AssignmentsRelationManager::class,
            ActivityRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListLicenses::route('/'),
            'create' => CreateLicense::route('/create'),
            'view'   => ViewLicense::route('/{record}'),
            'edit'   => EditLicense::route('/{record}/edit'),
        ];
    }
}
