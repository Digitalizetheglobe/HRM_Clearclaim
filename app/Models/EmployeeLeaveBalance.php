<?php

// app/Models/EmployeeLeaveBalance.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'month',
        'allocated_days',
        'used_days',
        'carry_forward_days'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    // Helper method to get available days
    public function getAvailableDaysAttribute()
    {
        return ($this->allocated_days + $this->carry_forward_days) - $this->used_days;
    }
}