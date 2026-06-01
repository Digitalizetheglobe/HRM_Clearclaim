<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('late_mark_deductions', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->string('payment_month'); // e.g. "2026-06"
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->integer('created_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('late_mark_deductions');
    }
};
