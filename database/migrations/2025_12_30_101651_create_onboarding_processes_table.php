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
        Schema::create('onboarding_processes', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('stage')->default(1);
            $table->integer('order')->default(0);
            
            // Step 1: Employee Creation Verification
            $table->boolean('employee_created_verified')->default(false);
            $table->integer('employee_created_verified_by')->nullable();
            $table->timestamp('employee_created_verified_at')->nullable();
            
            // Step 2: Document Upload & Verification
            $table->boolean('document_upload_verified')->default(false);
            $table->integer('document_upload_verified_by')->nullable();
            $table->timestamp('document_upload_verified_at')->nullable();
            
            // Step 3: Employee Acknowledgement (Hard Copy)
            $table->boolean('employee_acknowledgement_received')->default(false);
            $table->integer('employee_acknowledgement_received_by')->nullable();
            $table->timestamp('employee_acknowledgement_received_at')->nullable();
            
            // Step 4: System & Access Provisioning (JSON)
            $table->text('system_access_checklist')->nullable();
            $table->string('system_access_status')->nullable(); // done, pending
            $table->integer('system_access_completed_by')->nullable();
            $table->timestamp('system_access_completed_at')->nullable();
            
            // Step 5: Asset Issuance (JSON)
            $table->text('asset_issuance_checklist')->nullable();
            $table->string('asset_issuance_status')->nullable(); // issued, not_issued
            $table->integer('asset_issuance_completed_by')->nullable();
            $table->timestamp('asset_issuance_completed_at')->nullable();
            
            // Step 6: Training, Policy & Agreement Acknowledgement
            $table->boolean('training_policy_acknowledgement')->default(false);
            $table->integer('training_policy_acknowledged_by')->nullable();
            $table->timestamp('training_policy_acknowledged_at')->nullable();
            
            // Step 7: Onboarding Completed
            $table->boolean('onboarding_completed')->default(false);
            $table->integer('onboarding_completed_by')->nullable();
            $table->timestamp('onboarding_completed_at')->nullable();
            
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_processes');
    }
};
