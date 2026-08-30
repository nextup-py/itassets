<?php

use Database\Seeders\RoleSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Deploys only run `php artisan migrate --force`, never seeders — so the
     * 'import_employee'/'import_supply' permissions added to RoleSeeder's
     * EXTRA_PERMISSIONS need a migration to actually reach production.
     * RoleSeeder::run() is idempotent, so re-running it here is safe.
     */
    public function up(): void
    {
        (new RoleSeeder())->run();
    }

    public function down(): void
    {
        // No-op: reverting already-granted permissions is not safe/desirable.
    }
};
