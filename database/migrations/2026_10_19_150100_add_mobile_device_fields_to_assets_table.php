<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('imei')->nullable()->unique()->after('serial_number');
            $table->string('imei2')->nullable()->unique()->after('imei');
            $table->string('phone_number')->nullable()->after('imei2');
            $table->string('carrier')->nullable()->after('phone_number');
            $table->string('plan_type')->nullable()->after('carrier'); // prepaid, postpaid
            $table->decimal('monthly_plan_cost', 10, 2)->nullable()->after('plan_type');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['imei', 'imei2', 'phone_number', 'carrier', 'plan_type', 'monthly_plan_cost']);
        });
    }
};
