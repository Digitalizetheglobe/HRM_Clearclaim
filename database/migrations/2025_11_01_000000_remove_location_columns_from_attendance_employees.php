<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveLocationColumnsFromAttendanceEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            // Check if columns exist before dropping them
            if (Schema::hasColumn('attendance_employees', 'clock_in_latitude')) {
                $table->dropColumn('clock_in_latitude');
            }
            if (Schema::hasColumn('attendance_employees', 'clock_in_longitude')) {
                $table->dropColumn('clock_in_longitude');
            }
            if (Schema::hasColumn('attendance_employees', 'clock_in_location')) {
                $table->dropColumn('clock_in_location');
            }
            if (Schema::hasColumn('attendance_employees', 'clock_out_latitude')) {
                $table->dropColumn('clock_out_latitude');
            }
            if (Schema::hasColumn('attendance_employees', 'clock_out_longitude')) {
                $table->dropColumn('clock_out_longitude');
            }
            if (Schema::hasColumn('attendance_employees', 'clock_out_location')) {
                $table->dropColumn('clock_out_location');
            }
            if (Schema::hasColumn('attendance_employees', 'clock_in_accuracy')) {
                $table->dropColumn('clock_in_accuracy');
            }
            if (Schema::hasColumn('attendance_employees', 'clock_out_accuracy')) {
                $table->dropColumn('clock_out_accuracy');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->decimal('clock_in_latitude', 10, 7)->nullable();
            $table->decimal('clock_in_longitude', 10, 7)->nullable();
            $table->text('clock_in_location')->nullable();
            $table->decimal('clock_out_latitude', 10, 7)->nullable();
            $table->decimal('clock_out_longitude', 10, 7)->nullable();
            $table->text('clock_out_location')->nullable();
            $table->decimal('clock_in_accuracy', 8, 2)->nullable();
            $table->decimal('clock_out_accuracy', 8, 2)->nullable();
        });
    }
}


