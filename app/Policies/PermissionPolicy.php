<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

// See RolePolicy — same rationale, for the Permissions screen.
class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->hasRole('Admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->hasRole('Admin');
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasRole('Admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }
}
