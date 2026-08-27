<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_id')->constrained()->restrictOnDelete();
            $table->string('type'); // entrega, reemplazo
            $table->unsignedInteger('quantity');
            $table->foreignId('employee_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->restrictOnDelete();
            // Only set for 'reemplazo': the supply that receives the damaged/returned unit(s).
            // Defaults to the same supply as $supply_id when left blank.
            $table->foreignId('received_supply_id')->nullable()->constrained('supplies')->restrictOnDelete();
            $table->date('performed_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_movements');
    }
};
