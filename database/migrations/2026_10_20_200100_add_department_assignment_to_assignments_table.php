<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->change();
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->after('employee_id')->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropForeign(['employee_id']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable(false)->change();
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->restrictOnDelete();
        });
    }
};
