<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('monthly_plan_cost');
            $table->string('hostname')->nullable()->after('ip_address');
            $table->string('mac_address')->nullable()->unique()->after('hostname');
            $table->string('vlan')->nullable()->after('mac_address');
            $table->string('device_role')->nullable()->after('vlan');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'hostname', 'mac_address', 'vlan', 'device_role']);
        });
    }
};
