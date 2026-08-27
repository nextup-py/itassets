<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('reason'); // loss, damage, theft, other
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_adjustments');
    }
};
