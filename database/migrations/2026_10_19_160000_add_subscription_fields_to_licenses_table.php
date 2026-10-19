<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('billing_cycle')->nullable()->after('license_type');
            $table->boolean('auto_renew')->default(false)->after('billing_cycle');
            $table->unsignedInteger('total_seats')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'auto_renew']);
        });
    }
};
