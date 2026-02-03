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
        Schema::table('resignations', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('description');
            $table->integer('approved_by')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resignations', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_by', 'approved_at']);
        });
    }
};
