<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffboardingProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offboarding_processes', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('resignation_id')->nullable();
            $table->integer('termination_id')->nullable();
            $table->integer('stage')->default(1);
            $table->integer('order')->default(0);
            
            // Step 2: Manager Approval
            $table->integer('manager_approved_by')->nullable();
            $table->text('manager_comment')->nullable();
            $table->string('manager_status')->nullable(); // approved, rejected
            $table->timestamp('manager_approved_at')->nullable();
            
            // Step 3: HR Approval & Notice Period
            $table->integer('hr_approved_by')->nullable();
            $table->date('last_working_day')->nullable();
            $table->integer('notice_period_days')->nullable();
            $table->string('hr_status')->nullable(); // approved, pending
            $table->timestamp('hr_approved_at')->nullable();
            
            // Step 4: Access Removal Checklist (JSON)
            $table->text('access_removal_checklist')->nullable();
            
            // Step 5: Asset Collection Checklist (JSON)
            $table->text('asset_collection_checklist')->nullable();
            
            // Step 6: Full & Final Settlement
            $table->text('settlement_details')->nullable();
            $table->string('settlement_status')->nullable(); // completed, pending
            $table->integer('settlement_completed_by')->nullable();
            $table->timestamp('settlement_completed_at')->nullable();
            
            // Step 7: Relieving & Experience Letter
            $table->integer('termination_completed_by')->nullable();
            $table->timestamp('termination_completed_at')->nullable();
            
            // Step 8: HR Uploads / Downloads
            $table->text('document_details')->nullable();
            $table->string('document_status')->nullable(); // uploaded, pending
            $table->integer('document_uploaded_by')->nullable();
            $table->timestamp('document_uploaded_at')->nullable();
            
            // Step 9: HR Records Feedback
            $table->text('employee_feedback')->nullable();
            $table->integer('feedback_recorded_by')->nullable();
            $table->timestamp('feedback_recorded_at')->nullable();
            
            $table->integer('created_by');
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
        Schema::dropIfExists('offboarding_processes');
    }
}





