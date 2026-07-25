<?php

use Database\Seeders\RoleSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * RoleSeeder never excluded 'import_asset' from Editor's synced
     * permissions, contradicting CLAUDE.md's documented model ("Editor: CRUD
     * except delete + import assets") since the roles/permissions feature was
     * first introduced. Deploys only run `php artisan migrate --force`, never
     * seeders, so fixing RoleSeeder's exclusion list alone has no effect in
     * production without re-running it here. RoleSeeder::run() is idempotent
     * (declarative syncPermissions), so this is safe to re-run.
     */
    public function up(): void
    {
        (new RoleSeeder())->run();
    }

    public function down(): void
    {
        // No-op: not safe to blindly re-grant a permission that was revoked.
    }
};
