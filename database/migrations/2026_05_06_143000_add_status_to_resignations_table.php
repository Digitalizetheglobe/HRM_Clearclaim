<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToResignationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resignations', function (Blueprint $table) {
            if (!Schema::hasColumn('resignations', 'status')) {
                $table->string('status')->default('pending')->after('description');
            }
            if (!Schema::hasColumn('resignations', 'approved_by')) {
                $table->integer('approved_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('resignations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
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
        Schema::table('resignations', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_by', 'approved_at']);
        });
    }
}
