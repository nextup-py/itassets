<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function createRolesAndPermissions(): void
{
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

    $resources = RoleSeeder::RESOURCES;
    $actions = RoleSeeder::ACTIONS;
    $extraPermissions = RoleSeeder::EXTRA_PERMISSIONS;

    // Keep this in sync with RoleSeeder::RESOURCES — see class doc there.
    foreach ($resources as $resource) {
        foreach ($actions as $action) {
            Permission::firstOrCreate(['name' => "{$action}_{$resource}"]);
        }
    }
    foreach ($extraPermissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    $admin = Role::firstOrCreate(['name' => 'Admin']);
    $admin->syncPermissions(Permission::all());

    $editor = Role::firstOrCreate(['name' => 'Editor']);
    $editor->syncPermissions(
        Permission::whereNotIn('name', [
            ...array_map(fn ($r) => "delete_{$r}", $resources),
            ...$extraPermissions,
        ])->pluck('name')
    );

    $viewer = Role::firstOrCreate(['name' => 'Viewer']);
    $viewer->syncPermissions(
        Permission::whereIn('name', [
            ...array_map(fn ($r) => "view_any_{$r}", $resources),
            ...array_map(fn ($r) => "view_{$r}", $resources),
        ])->pluck('name')
    );
}

function makeAdminUser(): User
{
    createRolesAndPermissions();

    return User::factory()->admin()->create([
        'email' => 'admin@test.com',
    ]);
}

function makeEditorUser(): User
{
    createRolesAndPermissions();

    return User::factory()->editor()->create([
        'email' => 'editor@test.com',
    ]);
}

function makeViewerUser(): User
{
    createRolesAndPermissions();

    return User::factory()->viewer()->create([
        'email' => 'viewer@test.com',
    ]);
}

function loginAsAdmin(): User
{
    $user = makeAdminUser();
    test()->actingAs($user);

    return $user;
}

function loginAsEditor(): User
{
    $user = makeEditorUser();
    test()->actingAs($user);

    return $user;
}

function loginAsViewer(): User
{
    $user = makeViewerUser();
    test()->actingAs($user);

    return $user;
}
