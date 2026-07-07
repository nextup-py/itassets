<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'assets',
        'asset_categories',
        'assignments',
        'employees',
        'licenses',
        'license_assignments',
        'locations',
        'maintenance_records',
        'suppliers',
        'settings',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->foreignId('updated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropForeign(['updated_by']);
                $table->dropColumn(['created_by', 'updated_by']);
            });
        }
    }
};
