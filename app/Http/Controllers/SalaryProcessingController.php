<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceEmployee;
use App\Models\Leave;
use App\Models\SalaryArrear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryProcessingController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Pay Slip') || \Auth::user()->type == 'hr' || \Auth::user()->type == 'company') {
            // Month and year options for dropdown
            $monthOptions = [
                '01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR',
                '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AUG',
                '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DEC',
            ];
            
            // Get month and year from request, default to current month
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            
            // Ensure month is zero-padded (01-12 format)
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            
            // Validate month is between 01-12
            if (!isset($monthOptions[$month])) {
                $month = date('m');
            }
            
            // Validate year is numeric
            if (!is_numeric($year) || $year < 2000 || $year > 2100) {
                $year = date('Y');
            }
            
            $formate_month_year = $year . '-' . $month;
            
            $terminatedEmployees = \App\Models\Termination::pluck('employee_id')->toArray();
            
            // Get all employees with salary > 0
            $employees = Employee::where('created_by', \Auth::user()->creatorId())
                ->where('salary', '>', 0)
                ->whereNotIn('id', $terminatedEmployees)
                ->orderBy('name', 'asc')
                ->get();
            
            // Calculate total days in the month
            $totalMonthlyDays = date('t', strtotime($formate_month_year . '-01'));
            
            $salaryData = [];
            
            foreach ($employees as $employee) {
                $data = $this->calculateEmployeeSalaryData($employee, $month, $year, $totalMonthlyDays);
                if ($data) {
                    $salaryData[] = $data;
                }
            }
            
            $currentyear = date("Y");
            $tempyear = intval($currentyear) - 2;
            $yearOptions = [];
            for ($i = 0; $i < 10; $i++) {
                $yearOptions[$tempyear + $i] = $tempyear + $i;
            }
            
            return view('salary-processing.index', compact('salaryData', 'monthOptions', 'yearOptions', 'month', 'year'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    private function calculateEmployeeSalaryData($employee, $month, $year, $totalMonthlyDays)
    {
        $formate_month_year = $year . '-' . $month;
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        // For LOP calculation: if viewing current month, only calculate up to today
        // For past months, calculate for entire month
        $today = Carbon::today();
        $selectedMonth = Carbon::create($year, $month, 1);
        $isCurrentMonth = $selectedMonth->isCurrentMonth() && $selectedMonth->year == $today->year;
        $lopEndDate = $isCurrentMonth ? ($today->lt($endDate) ? $today : $endDate) : $endDate;
        
        // Get attendance records - also get records up to lopEndDate for LOP calculation
        $attendanceRecords = AttendanceEmployee::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
        
        // Also get attendance records up to lopEndDate for accurate LOP calculation
        $attendanceRecordsForLOP = AttendanceEmployee::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $lopEndDate->format('Y-m-d')])
            ->get();
        
        // Calculate present days (full present days)
        $presentDays = 0;
        $halfDayCount = 0;
        
        foreach ($attendanceRecords as $attendance) {
            if ($attendance->status == 'Present') {
                $presentDays++;
            } elseif ($attendance->status == 'Half Day') {
                $halfDayCount++;
            }
        }
        
        // Convert half days to full days (2 half days = 1 full day)
        $halfDaysAsFull = $halfDayCount / 2;
        $presentDays += $halfDaysAsFull;
        
        // Get approved leaves for the month
        // Leaves that overlap with the selected month
        $leaves = Leave::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    // Leave starts in the month
                    $q->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                })->orWhere(function($q) use ($startDate, $endDate) {
                    // Leave ends in the month
                    $q->whereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                })->orWhere(function($q) use ($startDate, $endDate) {
                    // Leave spans the entire month
                    $q->where('start_date', '<=', $startDate->format('Y-m-d'))
                      ->where('end_date', '>=', $endDate->format('Y-m-d'));
                });
            })
            ->get();
        
        // Calculate approved leave days (excluding LOP)
        $approvedLeaveDays = 0;
        $lopLeaveDays = 0;
        $approvedLeaveCount = 0; // Count of approved leave records (not days)
        
        // Separate LOP and non-LOP leaves
        $nonLopLeaves = $leaves->filter(function($leave) {
            return $leave->is_lop != true && $leave->is_lop != 1;
        })->sortBy('start_date');
        
        $lopLeaves = $leaves->filter(function($leave) {
            return $leave->is_lop == true || $leave->is_lop == 1;
        });
        
        // Process non-LOP leaves (up to 2 leaves)
        foreach ($nonLopLeaves as $leave) {
            if ($approvedLeaveCount < 2) {
                $approvedLeaveCount++;
                
                $leaveStart = Carbon::parse($leave->start_date);
                $leaveEnd = Carbon::parse($leave->end_date);
                
                // Only count days within the selected month
                $actualStart = $leaveStart->lt($startDate) ? $startDate : $leaveStart;
                $actualEnd = $leaveEnd->gt($endDate) ? $endDate : $leaveEnd;
                
                if ($leave->leave_duration == 'Half Day') {
                    // Check if the half day is within the month
                    if ($actualStart->lte($actualEnd)) {
                        $approvedLeaveDays += 0.5;
                    }
                } else {
                    // Full day leave - count all days within the month
                    if ($actualStart->lte($actualEnd)) {
                        $days = $actualStart->diffInDays($actualEnd) + 1;
                        $approvedLeaveDays += $days;
                    }
                }
            }
        }
        
        // Process LOP leaves
        // $lopEndDate is already set above based on whether it's current month or not
        foreach ($lopLeaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date);
            $leaveEnd = Carbon::parse($leave->end_date);
            
            // Only count days within the selected month and up to today
            $actualStart = $leaveStart->lt($startDate) ? $startDate : $leaveStart;
            $actualEnd = $leaveEnd->gt($lopEndDate) ? $lopEndDate : $leaveEnd;
            
            if ($leave->leave_duration == 'Half Day') {
                if ($actualStart->lte($actualEnd) && $actualStart->dayOfWeek != Carbon::SUNDAY) {
                    $lopLeaveDays += 0.5;
                }
            } else {
                if ($actualStart->lte($actualEnd)) {
                    // Count days excluding Sundays
                    $currentDay = $actualStart->copy();
                    $days = 0;
                    while ($currentDay->lte($actualEnd)) {
                        if ($currentDay->dayOfWeek != Carbon::SUNDAY) {
                            $days++;
                        }
                        $currentDay->addDay();
                    }
                    $lopLeaveDays += $days;
                }
            }
        }
        
        // Calculate payable days
        $payableDays = $presentDays + $approvedLeaveDays;
        
        // Calculate LOP days (absent days + LOP leaves)
        // LOP should only be calculated up to today (if current month), and Sundays should be excluded
        // $lopEndDate is already set above based on whether it's current month or not
        
        // Find all dates in the month up to lopEndDate
        $allDates = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($lopEndDate)) {
            $allDates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }
        
        // Find absent days (days with no attendance and no approved leave)
        // Exclude Sundays from LOP calculation
        $absentDays = 0;
        foreach ($allDates as $dateStr) {
            $date = Carbon::parse($dateStr);
            
            // Skip Sundays - they should not be counted in LOP
            if ($date->dayOfWeek == Carbon::SUNDAY) {
                continue;
            }
            
            // Check if there's attendance
            // Handle both string and date object formats
            $attendanceRecord = $attendanceRecordsForLOP->first(function($attendance) use ($dateStr) {
                // Convert date to string format for comparison
                if ($attendance->date instanceof \Carbon\Carbon) {
                    $attendanceDate = $attendance->date->format('Y-m-d');
                } elseif (is_string($attendance->date)) {
                    $attendanceDate = $attendance->date;
                } else {
                    $attendanceDate = date('Y-m-d', strtotime($attendance->date));
                }
                return $attendanceDate === $dateStr;
            });
            
            // If no attendance record, or if attendance status is "Absent", it counts as LOP
            if (!$attendanceRecord || $attendanceRecord->status == 'Absent') {
                // Check if there's an approved leave (non-LOP) for this date
                $hasApprovedLeave = false;
                $approvedLeaveIndex = 0;
                foreach ($nonLopLeaves as $leave) {
                    if ($approvedLeaveIndex < 2) {
                        $leaveStart = Carbon::parse($leave->start_date);
                        $leaveEnd = Carbon::parse($leave->end_date);
                        
                        if ($date->between($leaveStart, $leaveEnd)) {
                            $hasApprovedLeave = true;
                            break;
                        }
                        $approvedLeaveIndex++;
                    }
                }
                
                if (!$hasApprovedLeave) {
                    $absentDays++;
                }
            }
        }
        
        $lopDays = $absentDays + $lopLeaveDays;
        
        // Actual Salary
        $actualSalary = $employee->salary ?? 0;
        
        // Monthly Salary = (Actual Salary / Total Monthly Days) * Payable Days
        $monthlySalary = ($actualSalary / $totalMonthlyDays) * $payableDays;
        
        // Get Salary Arrears for this month
        $salaryArrears = SalaryArrear::where('employee_id', $employee->id)
            ->where('payment_month', 'like', $formate_month_year . '%')
            ->where('created_by', \Auth::user()->creatorId())
            ->sum('amount');
        
        // Final Payable Salary
        $finalPayableSalary = $monthlySalary + $salaryArrears;
        
        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'total_monthly_days' => $totalMonthlyDays,
            'payable_days' => round($payableDays, 2),
            'present_days' => round($presentDays, 2),
            'approved_leave_days' => round($approvedLeaveDays, 2),
            'lop_days' => round($lopDays, 2),
            'actual_salary' => $actualSalary,
            'monthly_salary' => round($monthlySalary, 2),
            'salary_arrears' => round($salaryArrears, 2),
            'final_payable_salary' => round($finalPayableSalary, 2),
        ];
    }
}

