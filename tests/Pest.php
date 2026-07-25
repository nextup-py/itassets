<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
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
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    $resources = ['asset', 'assignment', 'employee', 'license',
        'maintenance_record', 'asset_category', 'supplier', 'location', 'user', 'department',
    ];
    $actions = ['view_any', 'view', 'create', 'update', 'delete'];
    $extraPermissions = ['import_asset', 'export_report'];

    foreach ($resources as $resource) {
        foreach ($actions as $action) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => "{$action}_{$resource}"]);
        }
    }
    foreach ($extraPermissions as $permission) {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
    }

    $admin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
    $admin->syncPermissions(\Spatie\Permission\Models\Permission::all());

    $editor = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Editor']);
    $editor->syncPermissions(
        \Spatie\Permission\Models\Permission::whereNotIn('name', [
            ...array_map(fn ($r) => "delete_{$r}", $resources),
            'export_report',
            'import_asset',
        ])->pluck('name')
    );

    $viewer = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Viewer']);
    $viewer->syncPermissions(
        \Spatie\Permission\Models\Permission::whereIn('name', [
            ...array_map(fn ($r) => "view_any_{$r}", $resources),
            ...array_map(fn ($r) => "view_{$r}", $resources),
        ])->pluck('name')
    );
}

function makeAdminUser(): \App\Models\User
{
    createRolesAndPermissions();

    return \App\Models\User::factory()->admin()->create([
        'email' => 'admin@test.com',
    ]);
}

function makeEditorUser(): \App\Models\User
{
    createRolesAndPermissions();

    return \App\Models\User::factory()->editor()->create([
        'email' => 'editor@test.com',
    ]);
}

function makeViewerUser(): \App\Models\User
{
    createRolesAndPermissions();

    return \App\Models\User::factory()->viewer()->create([
        'email' => 'viewer@test.com',
    ]);
}

function loginAsAdmin(): \App\Models\User
{
    $user = makeAdminUser();
    test()->actingAs($user);

    return $user;
}

function loginAsEditor(): \App\Models\User
{
    $user = makeEditorUser();
    test()->actingAs($user);

    return $user;
}

function loginAsViewer(): \App\Models\User
{
    $user = makeViewerUser();
    test()->actingAs($user);

    return $user;
}
