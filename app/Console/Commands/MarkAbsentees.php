<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\AttendanceEmployee;
use Carbon\Carbon;

class MarkAbsentees extends Command
{
    protected $signature = 'attendance:mark-absentees';
    protected $description = 'Mark employees as absent who didn\'t punch in';

    public function handle()
    {
        $todayCarbon = Carbon::today();
        $today = $todayCarbon->toDateString();
        
        // 1. Skip weekends (Saturday and Sunday)
        if ($todayCarbon->isWeekend()) {
            $this->info('Today is a weekend. Skipping marking absentees.');
            return;
        }
        
        // 2. Skip public holidays
        $isHoliday = \App\Models\Holiday::where('start_date', '<=', $today)
                                        ->where('end_date', '>=', $today)
                                        ->exists();
        if ($isHoliday) {
            $this->info('Today is a holiday. Skipping marking absentees.');
            return;
        }
        
        $employees = Employee::all();
        
        foreach ($employees as $employee) {
            // 3. Skip if the employee has an active, non-rejected leave on this date
            $hasLeave = \App\Models\Leave::where('employee_id', $employee->id)
                                        ->where('status', '!=', 'Reject')
                                        ->where('start_date', '<=', $today)
                                        ->where('end_date', '>=', $today)
                                        ->exists();
            if ($hasLeave) {
                continue; // Skip creating an absent record since they are on leave
            }

            $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                                          ->where('date', $today)
                                          ->first();
            
            if (!$attendance) {
                AttendanceEmployee::create([
                    'employee_id' => $employee->id,
                    'date' => $today,
                    'status' => AttendanceEmployee::STATUS_ABSENT,
                    'clock_in' => '00:00:00',
                    'clock_out' => '00:00:00',
                    'created_by' => 1 // Or your admin user ID
                ]);
            } elseif ($attendance->clock_in != '00:00:00' && $attendance->clock_out == '00:00:00') {
                // If only punched in but not out by end of day
                $attendance->update([
                    'status' => AttendanceEmployee::STATUS_SINGLE_PUNCH
                ]);
            }
        }
        
        $this->info('Absentees marked successfully.');
    }
}