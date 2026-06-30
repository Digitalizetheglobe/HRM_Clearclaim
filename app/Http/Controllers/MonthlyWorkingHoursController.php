<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AttendanceEmployee;
use App\Models\Leave;
use App\Models\Holiday;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyWorkingHoursExport;

class MonthlyWorkingHoursController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') {
            $month = $request->month ? $request->month : date('m');
            $year = $request->year ? $request->year : date('Y');
            
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', '');

            $employeesQuery = Employee::where('created_by', \Auth::user()->creatorId())
                ->whereNotIn('id', \App\Models\Termination::pluck('employee_id')->toArray());
            
            if (!empty($request->department)) {
                $employeesQuery->where('department_id', $request->department);
            }
            if (!empty($request->employee)) {
                $employeesQuery->where('id', $request->employee);
            }

            if (!empty($request->search)) {
                $employeesQuery->whereHas('user', function($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->search}%");
                });
            }

            $employees = $employeesQuery->get();
            $employeeFilter = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employeeFilter->prepend('All', '');

            $summaryData = $this->calculateSummary($employees, $month, $year);

            return view('attendance.monthly_summary', compact('summaryData', 'departments', 'employeeFilter', 'month', 'year'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export(Request $request)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') {
            $month = $request->month ? $request->month : date('m');
            $year = $request->year ? $request->year : date('Y');
            return Excel::download(new MonthlyWorkingHoursExport($request->all(), $month, $year), "monthly_working_hours_{$year}_{$month}.xlsx");
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function calculateSummary($employees, $month, $year)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $totalDaysInMonth = $startDate->daysInMonth;

        $holidays = Holiday::where('created_by', \Auth::user()->creatorId())
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->pluck('date')->toArray();

        $summaryData = [];
        $departmentSummary = [];

        foreach ($employees as $employee) {
            $expectedHours = 0;
            $actualHours = 0;
            $overtimeHours = 0; // in seconds
            $shortfallHours = 0; // in seconds
            $workingDays = 0;
            $holidayCount = 0;
            $weeklyOffsCount = 0;
            $approvedLeaveDays = 0;
            
            $attendances = AttendanceEmployee::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get()->keyBy('date');

            $leaves = Leave::where('employee_id', $employee->id)
                ->where('status', 'Approved')
                ->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                      ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                })->get();

            $leaveDates = [];
            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                for ($d = $start; $d->lte($end); $d->addDay()) {
                    if ($d->month == $month) {
                        $leaveDates[] = $d->format('Y-m-d');
                    }
                }
            }

            for ($i = 1; $i <= $totalDaysInMonth; $i++) {
                $currentDate = Carbon::createFromDate($year, $month, $i);
                $dateStr = $currentDate->format('Y-m-d');

                $isWeekend = $currentDate->isWeekend();
                $isHoliday = in_array($dateStr, $holidays);
                $isLeave = in_array($dateStr, $leaveDates);

                if ($isLeave) {
                    $approvedLeaveDays++;
                    $actualHours += 9; 
                    $workingDays += 1;
                    $expectedHours += 9;
                    continue; 
                }

                if ($attendances->has($dateStr)) {
                    $attendance = $attendances[$dateStr];
                    $clockIn = $attendance->clock_in;
                    $clockOut = $attendance->clock_out;
                    $status = $attendance->status;

                    $isHalfDay = (strpos($status, 'Half Day') !== false);
                    $isAbsent = ($status == 'Absent');

                    if ($isWeekend) {
                        $weeklyOffsCount++;
                    } elseif ($isHoliday) {
                        $holidayCount++;
                    }

                    if (!$isWeekend && !$isHoliday) {
                        if ($isHalfDay) {
                            $workingDays += 0.5;
                            $expectedHours += 4.5;
                        } elseif (!$isAbsent) {
                            $workingDays += 1;
                            $expectedHours += 9;
                        }
                    }

                    if ($clockIn != '00:00:00' && $clockOut != '00:00:00') {
                        $in = Carbon::parse($clockIn);
                        $out = Carbon::parse($clockOut);
                        $workedSeconds = $in->diffInSeconds($out);
                        $actualHours += ($workedSeconds / 3600);
                    }
                } else {
                    if ($isWeekend) {
                        $weeklyOffsCount++;
                    } elseif ($isHoliday) {
                        $holidayCount++;
                    }
                }
            }
            
            $totalExpectedSeconds = $expectedHours * 3600;
            $totalActualSeconds = $actualHours * 3600;
            
            $netHoursSeconds = $totalActualSeconds - $totalExpectedSeconds;
            
            if ($netHoursSeconds > 0) {
                $overtimeHours = $netHoursSeconds;
                $shortfallHours = 0;
            } else {
                $overtimeHours = 0;
                $shortfallHours = abs($netHoursSeconds);
            }
            
            $formatTime = function($seconds) {
                $h = floor($seconds / 3600);
                $m = floor(($seconds % 3600) / 60);
                return sprintf('%02d:%02d', $h, $m);
            };

            $netHoursPrefix = $netHoursSeconds >= 0 ? '+' : '-';
            $netHoursFormatted = $netHoursPrefix . $formatTime(abs($netHoursSeconds));

            $actualHoursFormatted = $formatTime($actualHours * 3600);
            $expectedHoursFormatted = $formatTime($expectedHours * 3600);

            $summaryData[] = [
                'employee_id' => \Auth::user()->employeeIdFormat($employee->employee_id),
                'name' => !empty($employee->name) ? $employee->name : '',
                'department' => !empty($employee->department) ? $employee->department->name : '',
                'working_days' => $workingDays,
                'expected_hours' => $expectedHoursFormatted,
                'actual_hours' => $actualHoursFormatted,
                'overtime' => $formatTime($overtimeHours),
                'shortfall' => $formatTime($shortfallHours),
                'net_hours' => $netHoursFormatted,
                'approved_leaves' => $approvedLeaveDays,
                'holidays' => $holidayCount,
                'weekly_offs' => $weeklyOffsCount,
                'raw_expected_hours' => $expectedHours * 3600,
                'raw_actual_hours' => $actualHours * 3600,
                'raw_overtime' => $overtimeHours,
                'raw_shortfall' => $shortfallHours,
            ];

            $deptName = !empty($employee->department) ? $employee->department->name : 'Unknown';
            if (!isset($departmentSummary[$deptName])) {
                $departmentSummary[$deptName] = [
                    'count' => 0,
                    'expected' => 0,
                    'actual' => 0,
                    'overtime' => 0,
                    'shortfall' => 0
                ];
            }
            $departmentSummary[$deptName]['count']++;
            $departmentSummary[$deptName]['expected'] += ($expectedHours * 3600);
            $departmentSummary[$deptName]['actual'] += ($actualHours * 3600);
            $departmentSummary[$deptName]['overtime'] += $overtimeHours;
            $departmentSummary[$deptName]['shortfall'] += $shortfallHours;
        }

        return [
            'employees' => collect($summaryData)->sortBy('employee_id')->values()->all(),
            'departments' => $departmentSummary,
            'totals' => [
                'expected' => collect($summaryData)->sum('raw_expected_hours'),
                'actual' => collect($summaryData)->sum('raw_actual_hours'),
                'overtime' => collect($summaryData)->sum('raw_overtime'),
                'shortfall' => collect($summaryData)->sum('raw_shortfall'),
                'working_days' => collect($summaryData)->sum('working_days'),
            ]
        ];
    }
}
