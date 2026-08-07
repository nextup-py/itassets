<?php

use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| RoleSeeder <-> sync-migration drift guard
|--------------------------------------------------------------------------
|
| Deploys only ever run `php artisan migrate --force` — they never run
| seeders (see CLAUDE.md / docs/ARCHITECTURE.md). The only way a resource
| added to RoleSeeder::RESOURCES actually reaches the `permissions` table
| and the Admin/Editor/Viewer roles in production is if a migration calls
| (new RoleSeeder())->run() *after* the resource was added — see
| database/migrations/2026_07_24_150000_sync_department_permissions.php.
|
| Both tests below deliberately do NOT call the test-only
| createRolesAndPermissions() helper. RefreshDatabase only runs migrations,
| so relying purely on that setup mirrors production exactly: if a future
| PR adds a resource to RoleSeeder::RESOURCES without shipping a matching
| sync migration, the permissions/role assignments will be missing here
| too, and these tests fail instead of silently repeating the
| Departments-resource-invisible-to-everyone incident.
|
*/

it('has every RoleSeeder permission already present after running only migrations', function () {
    $expected = [];

    foreach (RoleSeeder::RESOURCES as $resource) {
        foreach (RoleSeeder::ACTIONS as $action) {
            $expected[] = "{$action}_{$resource}";
        }
    }

    array_push($expected, ...RoleSeeder::EXTRA_PERMISSIONS);

    $missing = array_diff($expected, Permission::pluck('name')->all());

    expect($missing)->toBe([]);
});

it('has Admin/Editor/Viewer already synced to the right permissions after running only migrations', function () {
    $admin = Role::where('name', 'Admin')->first();
    $editor = Role::where('name', 'Editor')->first();
    $viewer = Role::where('name', 'Viewer')->first();

    expect($admin)->not->toBeNull()
        ->and($editor)->not->toBeNull()
        ->and($viewer)->not->toBeNull();

    $allPermissionNames = Permission::pluck('name')->sort()->values()->all();
    $adminPermissionNames = $admin->permissions->pluck('name')->sort()->values()->all();

    expect($adminPermissionNames)->toBe($allPermissionNames);

    $editorPermissionNames = $editor->permissions->pluck('name')->all();
    $viewerPermissionNames = $viewer->permissions->pluck('name')->all();

    foreach (RoleSeeder::RESOURCES as $resource) {
        expect($editorPermissionNames)->not->toContain("delete_{$resource}")
            ->and($viewerPermissionNames)->not->toContain("create_{$resource}")
            ->and($viewerPermissionNames)->toContain("view_any_{$resource}");
    }

    foreach (RoleSeeder::EXTRA_PERMISSIONS as $permission) {
        expect($editorPermissionNames)->not->toContain($permission);
    }
});
