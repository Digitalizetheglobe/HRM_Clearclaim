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
        Schema::table('ip_restricts', function (Blueprint $table) {
            $table->enum('type', ['local', 'public'])->default('public')->after('ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ip_restricts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
