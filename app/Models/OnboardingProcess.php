<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\User;

class OnboardingProcess extends Model
{
    protected $fillable = [
        'employee_id',
        'stage',
        'order',
        'employee_created_verified',
        'employee_created_verified_by',
        'employee_created_verified_at',
        'document_upload_verified',
        'document_upload_verified_by',
        'document_upload_verified_at',
        'employee_acknowledgement_received',
        'employee_acknowledgement_received_by',
        'employee_acknowledgement_received_at',
        'system_access_checklist',
        'system_access_status',
        'system_access_completed_by',
        'system_access_completed_at',
        'asset_issuance_checklist',
        'asset_issuance_status',
        'asset_issuance_completed_by',
        'asset_issuance_completed_at',
        'training_policy_acknowledgement',
        'training_policy_acknowledged_by',
        'training_policy_acknowledged_at',
        'onboarding_completed',
        'onboarding_completed_by',
        'onboarding_completed_at',
        'created_by',
    ];

    protected $casts = [
        'system_access_checklist' => 'array',
        'asset_issuance_checklist' => 'array',
        'employee_created_verified_at' => 'datetime',
        'document_upload_verified_at' => 'datetime',
        'employee_acknowledgement_received_at' => 'datetime',
        'system_access_completed_at' => 'datetime',
        'asset_issuance_completed_at' => 'datetime',
        'training_policy_acknowledged_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id')->with('user');
    }

    public function employeeCreatedVerifiedBy()
    {
        return $this->belongsTo(User::class, 'employee_created_verified_by');
    }

    public function documentUploadVerifiedBy()
    {
        return $this->belongsTo(User::class, 'document_upload_verified_by');
    }

    public function employeeAcknowledgementReceivedBy()
    {
        return $this->belongsTo(User::class, 'employee_acknowledgement_received_by');
    }

    public function systemAccessCompletedBy()
    {
        return $this->belongsTo(User::class, 'system_access_completed_by');
    }

    public function assetIssuanceCompletedBy()
    {
        return $this->belongsTo(User::class, 'asset_issuance_completed_by');
    }

    public function trainingPolicyAcknowledgedBy()
    {
        return $this->belongsTo(User::class, 'training_policy_acknowledged_by');
    }

    public function onboardingCompletedBy()
    {
        return $this->belongsTo(User::class, 'onboarding_completed_by');
    }

    public function currentStage()
    {
        return $this->belongsTo(OnboardingStage::class, 'stage');
    }
}








