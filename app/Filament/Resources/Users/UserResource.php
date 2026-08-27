<?php

namespace App\Filament\Resources\Users;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Shared\ActivityRelationManager;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionName(): string
    {
        return 'user';
    }

    // "Administración" is Admin-only: Editor/Viewer must never see or reach
    // this resource, regardless of their {action}_user permission grants.
    // Overriding authorizeAbility() (rather than the get*AuthorizationResponse
    // methods individually) keeps this one extra check in a single place
    // while still going through every check the trait wires up.
    protected static function authorizeAbility(string $ability): Response
    {
        return Auth::user()?->hasRole('Admin') && Auth::user()->can($ability)
            ? Response::allow()
            : Response::deny();
    }

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static \UnitEnum|string|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
