<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\Resignation;
use App\Models\Termination;
use App\Models\User;

class OffboardingProcess extends Model
{
    protected $fillable = [
        'employee_id',
        'resignation_id',
        'termination_id',
        'stage',
        'order',
        'manager_approved_by',
        'manager_comment',
        'manager_status',
        'manager_approved_at',
        'hr_approved_by',
        'last_working_day',
        'notice_period_days',
        'hr_status',
        'hr_approved_at',
        'access_removal_checklist',
        'asset_collection_checklist',
        'settlement_details',
        'settlement_status',
        'settlement_completed_by',
        'settlement_completed_at',
        'termination_completed_by',
        'termination_completed_at',
        'document_details',
        'document_status',
        'document_uploaded_by',
        'document_uploaded_at',
        'employee_feedback',
        'feedback_recorded_by',
        'feedback_recorded_at',
        'created_by',
    ];

    protected $casts = [
        'access_removal_checklist' => 'array',
        'asset_collection_checklist' => 'array',
        'settlement_details' => 'array',
        'document_details' => 'array',
        'last_working_day' => 'date',
        'manager_approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
        'settlement_completed_at' => 'datetime',
        'termination_completed_at' => 'datetime',
        'document_uploaded_at' => 'datetime',
        'feedback_recorded_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id')->with('user');
    }

    public function resignation()
    {
        return $this->belongsTo(Resignation::class, 'resignation_id');
    }

    public function termination()
    {
        return $this->belongsTo(Termination::class, 'termination_id');
    }

    public function managerApprovedBy()
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    public function hrApprovedBy()
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }

    public function settlementCompletedBy()
    {
        return $this->belongsTo(User::class, 'settlement_completed_by');
    }

    public function terminationCompletedBy()
    {
        return $this->belongsTo(User::class, 'termination_completed_by');
    }

    public function documentUploadedBy()
    {
        return $this->belongsTo(User::class, 'document_uploaded_by');
    }

    public function feedbackRecordedBy()
    {
        return $this->belongsTo(User::class, 'feedback_recorded_by');
    }

    public function currentStage()
    {
        return $this->belongsTo(OffboardingStage::class, 'stage');
    }
}

