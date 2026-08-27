<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('asset_category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity_available')->default(0);
            $table->unsignedInteger('quantity_damaged')->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
