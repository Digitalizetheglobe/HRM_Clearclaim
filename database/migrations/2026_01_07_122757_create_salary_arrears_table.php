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
        Schema::create('salary_arrears', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->date('pending_month'); // Month for which amount is pending
            $table->date('payment_month'); // Month in which it will be paid
            $table->decimal('amount', 15, 2);
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_arrears');
    }
};
