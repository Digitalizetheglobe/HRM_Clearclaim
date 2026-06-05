<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceEmployee;
use App\Models\Leave;
use App\Models\SalaryArrear;
use App\Models\LateMarkDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalaryProcessingExport;

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
        
        // Adjust start date to joining date if they joined this month
        if (!empty($employee->company_doj)) {
            $employeeDoj = Carbon::parse($employee->company_doj)->startOfDay();
            // If joining date is after the month ends, they shouldn't get any salary
            if ($employeeDoj->gt($endDate)) {
                return null;
            }
            // If they joined during this month, start calculations from their joining date
            if ($employeeDoj->gt($startDate) && $employeeDoj->format('Y-m') == $formate_month_year) {
                $startDate = $employeeDoj;
            }
        }
        
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
        $totalLateMarks = 0;
        
        $attendanceController = app(\App\Http\Controllers\AttendanceEmployeeController::class);
        
        foreach ($attendanceRecords as $attendance) {
            $dateString = $attendance->date instanceof \Carbon\Carbon ? $attendance->date->format('Y-m-d') : date('Y-m-d', strtotime($attendance->date));
            $trueStatus = $attendanceController->calculateAttendanceStatusWithNewRules(
                $attendance->clock_in,
                $attendance->clock_out,
                $dateString,
                $employee->id
            );
            
            if (AttendanceEmployee::isLateMarkForEmployee($employee->id, $attendance->clock_in)) {
                $totalLateMarks++;
            }
            
            if (in_array($trueStatus, ['Present', 'Present (Late)', 'Half Day (Late)'])) {
                $presentDays++;
            } elseif (in_array($trueStatus, ['Half Day', 'Half Day (Punch Miss)'])) {
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
        
        // Process non-LOP leaves
        foreach ($nonLopLeaves as $leave) {
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
        
        $holidaysList = \App\Models\Holiday::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->pluck('date')->toArray();

        // Calculate week offs (Sundays) and holidays up to lopEndDate
        $weekOffDays = 0;
        $holidayDays = 0;
        $allDates = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $allDates[] = $dateStr;
            
            if ($currentDate->dayOfWeek == Carbon::SUNDAY) {
                $weekOffDays++;
            } elseif (in_array($dateStr, $holidaysList)) {
                $holidayDays++;
            }
            
            $currentDate->addDay();
        }

        // Calculate total days in the employee's active period this month
        $totalDaysInPeriod = $startDate->diffInDays($endDate) + 1;
        
        // Calculate payable days first
        $payableDays = $presentDays + $approvedLeaveDays + $weekOffDays + $holidayDays;
        
        // LOP days is simply the difference between the days they should have worked and the days they are getting paid for
        $lopDays = $totalDaysInPeriod - $payableDays;
        if ($lopDays < 0) {
            $lopDays = 0;
        }

        // Calculate payable days
        $payableDays = $presentDays + $approvedLeaveDays + $weekOffDays + $holidayDays;
        
        // Get Late Mark Deduction (in days) for this month
        $lateMarkDeduction = LateMarkDeduction::where('employee_id', $employee->id)
            ->where('payment_month', $formate_month_year)
            ->sum('amount');
            
        // Calculate actual payable days
        $actualPayableDays = $payableDays - $lateMarkDeduction;
        

        
        // Actual Salary
        $actualSalary = $employee->salary ?? 0;
        
        // Daily Salary = Actual Salary / Total Monthly Days (rounded to 2 decimal places)
        $dailySalary = $totalMonthlyDays > 0 ? round($actualSalary / $totalMonthlyDays, 2) : 0;
        
        // Monthly Salary = Daily Salary * Actual Payable Days
        $monthlySalary = $dailySalary * $actualPayableDays;
        
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
            'total_late_marks' => $totalLateMarks > 3 ? $totalLateMarks - 3 : 0, // Exclude first 3 as requested
            'payable_days' => round($payableDays, 2),
            'actual_payable_days' => round($actualPayableDays, 2),
            'present_days' => round($presentDays, 2),
            'half_days' => $halfDayCount,
            'half_days_payable' => round($halfDaysAsFull, 2),
            'approved_leave_days' => round($approvedLeaveDays, 2),
            'lop_days' => round($lopDays, 2),
            'actual_salary' => $actualSalary,
            'daily_salary' => round($dailySalary, 2),
            'monthly_salary' => round($monthlySalary, 2),
            'salary_arrears' => round($salaryArrears, 2),
            'late_mark_deduction_amount' => $lateMarkDeduction,
            'final_payable_salary' => round($finalPayableSalary, 2),
        ];
    }

    public function export(Request $request)
    {
        if (\Auth::user()->can('Manage Pay Slip') || \Auth::user()->type == 'hr' || \Auth::user()->type == 'company') {
            $monthOptions = [
                '01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR',
                '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AUG',
                '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DEC',
            ];
            
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            
            if (!isset($monthOptions[$month])) {
                $month = date('m');
            }
            if (!is_numeric($year) || $year < 2000 || $year > 2100) {
                $year = date('Y');
            }
            
            $formate_month_year = $year . '-' . $month;
            $terminatedEmployees = \App\Models\Termination::pluck('employee_id')->toArray();
            
            $employees = Employee::where('created_by', \Auth::user()->creatorId())
                ->where('salary', '>', 0)
                ->whereNotIn('id', $terminatedEmployees)
                ->orderBy('name', 'asc')
                ->get();
            
            $totalMonthlyDays = date('t', strtotime($formate_month_year . '-01'));
            $salaryData = [];
            
            foreach ($employees as $employee) {
                $data = $this->calculateEmployeeSalaryData($employee, $month, $year, $totalMonthlyDays);
                if ($data) {
                    $salaryData[] = $data;
                }
            }
            
            $fileName = 'Salary_Processing_' . $monthOptions[$month] . '_' . $year . '.xlsx';
            
            return Excel::download(new SalaryProcessingExport($salaryData), $fileName);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

