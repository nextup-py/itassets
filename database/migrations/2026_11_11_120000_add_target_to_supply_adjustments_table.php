<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_adjustments', function (Blueprint $table) {
            // Which bucket this adjustment decrements: 'available' (the existing
            // "Baja manual" flow) or 'damaged' (new "Disponer de dañados" flow,
            // for writing off units that reemplazo movements already piled into
            // quantity_damaged with no way back out otherwise).
            $table->string('target')->default('available')->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('supply_adjustments', function (Blueprint $table) {
            $table->dropColumn('target');
        });
    }
};
