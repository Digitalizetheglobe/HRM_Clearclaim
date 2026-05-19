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
        Schema::table('holidays', function (Blueprint $table) {
            if (Schema::hasColumn('holidays', 'end_date')) {
                $table->dropColumn('end_date');
            }
            if (Schema::hasColumn('holidays', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (!Schema::hasColumn('holidays', 'date')) {
                $table->date('date')->nullable();
            }
            if (!Schema::hasColumn('holidays', 'day')) {
                $table->string('day')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            if (Schema::hasColumn('holidays', 'date')) {
                $table->dropColumn('date');
            }
            if (Schema::hasColumn('holidays', 'day')) {
                $table->dropColumn('day');
            }
            if (!Schema::hasColumn('holidays', 'start_date')) {
                $table->date('start_date')->nullable();
            }
            if (!Schema::hasColumn('holidays', 'end_date')) {
                $table->date('end_date')->nullable();
            }
        });
    }
};
