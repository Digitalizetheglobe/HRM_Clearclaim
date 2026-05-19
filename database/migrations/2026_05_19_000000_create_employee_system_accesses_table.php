<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_system_accesses', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->boolean('biometric')->default(false);
            $table->boolean('email')->default(false);
            $table->boolean('crm')->default(false);
            $table->boolean('whatsapp')->default(false);
            $table->boolean('internal_tools')->default(false);
            $table->boolean('other')->default(false);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_system_accesses');
    }
};
