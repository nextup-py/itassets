<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_record_supply', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity_used');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_record_supply');
    }
};
