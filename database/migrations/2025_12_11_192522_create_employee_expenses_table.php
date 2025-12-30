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
        Schema::create('employee_expenses', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('category_id');
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->json('receipt_file')->nullable(); // JSON array for multiple files
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', [
                'pending_hr',
                'rejected_hr',
                'pending_finance',
                'paid'
            ])->default('pending_hr');
            $table->integer('manager_id')->nullable();
            $table->text('manager_remark')->nullable();
            $table->timestamp('manager_approved_at')->nullable();
            $table->integer('hr_id')->nullable();
            $table->text('hr_remark')->nullable();
            $table->timestamp('hr_approved_at')->nullable();
            $table->integer('finance_id')->nullable();
            $table->date('paid_date')->nullable();
            $table->enum('payment_mode', ['bank', 'upi', 'cash'])->nullable();
            $table->string('payment_proof')->nullable();
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_expenses');
    }
};
