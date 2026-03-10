<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing attendance records with new status logic
        $attendances = AttendanceEmployee::all();
        
        foreach ($attendances as $attendance) {
            $newStatus = $this->calculateNewStatus($attendance);
            
            if ($newStatus !== $attendance->status) {
                $attendance->status = $newStatus;
                $attendance->save();
            }
        }
    }

    /**
     * Calculate new status based on the enhanced logic
     */
    private function calculateNewStatus($attendance)
    {
        // If no clock in at all, return Absent
        if (empty($attendance->clock_in) || $attendance->clock_in == '00:00:00') {
            return AttendanceEmployee::STATUS_ABSENT;
        }
        
        // Check if this is a late mark
        $isLateMark = AttendanceEmployee::isLateMarkForEmployee($attendance->employee_id, $attendance->clock_in);
        
        // Count late marks for this employee in the month (excluding current day)
        $lateMarksCount = $this->countLateMarksInMonthExcludingCurrent($attendance->employee_id, $attendance->date);
        
        // If clocked in but not out, return Half Day (Punch Miss)
        if (empty($attendance->clock_out) || $attendance->clock_out == '00:00:00') {
            return 'Half Day (Punch Miss)';
        }
        
        // Calculate total worked hours
        $start = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
        $end = Carbon::parse($attendance->date . ' ' . $attendance->clock_out);
        
        // Handle case where clock out might be next day
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        $totalMinutes = $end->diffInMinutes($start);
        $workedHours = $totalMinutes / 60;
        
        // Check if worked hours < 4.5 hours (genuine Half Day)
        if ($workedHours < AttendanceEmployee::HALF_DAY_HOURS_THRESHOLD) {
            return AttendanceEmployee::STATUS_HALF_DAY;
        }
        
        // For late marks with sufficient hours (≥4.5)
        if ($isLateMark) {
            if ($lateMarksCount >= AttendanceEmployee::MAX_LATE_MARKS_PER_MONTH) {
                // This is the 4th+ late mark - should be Half Day (Late)
                return 'Half Day (Late)';
            } else {
                // First 3 late marks - always Present (Late) regardless of hours
                return 'Present (Late)';
            }
        }
        
        // If all conditions pass, check if it's a full day (8.5 hours)
        if ($totalMinutes >= 510) { // 8.5 hours = 510 minutes
            return AttendanceEmployee::STATUS_PRESENT;
        } else {
            return AttendanceEmployee::STATUS_HALF_DAY;
        }
    }

    /**
     * Count late marks for an employee in a given month (excluding current day)
     */
    private function countLateMarksInMonthExcludingCurrent($employeeId, $currentDate)
    {
        $carbonDate = Carbon::parse($currentDate);
        $startOfMonth = $carbonDate->copy()->startOfMonth()->format('Y-m-d');
        
        $lateMarks = AttendanceEmployee::where('employee_id', $employeeId)
            ->where('date', '>=', $startOfMonth)
            ->where('date', '<', $currentDate) // Only dates BEFORE current date
            ->where('clock_in', '!=', '00:00:00')
            ->get()
            ->filter(function($attendance) use ($employeeId) {
                return AttendanceEmployee::isLateMarkForEmployee($employeeId, $attendance->clock_in);
            });
        
        return $lateMarks->count();
    }

    /**
     * Count late marks for an employee in a given month
     */
    private function countLateMarksInMonth($employeeId, $date)
    {
        $carbonDate = Carbon::parse($date);
        $startOfMonth = $carbonDate->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $carbonDate->copy()->endOfMonth()->format('Y-m-d');
        
        $lateMarks = AttendanceEmployee::where('employee_id', $employeeId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('clock_in', '!=', '00:00:00')
            ->get()
            ->filter(function($attendance) use ($employeeId) {
                return AttendanceEmployee::isLateMarkForEmployee($employeeId, $attendance->clock_in);
            });
        
        return $lateMarks->count();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old logic - this would require more complex logic
        // For now, we'll leave the data as is since reverting could cause data loss
    }
};
