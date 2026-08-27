<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

// The Roles & Permissions screens (registered by the Althinect Filament
// plugin) ship with no authorization of their own — without this policy any
// authenticated panel user, Editor/Viewer included, could view and edit every
// role and permission. Filament's Resource::can() resolves through Laravel's
// standard Gate/Policy lookup, so registering this policy for Spatie's Role
// model is enough to lock the whole resource down to Admins.
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasRole('Admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasRole('Admin');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasRole('Admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }
}
