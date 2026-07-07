<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $resources = [
            'asset', 'assignment', 'employee', 'license',
            'maintenance_record', 'asset_category', 'supplier', 'location',
            'user',
        ];

        $actions = ['view_any', 'view', 'create', 'update', 'delete'];

        $extraPermissions = ['import_asset', 'export_report'];

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
                'export_report',
            ])->pluck('name')
        );

        $viewer = Role::firstOrCreate(['name' => 'Viewer']);
        $viewer->syncPermissions(
            Permission::whereIn('name', [
                ...array_map(fn ($r) => "view_any_{$r}", $resources),
                ...array_map(fn ($r) => "view_{$r}", $resources),
            ])->pluck('name')
        );

        $user = User::first();
        if ($user && ! $user->hasRole('Admin')) {
            $user->assignRole('Admin');
        }
    }
}
