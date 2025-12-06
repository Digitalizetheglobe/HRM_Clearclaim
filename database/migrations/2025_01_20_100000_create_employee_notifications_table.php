<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('employee_id');
            $table->string('type'); // 'approved' or 'rejected'
            $table->text('message');
            $table->text('rejection_reason')->nullable();
            $table->integer('created_by')->nullable(); // User who approved/rejected
            $table->boolean('seen')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_notifications');
    }
}

