<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRegularisation extends Model
{
    const STATUS_PENDING = 'Pending';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';

    const REASON_MISSED_PUNCH = 'Missed Punch';
    const REASON_TECHNICAL_ERROR = 'Technical Error';
    const REASON_OTHER = 'Other';

    protected $fillable = [
        'employee_id',
        'date',
        'punch_in_time',
        'punch_out_time',
        'reason',
        'remarks',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

