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
        if (!Schema::hasColumn('leaves', 'leave_duration')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->string('leave_duration')->default('Full Day')->after('total_leave_days'); // 'Full Day' or 'Half Day'
            });
        }
        if (!Schema::hasColumn('leaves', 'leave_session')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->string('leave_session')->nullable()->after('leave_duration'); // 'First Half' or 'Second Half' (only for Half Day)
            });
        }
        if (!Schema::hasColumn('leaves', 'is_paid')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->boolean('is_paid')->default(true)->after('leave_session'); // Track if it's paid leave
            });
        }
        if (!Schema::hasColumn('leaves', 'is_lop')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->boolean('is_lop')->default(false)->after('is_paid'); // Track if it's Loss of Pay
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            if (Schema::hasColumn('leaves', 'leave_duration')) {
                $table->dropColumn('leave_duration');
            }
            if (Schema::hasColumn('leaves', 'leave_session')) {
                $table->dropColumn('leave_session');
            }
            if (Schema::hasColumn('leaves', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
            if (Schema::hasColumn('leaves', 'is_lop')) {
                $table->dropColumn('is_lop');
            }
        });
    }
};
