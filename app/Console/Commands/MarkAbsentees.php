<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\AttendanceEmployee;
use Carbon\Carbon;

class MarkAbsentees extends Command
{
    protected $signature = 'attendance:mark-absentees';
    protected $description = 'Log employees who did not punch in (no longer writes to DB — absent is derived on-the-fly)';

    public function handle()
    {
        $todayCarbon = Carbon::today();
        $today = $todayCarbon->toDateString();

        // 1. Skip weekends (Saturday and Sunday)
        if ($todayCarbon->isWeekend()) {
            $this->info('Today is a weekend. Skipping.');
            return;
        }

        // 2. Skip public holidays
        $isHoliday = \App\Models\Holiday::where('date', $today)->exists();
        if ($isHoliday) {
            $this->info('Today is a holiday. Skipping.');
            return;
        }

        $employees = Employee::all();
        $absentCount = 0;

        foreach ($employees as $employee) {
            // Skip if the employee has an active, non-rejected leave on this date
            $hasLeave = \App\Models\Leave::where('employee_id', $employee->id)
                                        ->where('status', '!=', 'Reject')
                                        ->where('start_date', '<=', $today)
                                        ->where('end_date', '>=', $today)
                                        ->exists();
            if ($hasLeave) {
                continue;
            }

            $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                                          ->where('date', $today)
                                          ->first();

            if (!$attendance) {
                // Absent is derived on-the-fly — no DB insert needed
                $absentCount++;
                \Log::info("Absent (no punch): Employee #{$employee->id} ({$employee->name}) on {$today}");
            } elseif ($attendance->clock_in != '00:00:00' && $attendance->clock_out == '00:00:00') {
                // If only punched in but not out by end of day — update to Single Punch
                $attendance->update([
                    'status' => AttendanceEmployee::STATUS_SINGLE_PUNCH
                ]);
            }
        }

        $this->info("Done. {$absentCount} employee(s) were absent today (logged only, not stored in DB).");
    }
}