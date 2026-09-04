<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hrm_action_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->index();
            $table->string('module', 64)->index();
            $table->string('action', 64)->index();
            $table->text('description');
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('actor_name')->nullable();
            $table->string('actor_type', 64)->nullable();
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->string('employee_name')->nullable();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hrm_action_logs');
    }
};
