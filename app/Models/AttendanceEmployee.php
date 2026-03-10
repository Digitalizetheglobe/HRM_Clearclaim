<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEmployee extends Model
{

    const STATUS_PRESENT = 'Present';
    const STATUS_PRESENT_LATE = 'Present (Late)';
    const STATUS_HALF_DAY = 'Half Day';
    const STATUS_ABSENT = 'Absent';
    const STATUS_SINGLE_PUNCH = 'Single Punch In';
    const STATUS_LATE = 'Late';
    const STATUS_HALF_DAY_LATE = 'Half Day (Late)';
    const STATUS_HALF_DAY_PUNCH_MISS = 'Half Day (Punch Miss)';
    const REQUIRED_WORKING_HOURS = 8.5; // 8 hours 30 minutes in decimal (for full day)
    const HALF_DAY_HOURS_THRESHOLD = 4.5; // 4 hours 30 minutes for half day
    const LATE_MARK_TIME = '10:15:00'; // Default punch-in time (fallback if department not set)
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

    /**
     * Get department-specific punch-in time for an employee
     */
    public static function getEmployeePunchInTime($employeeId)
    {
        $employee = Employee::find($employeeId);
        if ($employee && $employee->department) {
            return $employee->department->punch_in_time ?: self::LATE_MARK_TIME;
        }
        return self::LATE_MARK_TIME;
    }

    /**
     * Check if punch-in time is late based on department settings
     */
    public static function isLateMarkForEmployee($employeeId, $clockInTime)
    {
        $punchInTime = self::getEmployeePunchInTime($employeeId);
        $clockInTimestamp = strtotime($clockInTime);
        $punchInTimestamp = strtotime($punchInTime);
        
        return $clockInTimestamp > $punchInTimestamp;
    }
    
    
}
