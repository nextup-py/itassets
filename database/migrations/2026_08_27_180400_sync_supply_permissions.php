<?php

use Database\Seeders\RoleSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Deploys only run `php artisan migrate --force`, never seeders — so the
     * 'supply' / 'supply_adjustment' permissions added to RoleSeeder's
     * $resources array for the Insumos module never actually get
     * created/synced in production without this. RoleSeeder::run() is
     * idempotent, so re-running it here is safe.
     */
    public function up(): void
    {
        (new RoleSeeder)->run();
    }

    public function down(): void
    {
        // No-op: reverting already-granted permissions is not safe/desirable.
    }
};
