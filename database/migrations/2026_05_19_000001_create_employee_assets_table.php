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
        Schema::create('employee_assets', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->boolean('laptop')->default(false);
            $table->boolean('chargers')->default(false);
            $table->boolean('mobile')->default(false);
            $table->boolean('mouse')->default(false);
            $table->boolean('sim_card')->default(false);
            $table->boolean('id_card')->default(false);
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
        Schema::dropIfExists('employee_assets');
    }
};
