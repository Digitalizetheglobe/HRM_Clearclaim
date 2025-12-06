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
        // Drop comp-off related tables
        Schema::dropIfExists('comp_offs');
        Schema::dropIfExists('comp_off_leaves');
        Schema::dropIfExists('comp_off_leave_logs');
        
        // Remove week_off_day column from employees table
        if (Schema::hasColumn('employees', 'week_off_day')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('week_off_day');
            });
        }
        
        // Remove comp_off_enabled column from employees table if it exists
        if (Schema::hasColumn('employees', 'comp_off_enabled')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('comp_off_enabled');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Recreate week_off_day column in employees table
        if (!Schema::hasColumn('employees', 'week_off_day')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('week_off_day')->nullable()->after('emergency_number');
            });
        }
        
        // Recreate comp_off_enabled column in employees table
        if (!Schema::hasColumn('employees', 'comp_off_enabled')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->boolean('comp_off_enabled')->default(0)->after('week_off_day');
            });
        }
        
        // Recreate comp_off_leaves table
        if (!Schema::hasTable('comp_off_leaves')) {
            Schema::create('comp_off_leaves', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employees_id');
                $table->date('comp_off_date');
                $table->decimal('comp_off_data', 8, 2)->default(1.0);
                $table->timestamps();
            });
        }
        
        // Recreate comp_off_leave_logs table
        if (!Schema::hasTable('comp_off_leave_logs')) {
            Schema::create('comp_off_leave_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employees_id');
                $table->date('log_date');
                $table->string('action');
                $table->text('details')->nullable();
                $table->timestamps();
            });
        }
        
        // Recreate comp_offs table
        if (!Schema::hasTable('comp_offs')) {
            Schema::create('comp_offs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->date('earned_date');
                $table->date('expiry_date')->nullable();
                $table->boolean('is_used')->default(false);
                $table->timestamps();
            });
        }
    }
};
