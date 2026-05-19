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
        if (!Schema::hasColumn('resignations', 'status')) {
            Schema::table('resignations', function (Blueprint $table) {
                $table->string('status')->default('pending')->after('description');
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
        Schema::table('resignations', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
    }
}
