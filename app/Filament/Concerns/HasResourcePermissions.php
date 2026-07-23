<?php

namespace App\Filament\Concerns;

use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasResourcePermissions
{
    abstract protected static function getPermissionName(): string;

    protected static function authorizeAbility(string $ability): Response
    {
        return Auth::user()?->can($ability)
            ? Response::allow()
            : Response::deny();
    }

    // Overriding the get*AuthorizationResponse() methods (rather than the
    // canX() convenience methods) is what matters here: Filament's action
    // wiring (header/bulk Delete, etc.) authorizes via these methods, not
    // via canX(), which only page-mount checks call directly.
    public static function getViewAnyAuthorizationResponse(): Response
    {
        return static::authorizeAbility('view_any_' . static::getPermissionName());
    }

    public static function getViewAuthorizationResponse(Model $record): Response
    {
        return static::authorizeAbility('view_' . static::getPermissionName());
    }

    public static function getCreateAuthorizationResponse(): Response
    {
        return static::authorizeAbility('create_' . static::getPermissionName());
    }

    public static function getEditAuthorizationResponse(Model $record): Response
    {
        return static::authorizeAbility('update_' . static::getPermissionName());
    }

    public static function getDeleteAuthorizationResponse(Model $record): Response
    {
        return static::authorizeAbility('delete_' . static::getPermissionName());
    }

    public static function getDeleteAnyAuthorizationResponse(): Response
    {
        return static::authorizeAbility('delete_' . static::getPermissionName());
    }
}
