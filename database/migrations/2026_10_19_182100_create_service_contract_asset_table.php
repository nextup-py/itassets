<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_contract_asset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['service_contract_id', 'asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_contract_asset');
    }
};
