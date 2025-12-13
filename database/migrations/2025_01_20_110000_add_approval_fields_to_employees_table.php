<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalFieldsToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'approval_status')) {
                $table->string('approval_status')->default('pending')->after('created_by');
            }
            if (!Schema::hasColumn('employees', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('employees', 'approved_by')) {
                $table->integer('approved_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('employees', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_by');
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
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'approved_at', 'approved_by', 'rejection_reason']);
        });
    }
}

