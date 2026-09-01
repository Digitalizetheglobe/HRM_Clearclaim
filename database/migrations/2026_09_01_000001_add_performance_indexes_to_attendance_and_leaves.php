<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->index(['employee_id', 'date'], 'attendance_employees_employee_date_index');
            $table->index('date', 'attendance_employees_date_index');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->index(['employee_id', 'status', 'start_date', 'end_date'], 'leaves_employee_status_dates_index');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropIndex('attendance_employees_employee_date_index');
            $table->dropIndex('attendance_employees_date_index');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->dropIndex('leaves_employee_status_dates_index');
        });
    }
};
