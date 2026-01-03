<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEmployee extends Model
{

    const STATUS_PRESENT = 'Present';
    const STATUS_HALF_DAY = 'Half Day';
    const STATUS_ABSENT = 'Absent';
    const STATUS_SINGLE_PUNCH = 'Single Punch In';
    const REQUIRED_WORKING_HOURS = 8.5; // 8 hours 30 minutes in decimal (for full day)
    const HALF_DAY_HOURS_THRESHOLD = 4.5; // 4 hours 30 minutes for half day
    const LATE_MARK_TIME = '10:15:00'; // Punch-in after this time is considered late
    const MAX_LATE_MARKS_PER_MONTH = 3; // Maximum allowed late marks per month


    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'late',
        'early_leaving',
        'overtime',
        'total_rest',
        'created_by',
    ];

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'employee_id');
    }

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }
    
    
}
