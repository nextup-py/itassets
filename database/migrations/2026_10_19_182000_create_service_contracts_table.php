<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contract_type'); // maintenance, support, extended_warranty, consulting
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('currency')->nullable();
            $table->string('billing_cycle')->nullable(); // one_time, monthly, annual
            $table->boolean('auto_renew')->default(false);
            $table->string('sla')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_contracts');
    }
};
