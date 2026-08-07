<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Single source of truth for which Filament resources get {action}_{resource}
     * permissions. Referenced by tests/Pest.php's createRolesAndPermissions() too,
     * so the two lists can't silently drift apart. Adding a resource here requires
     * shipping a migration that calls (new RoleSeeder())->run()) — see
     * database/migrations/2026_07_24_150000_sync_department_permissions.php —
     * otherwise the permissions never get created in production (deploy only runs
     * `migrate --force`, never seeders).
     */
    public const RESOURCES = [
        'asset', 'assignment', 'employee', 'license',
        'maintenance_record', 'asset_category', 'supplier', 'location',
        'user', 'department',
    ];

    public const ACTIONS = ['view_any', 'view', 'create', 'update', 'delete'];

    public const EXTRA_PERMISSIONS = ['import_asset', 'export_report'];

    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::RESOURCES as $resource) {
            foreach (self::ACTIONS as $action) {
                Permission::firstOrCreate(['name' => "{$action}_{$resource}"]);
            }
        }

        foreach (self::EXTRA_PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions(Permission::all());

        $editor = Role::firstOrCreate(['name' => 'Editor']);
        $editor->syncPermissions(
            Permission::whereNotIn('name', [
                ...array_map(fn ($r) => "delete_{$r}", self::RESOURCES),
                ...self::EXTRA_PERMISSIONS,
            ])->pluck('name')
        );

        $viewer = Role::firstOrCreate(['name' => 'Viewer']);
        $viewer->syncPermissions(
            Permission::whereIn('name', [
                ...array_map(fn ($r) => "view_any_{$r}", self::RESOURCES),
                ...array_map(fn ($r) => "view_{$r}", self::RESOURCES),
            ])->pluck('name')
        );

        $user = User::first();
        if ($user && ! $user->hasRole('Admin')) {
            $user->assignRole('Admin');
        }
    }
}
