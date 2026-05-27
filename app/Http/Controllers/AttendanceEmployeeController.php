<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceImport;
use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\IpRestrict;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  // Add this line
use App\Models\Leave as LocalLeave;  // Add this line

class AttendanceEmployeeController extends Controller
{

    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance')) {
            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

              // Get employees for filter dropdown
            $terminatedEmployees = \App\Models\Termination::pluck('employee_id')->toArray();
            $employees = Employee::where('created_by', \Auth::user()->creatorId())
                ->whereNotIn('id', $terminatedEmployees)
                ->with('user')
                ->get()
                ->pluck('name', 'id');
            $employees->prepend('All', '');

            if (\Auth::user()->type == 'employee') {
                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

                $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp)
                                ->where('status', '!=', AttendanceEmployee::STATUS_ABSENT) // Absent is derived on-the-fly
                                ->where('clock_in', '!=', '00:00:00') // Only real punch records
                                ->orderBy('date', 'desc')
                                ->orderBy('clock_in', 'desc');

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));


                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }

                $attendanceEmployee = $attendanceEmployee->get();
            } else {
                $terminatedEmployees = \App\Models\Termination::pluck('employee_id')->toArray();
                $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId())
                    ->whereNotIn('id', $terminatedEmployees);
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }

                if (!empty($request->employee)) {
                    $employee->where('id', $request->employee);
                }

                $employee = $employee->get()->pluck('id');

                $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee)
                                ->where('status', '!=', AttendanceEmployee::STATUS_ABSENT) // Absent is derived on-the-fly
                                ->where('clock_in', '!=', '00:00:00') // Only real punch records
                                ->orderBy('date', 'desc')
                                ->orderBy('clock_in', 'desc');
                
                if ($request->type == 'monthly' && !empty($request->month)) {

                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {

                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }

                $attendanceEmployee = $attendanceEmployee->get();
            }

            // Process records with missing clock-out based on date
            $today = Carbon::today()->format('Y-m-d');
            foreach ($attendanceEmployee as $attendance) {
                if ($attendance->clock_out == '00:00:00' && $attendance->clock_in != '00:00:00') {
                    $attendanceDate = Carbon::parse($attendance->date)->format('Y-m-d');
                    
                    if ($attendanceDate < $today) {
                        // Past date: Apply missing punch-out logic (Half Day with calculated clock out)
                        try {
                            // Parse clock_in time
                            $clockInTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                            
                            // Add 4.5 hours (4 hours 30 minutes) to clock_in
                            $calculatedClockOut = $clockInTime->copy()->addHours(4)->addMinutes(30);
                            
                            // If calculated time goes past midnight (next day), cap it at end of day (23:59:59)
                            $endOfDay = Carbon::parse($attendance->date . ' 23:59:59');
                            if ($calculatedClockOut->gt($endOfDay)) {
                                $calculatedClockOut = $endOfDay;
                            }
                            
                            // Format as H:i:s
                            $clockOutTime = $calculatedClockOut->format('H:i:s');
                            
                            // Check if this was a late mark
                            $isLateMark = AttendanceEmployee::isLateMarkForEmployee($attendance->employee_id, $attendance->clock_in);
                            $lateMarksCount = $this->countLateMarksInMonth($attendance->employee_id, $attendance->date);
                            
                            // Update attendance record
                            $attendance->clock_out = $clockOutTime;
                            
                            // Set status based on late mark count
                            if ($isLateMark && $lateMarksCount > AttendanceEmployee::MAX_LATE_MARKS_PER_MONTH) {
                                $attendance->status = AttendanceEmployee::STATUS_HALF_DAY_LATE;
                            } else {
                                $attendance->status = AttendanceEmployee::STATUS_HALF_DAY_PUNCH_MISS;
                            }
                            
                            // Calculate early leaving (if applicable)
                            $endTime = Utility::getValByName('company_end_time');
                            if ($endTime) {
                                $expectedEndTime = Carbon::parse($attendance->date . ' ' . $endTime);
                                if ($calculatedClockOut->lt($expectedEndTime)) {
                                    $totalEarlyLeavingSeconds = $expectedEndTime->diffInSeconds($calculatedClockOut);
                                    $hours = floor($totalEarlyLeavingSeconds / 3600);
                                    $mins = floor(($totalEarlyLeavingSeconds % 3600) / 60);
                                    $secs = $totalEarlyLeavingSeconds % 60;
                                    $attendance->early_leaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                                } else {
                                    $attendance->early_leaving = '00:00:00';
                                }
                            }
                            
                            // Set overtime to 00:00:00 (no overtime for half day)
                            $attendance->overtime = '00:00:00';
                            
                            $attendance->save();
                        } catch (\Exception $e) {
                            \Log::error('Error processing missing punch-out for attendance ID ' . $attendance->id . ': ' . $e->getMessage());
                            // If error occurs, at least set status to Single Punch In
                            if ($attendance->status != AttendanceEmployee::STATUS_SINGLE_PUNCH) {
                                $attendance->status = AttendanceEmployee::STATUS_SINGLE_PUNCH;
                                $attendance->save();
                            }
                        }
                    } else {
                        // Current date: Keep as "Single Punch In"
                        if ($attendance->status != AttendanceEmployee::STATUS_SINGLE_PUNCH) {
                            $attendance->status = AttendanceEmployee::STATUS_SINGLE_PUNCH;
                            $attendance->save();
                        }
                    }
                }
            }

            return view('attendance.index', compact('attendanceEmployee', 'branch', 'department', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Attendance')) {
            $terminatedUserIds = Employee::whereIn('id', \App\Models\Termination::pluck('employee_id')->toArray())->pluck('user_id')->toArray();
            $employees = User::where('created_by', '=', Auth::user()->creatorId())
                ->where('type', '=', "employee")
                ->whereNotIn('id', $terminatedUserIds)
                ->get()
                ->pluck('name', 'id');

            return view('attendance.create', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    
    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'date' => 'required',
                    'clock_in' => 'required',
                    'clock_out' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            // Check for existing attendance
            $attendance = AttendanceEmployee::where('employee_id', '=', $request->employee_id)
                ->where('date', '=', $request->date)
                ->where('clock_out', '=', '00:00:00')
                ->get()
                ->toArray();

            if ($attendance) {
                return redirect()->route('attendanceemployee.index')->with('error', __('Employee Attendance Already Created.'));
            }

            $date = $request->date ?? date("Y-m-d");

            // Calculate late time based on department-specific punch-in time
            $late = $this->calculateLateTime($request->clock_in . ':00', $date, $request->employee_id);

            // Calculate early leaving
            $endTime = Utility::getValByName('company_end_time');
            $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($date . ' ' . $request->clock_out . ':00');
            $hours = floor($totalEarlyLeavingSeconds / 3600);
            $mins  = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs  = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            // Calculate overtime
            if (strtotime($request->clock_out . ':00') > strtotime($date . ' ' . $endTime)) {
                $totalOvertimeSeconds = strtotime($date . ' ' . $request->clock_out . ':00') - strtotime($date . ' ' . $endTime);
                $hours = floor($totalOvertimeSeconds / 3600);
                $mins  = floor($totalOvertimeSeconds / 60 % 60);
                $secs  = floor($totalOvertimeSeconds % 60);
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            // Calculate status using new rules
            $status = $this->calculateAttendanceStatusWithNewRules(
                $request->clock_in . ':00',
                $request->clock_out . ':00',
                $date,
                $request->employee_id
            );

            $employeeAttendance = new AttendanceEmployee();
            $employeeAttendance->employee_id   = $request->employee_id;
            $employeeAttendance->date          = $request->date;
            $employeeAttendance->status        = $status;
            $employeeAttendance->clock_in      = $request->clock_in . ':00';
            $employeeAttendance->clock_out     = $request->clock_out . ':00';
            $employeeAttendance->late          = $late;
            $employeeAttendance->early_leaving = $earlyLeaving;
            $employeeAttendance->overtime      = $overtime;
            $employeeAttendance->total_rest    = '00:00:00';
            $employeeAttendance->created_by    = \Auth::user()->creatorId();
            
            // Handle different Half Day scenarios with automatic punch-out calculation
            if ($status === AttendanceEmployee::STATUS_HALF_DAY) {
                // Rule 1: Regular Half Day - set punch-out to exactly 4.5 hours
                $this->handleHalfDayStatus($employeeAttendance, $date);
            } elseif ($status === AttendanceEmployee::STATUS_HALF_DAY_PUNCH_MISS) {
                // Rule 2: Half Day (Punch Miss) - auto-calculate punch-out to 4.5 hours
                $this->handleHalfDayPunchMiss($employeeAttendance, $date);
            } elseif ($status === AttendanceEmployee::STATUS_HALF_DAY_LATE) {
                // Rule 3: Half Day (Late) - auto-calculate punch-out to 4.5 hours
                $this->handleHalfDayLateMark($employeeAttendance, $date);
            } else {
                $employeeAttendance->save();
            }

            return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    public function show(Request $request)
    {
        // return redirect()->back();
        return redirect()->route('attendanceemployee.index');
    }

    public function edit($id)
    {
        if (\Auth::user()->can('Edit Attendance')) {
            $attendanceEmployee = AttendanceEmployee::where('id', $id)->first();
            $terminatedEmployees = \App\Models\Termination::pluck('employee_id')->toArray();
            $employees          = Employee::where('created_by', '=', \Auth::user()->creatorId())
                ->whereNotIn('id', $terminatedEmployees)
                ->get()
                ->pluck('name', 'id');

            return view('attendance.edit', compact('attendanceEmployee', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function update(Request $request, $id)
    // {
    //     if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') {
    //         $employeeId      = AttendanceEmployee::where('employee_id', $request->employee_id)->first();
    //         $check = AttendanceEmployee::where('employee_id', '=', $request->employee_id)->where('date', $request->date)->first();

    //         $startTime = Utility::getValByName('company_start_time');
    //         $endTime   = Utility::getValByName('company_end_time');

    //         $clockIn = $request->clock_in;
    //         $clockOut = $request->clock_out;

    //         if ($clockIn) {
    //             $status = "present";
    //         } else {
    //             $status = "leave";
    //         }

    //         $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

    //         $hours = floor($totalLateSeconds / 3600);
    //         $mins  = floor($totalLateSeconds / 60 % 60);
    //         $secs  = floor($totalLateSeconds % 60);
    //         $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //         $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
    //         $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //         $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //         $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //         $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //         if (strtotime($clockOut) > strtotime($endTime)) {
    //             //Overtime
    //             $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
    //             $hours                = floor($totalOvertimeSeconds / 3600);
    //             $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //             $secs                 = floor($totalOvertimeSeconds % 60);
    //             $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //         } else {
    //             $overtime = '00:00:00';
    //         }
    //         if ($check->date == date('Y-m-d')) {
    //             $check->update([
    //                 'late' => $late,
    //                 'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
    //                 'overtime' => $overtime,
    //                 'clock_in' => $clockIn,
    //                 'clock_out' => $clockOut
    //             ]);

    //             return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
    //         } else {
    //             return redirect()->route('attendanceemployee.index')->with('error', __('You can only update current day attendance'));
    //         }
    //     }

    //     $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
    //     $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();
    //     if (!empty($todayAttendance) && $todayAttendance->clock_out == '00:00:00') {
    //         $startTime = Utility::getValByName('company_start_time');
    //         $endTime   = Utility::getValByName('company_end_time');
    //         if (Auth::user()->type == 'employee') {

    //             $date = date("Y-m-d");
    //             $time = date("H:i:s");

    //             //early Leaving
    //             $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
    //             $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //             $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //             $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //             $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //             if (time() > strtotime($date . $endTime)) {
    //                 //Overtime
    //                 $totalOvertimeSeconds = time() - strtotime($date . $endTime);
    //                 $hours                = floor($totalOvertimeSeconds / 3600);
    //                 $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //                 $secs                 = floor($totalOvertimeSeconds % 60);
    //                 $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //             } else {
    //                 $overtime = '00:00:00';
    //             }

    //             $attendanceEmployee                = AttendanceEmployee::find($id);
    //             $attendanceEmployee->clock_out     = $time;
    //             $attendanceEmployee->early_leaving = $earlyLeaving;
    //             $attendanceEmployee->overtime      = $overtime;
    //             $attendanceEmployee->save();

    //             return redirect()->route('dashboard')->with('success', __('Employee successfully clock Out.'));
    //         } else {
    //             $date = date("Y-m-d");
    //             //late
    //             $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

    //             $hours = floor($totalLateSeconds / 3600);
    //             $mins  = floor($totalLateSeconds / 60 % 60);
    //             $secs  = floor($totalLateSeconds % 60);
    //             $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //             //early Leaving
    //             $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
    //             $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //             $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //             $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //             $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


    //             if (strtotime($request->clock_out) > strtotime($date . $endTime)) {
    //                 //Overtime
    //                 $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
    //                 $hours                = floor($totalOvertimeSeconds / 3600);
    //                 $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //                 $secs                 = floor($totalOvertimeSeconds % 60);
    //                 $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //             } else {
    //                 $overtime = '00:00:00';
    //             }
    
    //             $attendanceEmployee                = AttendanceEmployee::find($id);
    //             $attendanceEmployee->employee_id   = $request->employee_id;
    //             $attendanceEmployee->date          = $request->date;
    //             $attendanceEmployee->clock_in      = $request->clock_in;
    //             $attendanceEmployee->clock_out     = $request->clock_out;
    //             $attendanceEmployee->late          = $late;
    //             $attendanceEmployee->early_leaving = $earlyLeaving;
    //             $attendanceEmployee->overtime      = $overtime;
    //             $attendanceEmployee->total_rest    = '00:00:00';

    //             $attendanceEmployee->save();

    //             return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
    //         }
    //     } else {
    //         return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
    //     }
    // }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') {
            $check = AttendanceEmployee::where('id', '=', $id)
                                    ->where('employee_id', '=', $request->employee_id)
                                    ->where('date', $request->date)
                                    ->first();

            if (!$check) {
                return redirect()->route('attendanceemployee.index')->with('error', __('Attendance record not found.'));
            }

            $endTime = Utility::getValByName('company_end_time');

            $clockIn = $request->clock_in;
            $clockOut = $request->clock_out;

            // Calculate late time based on department-specific punch-in time
            $late = $this->calculateLateTime($clockIn, $request->date, $request->employee_id);

            // Determine status and calculate other values
            // If clock_out is '00:00:00' or empty, it's a single punch in (Half Day per new rules)
            if (empty($clockOut) || $clockOut == '00:00:00') {
                // Check if HR/Company user wants to override status
                $status = $request->status ?? AttendanceEmployee::STATUS_HALF_DAY;
                $earlyLeaving = '00:00:00';
                $overtime = '00:00:00';
            } else {
                // Calculate early leaving
                $totalEarlyLeavingSeconds = strtotime($request->date . ' ' . $endTime) - strtotime($request->date . ' ' . $clockOut);
                $hours = floor($totalEarlyLeavingSeconds / 3600);
                $mins  = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs  = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                // Calculate overtime
                if (strtotime($request->date . ' ' . $clockOut) > strtotime($request->date . ' ' . $endTime)) {
                    $totalOvertimeSeconds = strtotime($request->date . ' ' . $clockOut) - strtotime($request->date . ' ' . $endTime);
                    $hours = floor($totalOvertimeSeconds / 3600);
                    $mins  = floor($totalOvertimeSeconds / 60 % 60);
                    $secs  = floor($totalOvertimeSeconds % 60);
                    $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } else {
                    $overtime = '00:00:00';
                }

                // Calculate status using new rules (HR/Company can override)
                if ((\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') && $request->has('status')) {
                    $status = $request->status;
                } else {
                    $status = $this->calculateAttendanceStatusWithNewRules(
                        $clockIn,
                        $clockOut,
                        $request->date,
                        $request->employee_id
                    );
                }
            }

            if ($check->date == date('Y-m-d')) {
                // Handle different Half Day scenarios with automatic punch-out calculation
                if ($status === AttendanceEmployee::STATUS_HALF_DAY) {
                    // Rule 1: Regular Half Day - set punch-out to exactly 4.5 hours
                    $check->clock_in = $clockIn;
                    $check->status = $status;
                    $this->handleHalfDayStatus($check, $request->date);
                } elseif ($status === AttendanceEmployee::STATUS_HALF_DAY_PUNCH_MISS) {
                    // Rule 2: Half Day (Punch Miss) - auto-calculate punch-out to 4.5 hours
                    $check->clock_in = $clockIn;
                    $check->status = $status;
                    $this->handleHalfDayPunchMiss($check, $request->date);
                } elseif ($status === AttendanceEmployee::STATUS_HALF_DAY_LATE) {
                    // Rule 3: Half Day (Late) - auto-calculate punch-out to 4.5 hours
                    $check->clock_in = $clockIn;
                    $check->status = $status;
                    $this->handleHalfDayLateMark($check, $request->date);
                } else {
                    $check->update([
                        'late' => $late,
                        'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                        'overtime' => $overtime,
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'status' => $status
                    ]);
                }

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
            } else {
                return redirect()->route('attendanceemployee.index')->with('error', __('You can only update current day attendance.'));
            }
        }

        $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();

        $startTime = Utility::getValByName('company_start_time');
        $endTime   = Utility::getValByName('company_end_time');
        if (Auth::user()->type == 'employee') {

            $date = date("Y-m-d");
            $time = date("H:i:s");

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (time() > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee['clock_out']     = $time;
            $attendanceEmployee['early_leaving'] = $earlyLeaving;
            $attendanceEmployee['overtime']      = $overtime;

            if (!empty($request->date)) {
                $attendanceEmployee['date']       =  $request->date;
            }
            AttendanceEmployee::where('id', $id)->update($attendanceEmployee);

            return redirect()->route('dashboard')->with('success', __('Employee successfully clock Out.'));
        } else {
            $date = date("Y-m-d");
            $clockout_time = date("H:i:s");
            //late
            $totalLateSeconds = strtotime($clockout_time) - strtotime($date . $startTime);

            $hours            = abs(floor($totalLateSeconds / 3600));
            $mins             = abs(floor($totalLateSeconds / 60 % 60));
            $secs             = abs(floor($totalLateSeconds % 60));

            $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($clockout_time);
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


            if (strtotime($clockout_time) > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = strtotime($clockout_time) - strtotime($date . $endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee                = AttendanceEmployee::find($id);
            $attendanceEmployee->clock_out     = $clockout_time;
            $attendanceEmployee->late          = $late;
            $attendanceEmployee->early_leaving = $earlyLeaving;
            $attendanceEmployee->overtime      = $overtime;
            $attendanceEmployee->total_rest    = '00:00:00';

            $attendanceEmployee->save();

            return redirect()->back()->with('success', __('Employee attendance successfully updated.'));
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('Delete Attendance')) {
            $attendance = AttendanceEmployee::where('id', $id)->first();

            $attendance->delete();

            return redirect()->route('attendanceemployee.index')->with('success', __('Attendance successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function attendance(Request $request)
    // {
    //     $settings = Utility::settings();

    //     if ($settings['ip_restrict'] == 'on') {
    //         $userIp = request()->ip();
    //         $ip     = IpRestrict::where('created_by', \Auth::user()->creatorId())->whereIn('ip', [$userIp])->first();
    //         if (!empty($ip)) {
    //             return redirect()->back()->with('error', __('this ip is not allowed to clock in & clock out.'));
    //         }
    //     }

    //     $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
    //     $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();
    //     if (empty($todayAttendance)) {

    //         $startTime = Utility::getValByName('company_start_time');
    //         $endTime   = Utility::getValByName('company_end_time');

    //         $attendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', $employeeId)->where('clock_out', '=', '00:00:00')->first();

    //         if ($attendance != null) {
    //             $attendance            = AttendanceEmployee::find($attendance->id);
    //             $attendance->clock_out = $endTime;
    //             $attendance->save();
    //         }

    //         $date = date("Y-m-d");
    //         $time = date("H:i:s");

    //         //late
    //         $totalLateSeconds = time() - strtotime($date . $startTime);
    //         $hours            = floor($totalLateSeconds / 3600);
    //         $mins             = floor($totalLateSeconds / 60 % 60);
    //         $secs             = floor($totalLateSeconds % 60);
    //         $late             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


    //         $checkDb = AttendanceEmployee::where('employee_id', '=', \Auth::user()->id)->get()->toArray();


    //         if (empty($checkDb)) {
    //             $employeeAttendance                = new AttendanceEmployee();
    //             $employeeAttendance->employee_id   = $employeeId;
    //             $employeeAttendance->date          = $date;
    //             $employeeAttendance->status        = 'Present';
    //             $employeeAttendance->clock_in      = $time;
    //             $employeeAttendance->clock_out     = '00:00:00';
    //             $employeeAttendance->late          = $late;
    //             $employeeAttendance->early_leaving = '00:00:00';
    //             $employeeAttendance->overtime      = '00:00:00';
    //             $employeeAttendance->total_rest    = '00:00:00';
    //             $employeeAttendance->created_by    = \Auth::user()->id;

    //             $employeeAttendance->save();

    //             return redirect()->route('dashboard')->with('success', __('Employee Successfully Clock In.'));
    //         }
    //         foreach ($checkDb as $check) {


    //             $employeeAttendance                = new AttendanceEmployee();
    //             $employeeAttendance->employee_id   = $employeeId;
    //             $employeeAttendance->date          = $date;
    //             $employeeAttendance->status        = 'Present';
    //             $employeeAttendance->clock_in      = $time;
    //             $employeeAttendance->clock_out     = '00:00:00';
    //             $employeeAttendance->late          = $late;
    //             $employeeAttendance->early_leaving = '00:00:00';
    //             $employeeAttendance->overtime      = '00:00:00';
    //             $employeeAttendance->total_rest    = '00:00:00';
    //             $employeeAttendance->created_by    = \Auth::user()->id;

    //             $employeeAttendance->save();

    //             return redirect()->route('dashboard')->with('success', __('Employee Successfully Clock In.'));
    //         }
    //     } else {
    //         return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
    //     }
    // }

    
    public function bulkAttendance(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            $employees = [];
            if (!empty($request->branch) && !empty($request->department)) {
                $terminatedEmployees = \App\Models\Termination::pluck('employee_id')->toArray();
                $employees = Employee::where('created_by', \Auth::user()->creatorId())
                    ->whereNotIn('id', $terminatedEmployees)
                    ->where('branch_id', $request->branch)
                    ->where('department_id', $request->department)
                    ->get();
            }

            return view('attendance.bulk', compact('employees', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulkAttendanceData(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {
            if (!empty($request->branch) && !empty($request->department)) {
                $startTime = Utility::getValByName('company_start_time');
                $endTime   = Utility::getValByName('company_end_time');
                $date      = $request->date;

                $employees = $request->employee_id;
                $atte      = [];
                foreach ($employees as $employee) {
                    $present = 'present-' . $employee;
                    $in      = 'in-' . $employee;
                    $out     = 'out-' . $employee;
                    $atte[]  = $present;
                    if ($request->$present == 'on') {

                        $in  = date("H:i:s", strtotime($request->$in));
                        $out = date("H:i:s", strtotime($request->$out));

                        // Calculate late time based on department-specific punch-in time
                        $late = $this->calculateLateTime($in, $date, $employee);

                        //early Leaving
                        $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($date . ' ' . $out);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        // Calculate status using new rules
                        $status = $this->calculateAttendanceStatusWithNewRules($in, $out, $date, $employee);

                        if (strtotime($out) > strtotime($endTime)) {
                            //Overtime
                            $totalOvertimeSeconds = strtotime($out) - strtotime($endTime);
                            $hours                = floor($totalOvertimeSeconds / 3600);
                            $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                            $secs                 = floor($totalOvertimeSeconds % 60);
                            $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } else {
                            $overtime = '00:00:00';
                        }

                        $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                        if (!empty($attendance)) {
                            $employeeAttendance = $attendance;
                        } else {
                            $employeeAttendance              = new AttendanceEmployee();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by  = \Auth::user()->creatorId();
                        }

                        $employeeAttendance->date          = $request->date;
                        $employeeAttendance->status        = $status; // Updated status
                        $employeeAttendance->clock_in      = $in;
                        $employeeAttendance->clock_out     = $out;
                        $employeeAttendance->late          = $late;
                        $employeeAttendance->early_leaving = ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00';
                        $employeeAttendance->overtime      = $overtime;
                        $employeeAttendance->total_rest    = '00:00:00';
                        $employeeAttendance->save();
                    } else {
                        $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                        if (!empty($attendance)) {
                            $employeeAttendance = $attendance;
                        } else {
                            $employeeAttendance              = new AttendanceEmployee();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by  = \Auth::user()->creatorId();
                        }

                        $employeeAttendance->status        = AttendanceEmployee::STATUS_ABSENT;
                        $employeeAttendance->date          = $request->date;
                        $employeeAttendance->clock_in      = '00:00:00';
                        $employeeAttendance->clock_out     = '00:00:00';
                        $employeeAttendance->late          = '00:00:00';
                        $employeeAttendance->early_leaving = '00:00:00';
                        $employeeAttendance->overtime      = '00:00:00';
                        $employeeAttendance->total_rest    = '00:00:00';
                        $employeeAttendance->save();
                    }
                }

                return redirect()->back()->with('success', __('Employee attendance successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Branch & department field required.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function importFile()
    {
        return view('attendance.import');
    }

    public function import(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:csv,txt,xlsx',
        ];
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $attendance = (new AttendanceImport())->toArray(request()->file('file'))[0];

        $email_data = [];
        foreach ($attendance as $key => $employee) {
            if ($key != 0) {
                echo "<pre>";
                if ($employee != null && Employee::where('email', $employee[0])->where('created_by', \Auth::user()->creatorId())->exists()) {
                    $email = $employee[0];
                } else {
                    $email_data[] = $employee[0];
                }
            }
        }
        $totalattendance = count($attendance) - 1;
        $errorArray    = [];

        $startTime = Utility::getValByName('company_start_time');
        $endTime   = Utility::getValByName('company_end_time');

        if (!empty($attendanceData)) {
            $errorArray[] = $attendanceData;
        } else {
            foreach ($attendance as $key => $value) {
                if ($key != 0) {
                    $employeeData = Employee::where('email', $value[0])->where('created_by', \Auth::user()->creatorId())->first();
                    // $employeeId = 0;
                    if (!empty($employeeData)) {
                        $employeeId = $employeeData->id;


                        $clockIn = $value[2];
                        $clockOut = $value[3];

                        if ($clockIn) {
                            $status = "present";
                        } else {
                            $status = "leave";
                        }

                        $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

                        $hours = floor($totalLateSeconds / 3600);
                        $mins  = floor($totalLateSeconds / 60 % 60);
                        $secs  = floor($totalLateSeconds % 60);
                        $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        if (strtotime($clockOut) > strtotime($endTime)) {
                            //Overtime
                            $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                            $hours                = floor($totalOvertimeSeconds / 3600);
                            $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                            $secs                 = floor($totalOvertimeSeconds % 60);
                            $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } else {
                            $overtime = '00:00:00';
                        }

                        $check = AttendanceEmployee::where('employee_id', $employeeId)->where('date', $value[1])->first();
                        if ($check) {
                            $check->update([
                                'late' => $late,
                                'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                                'overtime' => $overtime,
                                'clock_in' => $value[2],
                                'clock_out' => $value[3]
                            ]);
                        } else {
                            $time_sheet = AttendanceEmployee::create([
                                'employee_id' => $employeeId,
                                'date' => $value[1],
                                'status' => $status,
                                'late' => $late,
                                'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                                'overtime' => $overtime,
                                'clock_in' => $value[2],
                                'clock_out' => $value[3],
                                'created_by' => \Auth::user()->id,
                            ]);
                        }
                    }
                } else {
                    $email_data = implode(' And ', $email_data);
                }
            }
            if (!empty($email_data)) {
                return redirect()->back()->with('status', 'this record is not import. ' . '</br>' . $email_data);
            } else {
                if (empty($errorArray)) {
                    $data['status'] = 'success';
                    $data['msg']    = __('Record successfully imported');
                } else {

                    $data['status'] = 'error';
                    $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalattendance . ' ' . 'record');


                    foreach ($errorArray as $errorData) {
                        $errorRecord[] = implode(',', $errorData->toArray());
                    }

                    \Session::put('errorArray', $errorRecord);
                }

                return redirect()->back()->with($data['status'], $data['msg']);
            }
        }
    }

    /**
     * Get real client IP address (not localhost)
     */
    private function getRealClientIp()
    {
        // Check for forwarded IP headers
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    // Validate IP and skip private/local IPs
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        // Fallback to request IP but check if it's localhost
        $ip = request()->ip();
        
        // If it's localhost, try to get external IP
        if ($ip === '127.0.0.1' || $ip === '::1') {
            // Try to get real IP from external service
            $context = stream_context_create([
                'http' => [
                    'timeout' => 2,
                    'user_agent' => 'Mozilla/5.0'
                ]
            ]);
            
            $externalIp = @file_get_contents('https://api.ipify.org?format=text', false, $context);
            if ($externalIp && filter_var($externalIp, FILTER_VALIDATE_IP)) {
                return trim($externalIp);
            }
        }
        
        return $ip;
    }

    public function attendance(Request $request)
    {
        $settings = Utility::settings();

        // IP Restriction Check (updated for IP ranges)
        if (!empty($settings['ip_restrict']) && $settings['ip_restrict'] == 'on') {
            // Get real client IP (not localhost)
            $userIp = $this->getRealClientIp();
            $ipRestrictions = IpRestrict::where('created_by', \Auth::user()->creatorId())->get();
            
            // Debug: Log the IPs being compared
            \Log::info('IP Restriction Check:', [
                'user_ip' => $userIp,
                'allowed_ips' => $ipRestrictions->pluck('ip')->toArray()
            ]);
            
            $isAllowed = false;
            foreach ($ipRestrictions as $ipRestriction) {
                if ($ipRestriction->matchesIp($userIp)) {
                    $isAllowed = true;
                    \Log::info('IP Match found:', ['allowed_ip' => $ipRestriction->ip, 'user_ip' => $userIp]);
                    break;
                }
            }
            
            if (!$isAllowed) {
                \Log::warning('IP Access Denied:', ['user_ip' => $userIp]);
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('This IP is not allowed to clock in & clock out.')
                    ]);
                }
                return redirect()->back()->with('error', __('This IP is not allowed to clock in & clock out.'));
            }
        }

        $employeeId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $employee = Employee::find($employeeId);
        $date = date("Y-m-d");
        $time = date("H:i:s");

        // Check existing attendance
        $todayAttendance = AttendanceEmployee::where('employee_id', $employeeId)
                                            ->where('date', $date)
                                            ->first();

        if (!$todayAttendance) {
            // PUNCH IN LOGIC
            $pendingClockIn = AttendanceEmployee::where('employee_id', $employeeId)
                                            ->where('date', $date)
                                            ->where('clock_in', '!=', '00:00:00')
                                            ->where('clock_out', '00:00:00')
                                            ->first();
            
            if ($pendingClockIn) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Your clock-in is already being processed.')
                    ]);
                }
                return redirect()->back()->with('error', __('Your clock-in is already being processed.'));
            }

            // Calculate late time based on department-specific punch-in time
            $late = $this->calculateLateTime($time, $date, $employeeId);

            $employeeAttendance = new AttendanceEmployee();
            $employeeAttendance->employee_id = $employeeId;
            $employeeAttendance->date = $date;
            $employeeAttendance->status = AttendanceEmployee::STATUS_SINGLE_PUNCH; // Set initial status
            $employeeAttendance->clock_in = $time;
            $employeeAttendance->clock_out = '00:00:00';
            $employeeAttendance->late = $late;
            $employeeAttendance->early_leaving = '00:00:00';
            $employeeAttendance->overtime = '00:00:00';
            $employeeAttendance->total_rest = '00:00:00';
            $employeeAttendance->created_by = \Auth::user()->id;
            
            $employeeAttendance->save();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Employee Successfully Clocked In.'),
                    'clock_in' => $time,
                    'date' => $date
                ]);
            }

            return redirect()->back()->with('success', __('Employee Successfully Clocked In.'));
        } 
        elseif ($todayAttendance->clock_out == '00:00:00') {
            // PUNCH OUT LOGIC
            $clockInTime = strtotime($todayAttendance->clock_in);
            $clockOutTime = strtotime($time);
            $workedSeconds = $clockOutTime - $clockInTime;
            
            // Calculate status using new rules
            $status = $this->calculateAttendanceStatusWithNewRules(
                $todayAttendance->clock_in,
                $time,
                $date,
                $employeeId
            );
            
            $totalWorked = gmdate("H:i:s", $workedSeconds);

            $todayAttendance->clock_out = $time;
            $todayAttendance->overtime = $totalWorked;
            $todayAttendance->status = $status; // Update status based on worked hours

            $todayAttendance->save();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Employee Successfully Clocked Out.'),
                    'clock_out' => $time,
                    'date' => $date
                ]);
            }

            return redirect()->back()->with('success', __('Employee Successfully Clocked Out.'));
        } 

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => __('You have already clocked out today.')
            ]);
        }

        return redirect()->back()->with('error', __('You have already clocked out today.'));
    }


    public function calendar(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance')) {
            $employees = [];
            $selectedEmployee = null;
            $terminatedEmployees = \App\Models\Termination::pluck('employee_id')->toArray();
            $allEmployees = Employee::where('created_by', \Auth::user()->creatorId())
                ->whereNotIn('id', $terminatedEmployees)
                ->get();

            // For employee users - automatically select their own record
            if (\Auth::user()->type == 'employee') {
                $selectedEmployee = Employee::where('user_id', \Auth::user()->id)->first();
                if ($selectedEmployee) {
                    $employees = [$selectedEmployee];
                }
            } 
            // For company users - check if employee is selected
            else {
                if ($request->has('employee_id') && $request->employee_id) {
                    $selectedEmployee = Employee::find($request->employee_id);
                    if ($selectedEmployee) {
                        $employees = [$selectedEmployee];
                    }
                }
            }

            // Get current month and year from request or use current date
            $currentMonth = (int)request()->input('month', date('m'));
            $currentYear = (int)request()->input('year', date('Y'));
            
            // Validate month and year
            if ($currentMonth < 1 || $currentMonth > 12) {
                $currentMonth = (int)date('m');
            }
            if ($currentYear < 2000 || $currentYear > 2100) {
                $currentYear = (int)date('Y');
            }

            $currentDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
            $previousMonth = $currentDate->copy()->subMonth();
            $nextMonth = $currentDate->copy()->addMonth();

            $attendanceData = [];

            // Only process data if we have a selected employee
            if ($selectedEmployee) {
                foreach ($employees as $employee) {
                    // Get attendance records ONLY for the current month
                    $startOfMonth = $currentDate->copy()->startOfMonth()->format('Y-m-d');
                    $endOfMonth = $currentDate->copy()->endOfMonth()->format('Y-m-d');
                    
                    $attendances = DB::table('attendance_employees')
                        ->where('employee_id', $employee->id)
                        ->whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->get()
                        ->map(function ($item) {
                            $date = \Carbon\Carbon::parse($item->date)->format('Y-m-d');
                            return [
                                'date' => $date,
                                'clock_in' => $item->clock_in,
                                'clock_out' => $item->clock_out
                            ];
                        });

                    // Get approved leaves ONLY for the current month
                    $leaves = LocalLeave::where('employee_id', $employee->id)
                        ->where('status', 'Approved')
                        ->where(function($query) use ($startOfMonth, $endOfMonth) {
                            $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                                  ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                                  ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                                      $q->where('start_date', '<=', $startOfMonth)
                                        ->where('end_date', '>=', $endOfMonth);
                                  });
                        })
                        ->get()
                        ->map(function ($item) {
                            return [
                                'start_date' => \Carbon\Carbon::parse($item->start_date)->format('Y-m-d'),
                                'end_date' => \Carbon\Carbon::parse($item->end_date)->format('Y-m-d'),
                                'leave_reason' => $item->leave_reason,
                                'is_lop' => $item->is_lop
                            ];
                        });

                    // Get holidays ONLY for the current month
                    $holidays = \App\Models\Holiday::where('created_by', \Auth::user()->creatorId())
                        ->whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->get()
                        ->map(function ($item) {
                            return [
                                'date' => \Carbon\Carbon::parse($item->date)->format('Y-m-d'),
                                'occasion' => $item->occasion
                            ];
                        });

                    $employeeData = [];

                    // Mark attendance status using the same 3-rule logic as the main system
                    foreach ($attendances as $attendance) {
                        // Use the same status calculation logic as the main attendance system
                        $status = $this->calculateAttendanceStatusWithNewRules(
                            $attendance['clock_in'],
                            $attendance['clock_out'],
                            $attendance['date'],
                            $selectedEmployee->id
                        );
                        
                        // Map status to calendar types
                        $calendarType = 'present'; // default
                        if ($status === AttendanceEmployee::STATUS_ABSENT) {
                            $calendarType = 'absent';
                        } elseif (in_array($status, [
                            AttendanceEmployee::STATUS_HALF_DAY,
                            AttendanceEmployee::STATUS_HALF_DAY_PUNCH_MISS,
                            AttendanceEmployee::STATUS_HALF_DAY_LATE
                        ])) {
                            $calendarType = 'half_day';
                        }
                        
                        // Check if this is a late mark based on department punch-in time
                        $isLateMark = $this->isLateMark($attendance['clock_in'], $selectedEmployee->id);
                        
                        $employeeData[$attendance['date']] = [
                            'type' => $calendarType,
                            'status' => $status, // Add the actual status for display
                            'clock_in' => $attendance['clock_in'],
                            'clock_out' => $attendance['clock_out'],
                            'is_late' => $isLateMark
                        ];
                    }

                    // Mark 'leave' days (only current month)
                    foreach ($leaves as $leave) {
                        $start = \Carbon\Carbon::parse($leave['start_date']);
                        $end = \Carbon\Carbon::parse($leave['end_date']);
                        $monthStart = $currentDate->copy()->startOfMonth();
                        $monthEnd = $currentDate->copy()->endOfMonth();

                        // Only process dates within the current month
                        $processStart = $start->gt($monthStart) ? $start : $monthStart;
                        $processEnd = $end->lt($monthEnd) ? $end : $monthEnd;

                        for ($date = $processStart->copy(); $date->lte($processEnd); $date->addDay()) {
                            $formattedDate = $date->format('Y-m-d');

                            if (!isset($employeeData[$formattedDate])) {
                                $employeeData[$formattedDate] = [
                                    'type' => !empty($leave['is_lop']) ? 'lop' : 'leave',
                                    'reason' => $leave['leave_reason']
                                ];
                            }
                        }
                    }

                    // Mark 'holiday' days (only current month)
                    foreach ($holidays as $holiday) {
                        $formattedDate = $holiday['date'];

                        if (!isset($employeeData[$formattedDate])) {
                            $employeeData[$formattedDate] = [
                                'type' => 'holiday',
                                'reason' => $holiday['occasion']
                            ];
                        }
                    }

                    // Fill in 'absent' ONLY for current month working dates (excluding Saturday=6 and Sunday=0)
                    $monthStart = $currentDate->copy()->startOfMonth();
                    $monthEnd = $currentDate->copy()->endOfMonth();
                    $today = \Carbon\Carbon::today();
                    
                    for ($date = $monthStart->copy(); $date->lte($monthEnd); $date->addDay()) {
                        $dateFormatted = $date->format('Y-m-d');
                        $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 6 = Saturday

                        if (!isset($employeeData[$dateFormatted])) {
                            // Skip Saturday (6) and Sunday (0) — both are Week Off
                            if ($date->lte($today) && $dayOfWeek != 0 && $dayOfWeek != 6) {
                                $employeeData[$dateFormatted] = ['type' => 'absent'];
                            }
                            // Future dates and Week Off days remain unmarked
                        }
                    }

                    // Sort data by date
                    ksort($employeeData);

                    $attendanceData[$employee->id] = [
                        'name' => $employee->name,
                        'data' => $employeeData
                    ];
                }
            }

            return view('attendance.calendar', [
                'attendanceData' => $attendanceData,
                'currentMonth' => $currentMonth,
                'currentYear' => $currentYear,
                'previousMonth' => $previousMonth->format('m'),
                'previousYear' => $previousMonth->format('Y'),
                'nextMonth' => $nextMonth->format('m'),
                'nextYear' => $nextMonth->format('Y'),
                'allEmployees' => $allEmployees,
                'selectedEmployee' => $selectedEmployee
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function teamCalendar(Request $request)
    {
        if (\Auth::user()->type == 'employee') {
            $user = \Auth::user();
            $employee = \App\Models\Employee::where('user_id', '=', $user->id)->first();
            $isManager = false;
            
            if ($employee && $employee->designation) {
                $isManager = (str_contains(strtolower($employee->designation->name), 'manager'));
            }

            if ($isManager && $employee) {
                $employees = [];
                $selectedEmployee = null;
                $terminatedEmployees = \App\Models\Termination::pluck('employee_id')->toArray();
                
                // Manager sees all employees in their department
                $departmentEmployeeIds = \App\Models\Employee::where('department_id', '=', $employee->department_id)
                    ->whereNotIn('id', $terminatedEmployees)
                    ->pluck('id')
                    ->toArray();
                    
                $allEmployees = \App\Models\Employee::whereIn('id', $departmentEmployeeIds)->get();

                if ($request->has('employee_id') && $request->employee_id) {
                    // Make sure the requested employee is in the manager's department
                    if (in_array($request->employee_id, $departmentEmployeeIds)) {
                        $selectedEmployee = \App\Models\Employee::find($request->employee_id);
                        if ($selectedEmployee) {
                            $employees = [$selectedEmployee];
                        }
                    }
                }

                // Get current month and year from request or use current date
                $currentMonth = (int)request()->input('month', date('m'));
                $currentYear = (int)request()->input('year', date('Y'));
                
                // Validate month and year
                if ($currentMonth < 1 || $currentMonth > 12) {
                    $currentMonth = (int)date('m');
                }
                if ($currentYear < 2000 || $currentYear > 2100) {
                    $currentYear = (int)date('Y');
                }

                $currentDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
                $previousMonth = $currentDate->copy()->subMonth();
                $nextMonth = $currentDate->copy()->addMonth();

                $attendanceData = [];

                // Only process data if we have a selected employee
                if ($selectedEmployee) {
                    foreach ($employees as $emp) {
                        // Get attendance records ONLY for the current month
                        $startOfMonth = $currentDate->copy()->startOfMonth()->format('Y-m-d');
                        $endOfMonth = $currentDate->copy()->endOfMonth()->format('Y-m-d');
                        
                        $attendances = \DB::table('attendance_employees')
                            ->where('employee_id', $emp->id)
                            ->whereBetween('date', [$startOfMonth, $endOfMonth])
                            ->get()
                            ->map(function ($item) {
                                $date = \Carbon\Carbon::parse($item->date)->format('Y-m-d');
                                return [
                                    'date' => $date,
                                    'clock_in' => $item->clock_in,
                                    'clock_out' => $item->clock_out
                                ];
                            });

                        // Get approved leaves ONLY for the current month
                        $leaves = \App\Models\Leave::where('employee_id', $emp->id)
                            ->where('status', 'Approved')
                            ->where(function($query) use ($startOfMonth, $endOfMonth) {
                                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                                      ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                                      ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                                          $q->where('start_date', '<=', $startOfMonth)
                                            ->where('end_date', '>=', $endOfMonth);
                                      });
                            })
                            ->get()
                            ->map(function ($item) {
                                return [
                                    'start_date' => \Carbon\Carbon::parse($item->start_date)->format('Y-m-d'),
                                    'end_date' => \Carbon\Carbon::parse($item->end_date)->format('Y-m-d'),
                                    'leave_reason' => $item->leave_reason,
                                    'is_lop' => $item->is_lop
                                ];
                            });

                        // Get holidays ONLY for the current month
                        $holidays = \App\Models\Holiday::where('created_by', \Auth::user()->creatorId())
                            ->whereBetween('date', [$startOfMonth, $endOfMonth])
                            ->get()
                            ->map(function ($item) {
                                return [
                                    'date' => \Carbon\Carbon::parse($item->date)->format('Y-m-d'),
                                    'occasion' => $item->occasion
                                ];
                            });

                        $employeeData = [];

                        // Mark attendance status using the same 3-rule logic as the main system
                        foreach ($attendances as $attendance) {
                            $status = $this->calculateAttendanceStatusWithNewRules(
                                $attendance['clock_in'],
                                $attendance['clock_out'],
                                $attendance['date'],
                                $selectedEmployee->id
                            );
                            
                            $calendarType = 'present';
                            if ($status === AttendanceEmployee::STATUS_ABSENT) {
                                $calendarType = 'absent';
                            } elseif (in_array($status, [
                                AttendanceEmployee::STATUS_HALF_DAY,
                                AttendanceEmployee::STATUS_HALF_DAY_PUNCH_MISS,
                                AttendanceEmployee::STATUS_HALF_DAY_LATE
                            ])) {
                                $calendarType = 'half_day';
                            }
                            
                            $isLateMark = $this->isLateMark($attendance['clock_in'], $selectedEmployee->id);
                            
                            $employeeData[$attendance['date']] = [
                                'type' => $calendarType,
                                'status' => $status,
                                'clock_in' => $attendance['clock_in'],
                                'clock_out' => $attendance['clock_out'],
                                'is_late' => $isLateMark
                            ];
                        }

                        // Mark 'leave' days
                        foreach ($leaves as $leave) {
                            $start = \Carbon\Carbon::parse($leave['start_date']);
                            $end = \Carbon\Carbon::parse($leave['end_date']);
                            $monthStart = $currentDate->copy()->startOfMonth();
                            $monthEnd = $currentDate->copy()->endOfMonth();

                            $processStart = $start->gt($monthStart) ? $start : $monthStart;
                            $processEnd = $end->lt($monthEnd) ? $end : $monthEnd;

                            for ($date = $processStart->copy(); $date->lte($processEnd); $date->addDay()) {
                                $formattedDate = $date->format('Y-m-d');
                                if (!isset($employeeData[$formattedDate])) {
                                    $employeeData[$formattedDate] = [
                                        'type' => !empty($leave['is_lop']) ? 'lop' : 'leave',
                                        'reason' => $leave['leave_reason']
                                    ];
                                }
                            }
                        }

                        // Mark 'holiday' days
                        foreach ($holidays as $holiday) {
                            $formattedDate = $holiday['date'];
                            if (!isset($employeeData[$formattedDate])) {
                                $employeeData[$formattedDate] = [
                                    'type' => 'holiday',
                                    'reason' => $holiday['occasion']
                                ];
                            }
                        }

                        // Fill in 'absent' ONLY for current month working dates (excluding Saturday=6 and Sunday=0)
                        $monthStart = $currentDate->copy()->startOfMonth();
                        $monthEnd = $currentDate->copy()->endOfMonth();
                        $today = \Carbon\Carbon::today();
                        
                        for ($date = $monthStart->copy(); $date->lte($monthEnd); $date->addDay()) {
                            $dateFormatted = $date->format('Y-m-d');
                            $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 6 = Saturday

                            if (!isset($employeeData[$dateFormatted])) {
                                // Skip Saturday (6) and Sunday (0) — both are Week Off
                                if ($date->lte($today) && $dayOfWeek != 0 && $dayOfWeek != 6) {
                                    $employeeData[$dateFormatted] = ['type' => 'absent'];
                                }
                            }
                        }

                        ksort($employeeData);

                        $attendanceData[$emp->id] = [
                            'name' => $emp->name,
                            'data' => $employeeData
                        ];
                    }
                }

                return view('attendance.team_calendar', [
                    'attendanceData' => $attendanceData,
                    'currentMonth' => $currentMonth,
                    'currentYear' => $currentYear,
                    'previousMonth' => $previousMonth->format('m'),
                    'previousYear' => $previousMonth->format('Y'),
                    'nextMonth' => $nextMonth->format('m'),
                    'nextYear' => $nextMonth->format('Y'),
                    'allEmployees' => $allEmployees,
                    'selectedEmployee' => $selectedEmployee
                ]);

            } else {
                return redirect()->back()->with('error', __('Permission denied. Only managers can view the team calendar.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied. Only managers can view the team calendar.'));
        }
    }

    public function export(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance')) {
            // Get the same filtered data as the index method
            if (\Auth::user()->type == 'employee') {
                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
                $query = AttendanceEmployee::where('employee_id', $emp);
            } else {
                $terminatedEmployees = \App\Models\Termination::pluck('employee_id')->toArray();
                $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId())
                    ->whereNotIn('id', $terminatedEmployees);
                
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }

                if (!empty($request->employee)) {
                    $employee->where('id', $request->employee);
                }

                $employee = $employee->get()->pluck('id');
                $query = AttendanceEmployee::whereIn('employee_id', $employee);
            }
            
            // Apply date filters - match exactly what index method does
            if ($request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year = date('Y', strtotime($request->month));
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
                $query->whereBetween('date', [$start_date, $end_date]);
            } elseif ($request->type == 'daily' && !empty($request->date)) {
                $start_date = $request->date;
                $end_date = $request->date;
                $query->where('date', $request->date);
            } else {
                // Default to current month
                $month = date('m');
                $year = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
                $query->whereBetween('date', [$start_date, $end_date]);
            }

            $attendances = $query->orderBy('date', 'asc')
                                ->orderBy('clock_in', 'asc')
                                ->get();

            // Get all dates in the selected period
            $dates = [];
            $current = \Carbon\Carbon::parse($start_date);
            $end = \Carbon\Carbon::parse($end_date);
            
            while ($current <= $end) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }

            // Get all employees in the filtered set
            $employeeIds = $attendances->pluck('employee_id')->unique();
            $employees = Employee::whereIn('id', $employeeIds)
                                ->with('user')
                                ->get();

            // Check if single employee is selected - use enhanced export format
            $isSingleEmployee = !empty($request->employee) && count($employeeIds) == 1;
            
            // Group attendance by employee and date
            $attendanceData = [];
            $totalMinutesByEmployee = [];
            $totalWorkingDaysByEmployee = [];
            $standardMinutesPerDay = 9 * 60; // 540 minutes
            
            foreach ($attendances as $attendance) {
                $clockIn = $attendance->clock_in;
                $clockOut = $attendance->clock_out;
                $workedHours = $this->calculateWorkedHours($clockIn, $clockOut);
                
                // Calculate minutes for enhanced format
                $dayMinutes = 0;
                if ($clockIn != '00:00:00' && $clockOut != '00:00:00') {
                    try {
                        $date = $attendance->date;
                        $inTime = \Carbon\Carbon::parse($date . ' ' . $clockIn);
                        $outTime = \Carbon\Carbon::parse($date . ' ' . $clockOut);
                        
                        if ($outTime->lt($inTime)) {
                            $outTime->addDay();
                        }
                        
                        $dayMinutes = $outTime->diffInMinutes($inTime);
                        if (!isset($totalMinutesByEmployee[$attendance->employee_id])) {
                            $totalMinutesByEmployee[$attendance->employee_id] = 0;
                            $totalWorkingDaysByEmployee[$attendance->employee_id] = 0;
                        }
                        $totalMinutesByEmployee[$attendance->employee_id] += $dayMinutes;
                        $totalWorkingDaysByEmployee[$attendance->employee_id]++;
                    } catch (\Exception $e) {
                        $dayMinutes = 0;
                    }
                }
                
                $attendanceData[$attendance->employee_id][$attendance->date] = [
                    'status' => $attendance->status,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'total' => $workedHours,
                    'minutes' => $dayMinutes
                ];
            }

            // Fetch Holidays
            $holidays = \App\Models\Holiday::where('created_by', \Auth::user()->creatorId())
                ->whereBetween('date', [$start_date, $end_date])
                ->get()
                ->pluck('occasion', 'date')
                ->toArray();
                
            // Fetch Leaves
            $leaves = LocalLeave::whereIn('employee_id', $employeeIds)
                ->where('status', 'Approved')
                ->where(function($query) use ($start_date, $end_date) {
                    $query->whereBetween('start_date', [$start_date, $end_date])
                          ->orWhereBetween('end_date', [$start_date, $end_date])
                          ->orWhere(function($q) use ($start_date, $end_date) {
                              $q->where('start_date', '<=', $start_date)
                                ->where('end_date', '>=', $end_date);
                          });
                })
                ->get();

            // Populate Leaves and Holidays into attendanceData
            foreach ($employeeIds as $empId) {
                if (!isset($attendanceData[$empId])) {
                    $attendanceData[$empId] = [];
                }
                
                // Add Holidays
                foreach ($holidays as $date => $occasion) {
                    if (!isset($attendanceData[$empId][$date])) {
                        $attendanceData[$empId][$date] = [
                            'status' => 'Holiday',
                            'clock_in' => '-',
                            'clock_out' => '-',
                            'total' => '-'
                        ];
                    }
                }
                
                // Add Leaves
                foreach ($leaves as $leave) {
                    if ($leave->employee_id == $empId) {
                        $start = \Carbon\Carbon::parse($leave->start_date);
                        $end = \Carbon\Carbon::parse($leave->end_date);
                        $periodStart = \Carbon\Carbon::parse($start_date);
                        $periodEnd = \Carbon\Carbon::parse($end_date);
                        
                        $processStart = $start->gt($periodStart) ? $start : $periodStart;
                        $processEnd = $end->lt($periodEnd) ? $end : $periodEnd;
                        
                        for ($d = $processStart->copy(); $d->lte($processEnd); $d->addDay()) {
                            $dateStr = $d->format('Y-m-d');
                            if (!isset($attendanceData[$empId][$dateStr])) {
                                $attendanceData[$empId][$dateStr] = [
                                    'status' => !empty($leave->is_lop) ? 'LOP' : 'Leave',
                                    'clock_in' => '-',
                                    'clock_out' => '-',
                                    'total' => '-'
                                ];
                            }
                        }
                    }
                }
            }
            
            // Calculate summary data for single employee export
            $summaryData = [];
            if ($isSingleEmployee && count($employees) > 0) {
                $employee = $employees->first();
                $totalMinutes = $totalMinutesByEmployee[$employee->id] ?? 0;
                $totalWorkingDays = $totalWorkingDaysByEmployee[$employee->id] ?? 0;
                
                $totalHours = floor($totalMinutes / 60);
                $totalMins = $totalMinutes % 60;
                $totalHoursFormatted = $totalHours . 'h ' . $totalMins . 'm';
                
                $totalMonthDays = count($dates);
                $requiredMinutes = $totalMonthDays * $standardMinutesPerDay;
                $requiredHours = floor($requiredMinutes / 60);
                $requiredMins = $requiredMinutes % 60;
                $requiredHoursFormatted = $requiredHours . 'h ' . $requiredMins . 'm';
                
                $diffMinutes = $totalMinutes - $requiredMinutes;
                $extraShortHours = '';
                if ($diffMinutes != 0) {
                    $sign = $diffMinutes > 0 ? '+' : '-';
                    $absMinutes = abs($diffMinutes);
                    $diffHours = floor($absMinutes / 60);
                    $diffMins = $absMinutes % 60;
                    if ($diffMins > 0) {
                        $extraShortHours = $sign . $diffHours . 'h ' . $diffMins . 'm';
                    } else {
                        $extraShortHours = $sign . $diffHours . 'h';
                    }
                } else {
                    $extraShortHours = '0h';
                }
                
                $summaryData = [
                    'totalHoursFormatted' => $totalHoursFormatted,
                    'requiredHoursFormatted' => $requiredHoursFormatted,
                    'extraShortHours' => $extraShortHours,
                    'totalWorkingDays' => $totalWorkingDays
                ];
            }

            // Generate Excel file
            $fileName = 'attendance_' . date('Y-m-d') . '.xlsx';
            if ($isSingleEmployee && count($employees) > 0) {
                $employeeName = str_replace(' ', '_', $employees->first()->name);
                $monthYear = \Carbon\Carbon::parse($start_date)->format('M_Y');
                $fileName = 'attendance_' . $employeeName . '_' . $monthYear . '.xlsx';
            }
            
            return \Excel::download(new class($dates, $employees, $attendanceData, $start_date, $end_date, $isSingleEmployee, $summaryData) implements \Maatwebsite\Excel\Concerns\FromView, \Maatwebsite\Excel\Concerns\WithStyles {
                private $dates;
                private $employees;
                private $attendanceData;
                private $start_date;
                private $end_date;
                private $isSingleEmployee;
                private $summaryData;

                public function __construct($dates, $employees, $attendanceData, $start_date, $end_date, $isSingleEmployee, $summaryData)
                {
                    $this->dates = $dates;
                    $this->employees = $employees;
                    $this->attendanceData = $attendanceData;
                    $this->start_date = $start_date;
                    $this->end_date = $end_date;
                    $this->isSingleEmployee = $isSingleEmployee;
                    $this->summaryData = $summaryData;
                }

                public function view(): \Illuminate\View\View
                {
                    // Use enhanced export for single employee, regular export for multiple
                    if ($this->isSingleEmployee && !empty($this->summaryData)) {
                        return view('attendance.export_employee', [
                            'dates' => $this->dates,
                            'employees' => $this->employees,
                            'attendanceData' => $this->attendanceData,
                            'start_date' => $this->start_date,
                            'end_date' => $this->end_date,
                            'totalHoursFormatted' => $this->summaryData['totalHoursFormatted'],
                            'requiredHoursFormatted' => $this->summaryData['requiredHoursFormatted'],
                            'extraShortHours' => $this->summaryData['extraShortHours'],
                            'totalWorkingDays' => $this->summaryData['totalWorkingDays']
                        ]);
                    } else {
                        return view('attendance.export', [
                            'dates' => $this->dates,
                            'employees' => $this->employees,
                            'attendanceData' => $this->attendanceData,
                            'start_date' => $this->start_date,
                            'end_date' => $this->end_date
                        ]);
                    }
                }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    // Apply borders to all cells
                    $lastColumn = count($this->dates) + 1;
                    $lastRow = (count($this->employees) * 50) + 2;
                    
                    $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumn) . $lastRow)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    
                    // Center align all cells
                    $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumn) . $lastRow)
                        ->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }
            }, $fileName);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function exportEmployee(Request $request, $employeeId)
    {
        if (\Auth::user()->can('Manage Attendance') || \Auth::user()->type == 'employee') {
            // For employees, they can only export their own attendance
            if (\Auth::user()->type == 'employee') {
                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
                if ($emp != $employeeId) {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
                $employee = Employee::where('id', $employeeId)->first();
            } else {
                // Verify employee belongs to the creator
                $employee = Employee::where('id', $employeeId)
                    ->where('created_by', \Auth::user()->creatorId())
                    ->first();
            }
            
            if (!$employee) {
                return redirect()->back()->with('error', __('Employee not found.'));
            }
            
            // Get attendance for this specific employee
            $query = AttendanceEmployee::where('employee_id', $employeeId);
            
            // Apply date filters from request
            if ($request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year = date('Y', strtotime($request->month));
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
            } elseif ($request->type == 'daily' && !empty($request->date)) {
                $start_date = $request->date;
                $end_date = $request->date;
            } else {
                $month = date('m');
                $year = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
            }
            
            $query->whereBetween('date', [$start_date, $end_date]);
            
            $attendances = $query->orderBy('date', 'asc')
                                ->orderBy('clock_in', 'asc')
                                ->get();
            
            // Get all dates in the selected period
            $dates = [];
            $current = \Carbon\Carbon::parse($start_date);
            $end = \Carbon\Carbon::parse($end_date);
            
            while ($current <= $end) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }
            
            // Get the employee
            $employees = collect([$employee]);
            
            // Group attendance by date and calculate totals
            $attendanceData = [];
            $totalMinutes = 0;
            $totalWorkingDays = 0;
            $standardHoursPerDay = 9; // 9 hours per day
            $standardMinutesPerDay = $standardHoursPerDay * 60; // 540 minutes
            
            foreach ($attendances as $attendance) {
                $clockIn = $attendance->clock_in;
                $clockOut = $attendance->clock_out;
                $workedHours = $this->calculateWorkedHours($clockIn, $clockOut);
                
                // Calculate minutes worked for this day
                $dayMinutes = 0;
                if ($clockIn != '00:00:00' && $clockOut != '00:00:00') {
                    try {
                        $date = $attendance->date;
                        $inTime = \Carbon\Carbon::parse($date . ' ' . $clockIn);
                        $outTime = \Carbon\Carbon::parse($date . ' ' . $clockOut);
                        
                        if ($outTime->lt($inTime)) {
                            $outTime->addDay();
                        }
                        
                        $dayMinutes = $outTime->diffInMinutes($inTime);
                        $totalMinutes += $dayMinutes;
                        $totalWorkingDays++;
                    } catch (\Exception $e) {
                        $dayMinutes = 0;
                    }
                }
                
                $attendanceData[$attendance->employee_id][$attendance->date] = [
                    'status' => $attendance->status,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'total' => $workedHours,
                    'minutes' => $dayMinutes
                ];
            }
            
            // Calculate monthly totals
            $totalHours = floor($totalMinutes / 60);
            $totalMins = $totalMinutes % 60;
            $totalHoursFormatted = $totalHours . 'h ' . $totalMins . 'm';
            
            // Calculate required hours (total month days * 9 hours)
            $totalMonthDays = count($dates);
            $requiredMinutes = $totalMonthDays * $standardMinutesPerDay;
            $requiredHours = floor($requiredMinutes / 60);
            $requiredMins = $requiredMinutes % 60;
            $requiredHoursFormatted = $requiredHours . 'h ' . $requiredMins . 'm';
            
            // Calculate extra/short hours
            $diffMinutes = $totalMinutes - $requiredMinutes;
            $extraShortHours = '';
            if ($diffMinutes != 0) {
                $sign = $diffMinutes > 0 ? '+' : '-';
                $absMinutes = abs($diffMinutes);
                $diffHours = floor($absMinutes / 60);
                $diffMins = $absMinutes % 60;
                if ($diffMins > 0) {
                    $extraShortHours = $sign . $diffHours . 'h ' . $diffMins . 'm';
                } else {
                    $extraShortHours = $sign . $diffHours . 'h';
                }
            } else {
                $extraShortHours = '0h';
            }
            
            // Generate Excel file with employee name in filename
            $employeeName = str_replace(' ', '_', $employee->name);
            $monthYear = \Carbon\Carbon::parse($start_date)->format('M_Y');
            $fileName = 'attendance_' . $employeeName . '_' . $monthYear . '.xlsx';
            
            return \Excel::download(new class($dates, $employees, $attendanceData, $start_date, $end_date, $totalHoursFormatted, $requiredHoursFormatted, $extraShortHours, $totalWorkingDays) implements \Maatwebsite\Excel\Concerns\FromView, \Maatwebsite\Excel\Concerns\WithStyles {
                private $dates;
                private $employees;
                private $attendanceData;
                private $start_date;
                private $end_date;
                private $totalHoursFormatted;
                private $requiredHoursFormatted;
                private $extraShortHours;
                private $totalWorkingDays;
                
                public function __construct($dates, $employees, $attendanceData, $start_date, $end_date, $totalHoursFormatted, $requiredHoursFormatted, $extraShortHours, $totalWorkingDays)
                {
                    $this->dates = $dates;
                    $this->employees = $employees;
                    $this->attendanceData = $attendanceData;
                    $this->start_date = $start_date;
                    $this->end_date = $end_date;
                    $this->totalHoursFormatted = $totalHoursFormatted;
                    $this->requiredHoursFormatted = $requiredHoursFormatted;
                    $this->extraShortHours = $extraShortHours;
                    $this->totalWorkingDays = $totalWorkingDays;
                }
                
                public function view(): \Illuminate\View\View
                {
                    return view('attendance.export_employee', [
                        'dates' => $this->dates,
                        'employees' => $this->employees,
                        'attendanceData' => $this->attendanceData,
                        'start_date' => $this->start_date,
                        'end_date' => $this->end_date,
                        'totalHoursFormatted' => $this->totalHoursFormatted,
                        'requiredHoursFormatted' => $this->requiredHoursFormatted,
                        'extraShortHours' => $this->extraShortHours,
                        'totalWorkingDays' => $this->totalWorkingDays
                    ]);
                }
                
                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    // Apply borders to all cells
                    $lastColumn = count($this->dates) + 1;
                    $lastRow = (count($this->employees) * 10) + 15; // Adjusted for summary rows
                    
                    $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumn) . $lastRow)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    
                    // Center align all cells
                    $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumn) . $lastRow)
                        ->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }
            }, $fileName);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    private function calculateWorkedHours($clockIn, $clockOut)
    {
        if ($clockIn == '00:00:00' || $clockOut == '00:00:00') {
            return '00:00';
        }
        
        $start = \Carbon\Carbon::parse($clockIn);
        $end = \Carbon\Carbon::parse($clockOut);
        
        $diff = $start->diff($end);
        
        return sprintf('%02d:%02d', $diff->h, $diff->i);
    }

    /**
     * Check if clock-in time is considered a late mark based on department settings
     */
    private function isLateMark($clockIn, $employeeId = null)
    {
        if (empty($clockIn) || $clockIn == '00:00:00') {
            return false;
        }
        
        // Use department-specific punch-in time if employee ID is provided
        if ($employeeId) {
            return AttendanceEmployee::isLateMarkForEmployee($employeeId, $clockIn);
        }
        
        // Fallback to default time
        $lateMarkTime = AttendanceEmployee::LATE_MARK_TIME;
        return strtotime($clockIn) > strtotime($lateMarkTime);
    }

    /**
     * Calculate late time based on department-specific punch-in time
     */
    private function calculateLateTime($clockIn, $date, $employeeId = null)
    {
        if (empty($clockIn) || $clockIn == '00:00:00') {
            return '00:00:00';
        }
        
        // Get department-specific punch-in time if employee ID is provided
        if ($employeeId) {
            $lateMarkTime = AttendanceEmployee::getEmployeePunchInTime($employeeId);
        } else {
            $lateMarkTime = AttendanceEmployee::LATE_MARK_TIME; // Fallback to default
        }
        
        $expectedTime = $date . ' ' . $lateMarkTime;
        $actualTime = $date . ' ' . $clockIn;
        
        $totalLateSeconds = max(strtotime($actualTime) - strtotime($expectedTime), 0);
        
        $hours = floor($totalLateSeconds / 3600);
        $mins = floor($totalLateSeconds / 60 % 60);
        $secs = $totalLateSeconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
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
                return $this->isLateMark($attendance->clock_in, $employeeId);
            });
        
        return $lateMarks->count();
    }

    /**
     * Calculate attendance status with new rules for Half Day scenarios
     * 
     * Rules:
     * 1. If worked ≤ 4.5 hours OR punch-out within 4.5 hours → "Half Day"
     * 2. If punch-out is missing → "Half Day (Punch Miss)" with auto 4.5h calculation
     * 3. If 4th+ late mark → "Half Day (Late)" with auto 4.5h calculation
     */
    private function calculateAttendanceStatusWithNewRules($clockIn, $clockOut, $date, $employeeId)
    {
        // If no clock in at all, return Absent
        if (empty($clockIn) || $clockIn == '00:00:00') {
            return AttendanceEmployee::STATUS_ABSENT;
        }
        
        // Check if this is a late mark
        $isLateMark = AttendanceEmployee::isLateMarkForEmployee($employeeId, $clockIn);
        
        // Count late marks for this employee in the month (excluding current day)
        $lateMarksCount = $this->countLateMarksInMonthExcludingCurrent($employeeId, $date);
        
        // Rule 2: If clocked in but no clock out, return Half Day (Punch Miss)
        if (empty($clockOut) || $clockOut == '00:00:00') {
            return AttendanceEmployee::STATUS_HALF_DAY_PUNCH_MISS;
        }
        
        // Calculate total worked hours
        $start = Carbon::parse($date . ' ' . $clockIn);
        $end = Carbon::parse($date . ' ' . $clockOut);
        
        // Handle case where clock out might be next day
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        $totalMinutes = $end->diffInMinutes($start);
        $workedHours = $totalMinutes / 60;
        
        // Rule 3: If 4th+ late mark, return Half Day (Late)
        if ($isLateMark && $lateMarksCount >= AttendanceEmployee::MAX_LATE_MARKS_PER_MONTH) {
            return AttendanceEmployee::STATUS_HALF_DAY_LATE;
        }
        
        // Rule 1: If worked ≤ 4.5 hours OR punch-out within 4.5 hours, return Half Day
        if ($totalMinutes <= 270) { // 4.5 hours = 270 minutes
            // For late marks with ≤ 4.5 hours, still return Half Day (not Half Day Late)
            return AttendanceEmployee::STATUS_HALF_DAY;
        }
        
        // For employees who worked > 4.5 hours
        if ($isLateMark) {
            // First 3 late marks with > 4.5 hours - Present (Late)
            return AttendanceEmployee::STATUS_PRESENT_LATE;
        }
        
        // If punch-out is after 4.5 hours and not late, mark as Present
        return AttendanceEmployee::STATUS_PRESENT;
    }


    /**
     * Handle Half Day (Punch Miss) status by calculating 4.5 hours and updating attendance
     */
    private function handleHalfDayPunchMiss($attendance, $date)
    {
        // Calculate 4.5 hours from punch-in time
        $clockInTime = Carbon::parse($date . ' ' . $attendance->clock_in);
        $calculatedClockOut = $clockInTime->copy()->addHours(4)->addMinutes(30);
        
        // If calculated time goes past midnight (next day), cap it at end of day (23:59:59)
        $endOfDay = Carbon::parse($date . ' 23:59:59');
        if ($calculatedClockOut->gt($endOfDay)) {
            $calculatedClockOut = $endOfDay;
        }
        
        // Format as H:i:s
        $clockOutTime = $calculatedClockOut->format('H:i:s');
        
        // Update attendance record
        $attendance->clock_out = $clockOutTime;
        $attendance->status = AttendanceEmployee::STATUS_HALF_DAY_PUNCH_MISS;
        
        // Calculate early leaving (if applicable)
        $endTime = Utility::getValByName('company_end_time');
        if ($endTime) {
            $expectedEndTime = Carbon::parse($date . ' ' . $endTime);
            if ($calculatedClockOut->lt($expectedEndTime)) {
                $totalEarlyLeavingSeconds = $expectedEndTime->diffInSeconds($calculatedClockOut);
                $hours = floor($totalEarlyLeavingSeconds / 3600);
                $mins = floor(($totalEarlyLeavingSeconds % 3600) / 60);
                $secs = $totalEarlyLeavingSeconds % 60;
                $attendance->early_leaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $attendance->early_leaving = '00:00:00';
            }
        }
        
        // Set overtime to 00:00:00 (no overtime for half day)
        $attendance->overtime = '00:00:00';
        
        $attendance->save();
        
        return $attendance;
    }

    /**
     * Handle Half Day status by calculating 4.5 hours and updating attendance
     */
    private function handleHalfDayStatus($attendance, $date)
    {
        // Calculate 4.5 hours from punch-in time
        $clockInTime = Carbon::parse($date . ' ' . $attendance->clock_in);
        $calculatedClockOut = $clockInTime->copy()->addHours(4)->addMinutes(30);
        
        // If calculated time goes past midnight (next day), cap it at end of day (23:59:59)
        $endOfDay = Carbon::parse($date . ' 23:59:59');
        if ($calculatedClockOut->gt($endOfDay)) {
            $calculatedClockOut = $endOfDay;
        }
        
        // Format as H:i:s
        $clockOutTime = $calculatedClockOut->format('H:i:s');
        
        // Update attendance record
        $attendance->clock_out = $clockOutTime;
        $attendance->status = AttendanceEmployee::STATUS_HALF_DAY;
        
        // Calculate early leaving (if applicable)
        $endTime = Utility::getValByName('company_end_time');
        if ($endTime) {
            $expectedEndTime = Carbon::parse($date . ' ' . $endTime);
            if ($calculatedClockOut->lt($expectedEndTime)) {
                $totalEarlyLeavingSeconds = $expectedEndTime->diffInSeconds($calculatedClockOut);
                $hours = floor($totalEarlyLeavingSeconds / 3600);
                $mins = floor(($totalEarlyLeavingSeconds % 3600) / 60);
                $secs = $totalEarlyLeavingSeconds % 60;
                $attendance->early_leaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $attendance->early_leaving = '00:00:00';
            }
        }
        
        // Set overtime to 00:00:00 (no overtime for half day)
        $attendance->overtime = '00:00:00';
        
        $attendance->save();
        
        return $attendance;
    }

    /**
     * Handle 4th+ late mark by calculating 4.5 hours and updating attendance
     */
    private function handleHalfDayLateMark($attendance, $date)
    {
        // Calculate 4.5 hours from punch-in time
        $clockInTime = Carbon::parse($date . ' ' . $attendance->clock_in);
        $calculatedClockOut = $clockInTime->copy()->addHours(4)->addMinutes(30);
        
        // If calculated time goes past midnight (next day), cap it at end of day (23:59:59)
        $endOfDay = Carbon::parse($date . ' 23:59:59');
        if ($calculatedClockOut->gt($endOfDay)) {
            $calculatedClockOut = $endOfDay;
        }
        
        // Format as H:i:s
        $clockOutTime = $calculatedClockOut->format('H:i:s');
        
        // Update attendance record
        $attendance->clock_out = $clockOutTime;
        $attendance->status = AttendanceEmployee::STATUS_HALF_DAY_LATE;
        
        // Calculate early leaving (if applicable)
        $endTime = Utility::getValByName('company_end_time');
        if ($endTime) {
            $expectedEndTime = Carbon::parse($date . ' ' . $endTime);
            if ($calculatedClockOut->lt($expectedEndTime)) {
                $totalEarlyLeavingSeconds = $expectedEndTime->diffInSeconds($calculatedClockOut);
                $hours = floor($totalEarlyLeavingSeconds / 3600);
                $mins = floor(($totalEarlyLeavingSeconds % 3600) / 60);
                $secs = $totalEarlyLeavingSeconds % 60;
                $attendance->early_leaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $attendance->early_leaving = '00:00:00';
            }
        }
        
        // Set overtime to 00:00:00 (no overtime for half day)
        $attendance->overtime = '00:00:00';
        
        $attendance->save();
        
        return $attendance;
    }

    /**
     * Get status abbreviation for export display
     */
    private function getStatusAbbreviation($status)
    {
        $abbreviations = [
            'Present' => 'P',
            'Present (Late)' => 'PL',
            'Late' => 'L',
            'Half Day (Late)' => 'HL',
            'Half Day (Punch Miss)' => 'HP',
            'Half Day' => 'H',
            'Absent' => 'A',
        ];
        
        return $abbreviations[$status] ?? substr($status, 0, 1);
    }

    protected function calculateAttendanceStatus($clockIn, $clockOut, $date, $employeeId = null)
    {
        // Use new rules if employee ID is provided
        if ($employeeId) {
            return $this->calculateAttendanceStatusWithNewRules($clockIn, $clockOut, $date, $employeeId);
        }
        
        // Fallback to old logic if employee ID not provided
        // If no clock in at all, return Absent
        if (empty($clockIn) || $clockIn == '00:00:00') {
            return 'Absent';
        }
        
        // If clocked in but not out, return Half Day (per new rules)
        if (empty($clockOut) || $clockOut == '00:00:00') {
            return AttendanceEmployee::STATUS_HALF_DAY;
        }
        
        // Calculate total worked time
        $start = \Carbon\Carbon::parse($date . ' ' . $clockIn);
        $end = \Carbon\Carbon::parse($date . ' ' . $clockOut);
        $totalMinutes = $end->diffInMinutes($start);
        
        // If punch-out is within 4.5 hours from punch-in, mark as Half Day
        if ($totalMinutes < 270) { // 4.5 hours = 270 minutes
            return 'Half Day';
        }
        
        // If punch-out is after 4.5 hours, mark as Present
        return 'Present';
    }

    /**
     * Get attendance overview data for dashboard
     */
    public function attendanceOverview(Request $request)
    {
        try {
            $employeeId = $request->employee_id ?? (\Auth::user()->employee->id ?? 0);
            $filterType = $request->filter_type ?? 'today';
    
            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ]);
            }
    
            $data = [];
    
            if ($filterType === 'today' || $filterType === 'date') {
                // Today or specific date
                if ($filterType === 'today') {
                    $date = Carbon::today()->format('Y-m-d');
                } else {
                    $requestDate = $request->input('date');
                    if (empty($requestDate)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Date is required',
                            'debug' => 'No date parameter received'
                        ]);
                    }
                    try {
                        $date = Carbon::parse($requestDate)->format('Y-m-d');
                        \Log::info('Attendance Overview - Date selected: ' . $date . ', Employee ID: ' . $employeeId . ', Request date: ' . $requestDate);
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid date format: ' . $e->getMessage(),
                            'debug' => 'Date received: ' . $requestDate
                        ]);
                    }
                }
    
                \Log::info('Attendance Overview - Querying for date: ' . $date . ', Employee ID: ' . $employeeId);
                
                // Query attendance table for the specific date
                $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                    ->where('date', $date)
                    ->first();
    
                \Log::info('Attendance Overview - Found attendance: ' . ($attendance ? 'Yes' : 'No') . ' for date: ' . $date);
    
                if ($attendance) {
                    // Format clock_in and clock_out times
                    $clockIn = null;
                    $clockOut = null;
                    $isLate = false;
                    
                    if ($attendance->clock_in && $attendance->clock_in != '00:00:00') {
                        $clockIn = Carbon::parse($attendance->date . ' ' . $attendance->clock_in)->format('h:i A');
                        
                        // Check if clock-in is late (after company start time)
                        $companyStartTime = Utility::getValByName('company_start_time');
                        if ($companyStartTime) {
                            $clockInTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                            $expectedStartTime = Carbon::parse($attendance->date . ' ' . $companyStartTime);
                            $isLate = $clockInTime->gt($expectedStartTime);
                        }
                    }
                    
                    if ($attendance->clock_out && $attendance->clock_out != '00:00:00') {
                        $clockOut = Carbon::parse($attendance->date . ' ' . $attendance->clock_out)->format('h:i A');
                    }
    
                    // Calculate hours worked (decimal)
                    $hoursCompleted = 0;
                    if ($attendance->clock_in && $attendance->clock_in != '00:00:00') {
                        if ($attendance->clock_out && $attendance->clock_out != '00:00:00') {
                            // Both clock_in and clock_out exist - calculate difference
                            $clockInTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                            $clockOutTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_out);
                            
                            // Handle overnight shifts (clock out next day)
                            if ($clockOutTime->lt($clockInTime)) {
                                $clockOutTime->addDay();
                            }
                            
                            $minutes = $clockInTime->diffInMinutes($clockOutTime);
                            $hoursCompleted = $minutes / 60;
                        } else {
                            // Only clock_in exists - calculate from clock_in to now if today, else 0
                            $selectedDate = Carbon::parse($date);
                            if ($selectedDate->isToday()) {
                                $clockInTime = Carbon::parse($date . ' ' . $attendance->clock_in);
                                $now = Carbon::now();
                                $minutes = $clockInTime->diffInMinutes($now);
                                $hoursCompleted = $minutes / 60;
                            } else {
                                $hoursCompleted = 0; // incomplete past date
                            }
                        }
                    }
    
                    $data = [
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'hours_completed' => round($hoursCompleted, 2),
                        'today_hours' => round($hoursCompleted, 2), // Store for real-time calculation
                        'is_late' => $isLate,
                        'date' => $date, // Pass the attendance date for accurate calculations
                        'clock_in_raw' => $attendance->clock_in // Pass raw clock-in time for calculations
                    ];
                } else {
                    // No attendance record found for this date
                    $data = [
                        'clock_in' => null,
                        'clock_out' => null,
                        'hours_completed' => 0
                    ];
                }
            } elseif ($filterType === 'weekly') {
                // Get reference date from request (the date selected by user)
                $referenceDate = $request->input('date');
                try {
                    if (!empty($referenceDate)) {
                        $ref = Carbon::parse($referenceDate);
                    } else {
                        $ref = Carbon::now();
                    }
                } catch (\Exception $e) {
                    $ref = Carbon::now();
                }
    
                // Calculate week start (Monday) and end (Sunday) based on reference date
                $startOfWeek = $ref->copy()->startOfWeek(Carbon::MONDAY); // Start from Monday
                $endOfWeek = $ref->copy()->endOfWeek(Carbon::SUNDAY); // End on Sunday
    
                \Log::info('Attendance Overview - Weekly: Querying from ' . $startOfWeek->format('Y-m-d') . ' to ' . $endOfWeek->format('Y-m-d') . ', Employee ID: ' . $employeeId);
    
                // Query all attendance records for the entire week
                $attendances = AttendanceEmployee::where('employee_id', $employeeId)
                    ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                    ->orderBy('date', 'asc')
                    ->get();
    
                $hoursCompleted = 0;
                $daysWorked = 0;
    
                // Calculate total hours by adding all clock_in and clock_out entries
                foreach ($attendances as $attendance) {
                    if ($attendance->clock_in && $attendance->clock_in != '00:00:00') {
                        if ($attendance->clock_out && $attendance->clock_out != '00:00:00') {
                            // Both clock_in and clock_out exist
                            $clockInTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                            $clockOutTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_out);
                            
                            // Handle overnight shifts
                            if ($clockOutTime->lt($clockInTime)) {
                                $clockOutTime->addDay();
                            }
                            
                            $minutes = $clockInTime->diffInMinutes($clockOutTime);
                            $hours = $minutes / 60;
                            $hoursCompleted += $hours;
                            $daysWorked++;
                        } else {
                            // Only clock_in exists - if today, calculate to now
                            $attendanceDate = Carbon::parse($attendance->date);
                            if ($attendanceDate->isToday()) {
                                $clockInTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                                $now = Carbon::now();
                                $minutes = $clockInTime->diffInMinutes($now);
                                $hours = $minutes / 60;
                                $hoursCompleted += $hours;
                                $daysWorked++;
                            }
                        }
                    }
                }
    
                // Count working days in week (exclude Sundays)
                $workingDays = 0;
                $current = $startOfWeek->copy();
                while ($current <= $endOfWeek) {
                    if ($current->dayOfWeek !== Carbon::SUNDAY) {
                        $workingDays++;
                    }
                    $current->addDay();
                }
    
                // Expected total hours for the week (working days * 9 hours per day)
                $totalHours = $workingDays * 9;
    
                // Get today's attendance for real-time calculation
                $todayAttendance = AttendanceEmployee::where('employee_id', $employeeId)
                    ->where('date', Carbon::today()->format('Y-m-d'))
                    ->first();
                
                $todayHours = 0;
                $todayClockIn = null;
                $todayClockOut = null;
                
                if ($todayAttendance && $todayAttendance->clock_in && $todayAttendance->clock_in != '00:00:00') {
                    $todayClockIn = Carbon::parse($todayAttendance->date . ' ' . $todayAttendance->clock_in)->format('h:i A');
                    if ($todayAttendance->clock_out && $todayAttendance->clock_out != '00:00:00') {
                        $todayClockOut = Carbon::parse($todayAttendance->date . ' ' . $todayAttendance->clock_out)->format('h:i A');
                        $clockInTime = Carbon::parse($todayAttendance->date . ' ' . $todayAttendance->clock_in);
                        $clockOutTime = Carbon::parse($todayAttendance->date . ' ' . $todayAttendance->clock_out);
                        if ($clockOutTime->lt($clockInTime)) {
                            $clockOutTime->addDay();
                        }
                        $todayHours = $clockInTime->diffInMinutes($clockOutTime) / 60;
                    }
                }
                
                $data = [
                    'hours_completed' => round($hoursCompleted, 2),
                    'total_hours' => $totalHours,
                    'days_worked' => $daysWorked,
                    'percentage' => $totalHours > 0 ? round(($hoursCompleted / $totalHours) * 100, 1) : 0,
                    'week_start' => $startOfWeek->format('M d, Y'),
                    'week_end' => $endOfWeek->format('M d, Y'),
                    'clock_in' => $todayClockIn,
                    'clock_out' => $todayClockOut,
                    'today_hours' => round($todayHours, 2)
                ];
            } elseif ($filterType === 'monthly') {
                // Get month from request (format: YYYY-MM)
                $requestMonth = $request->input('month');
                if (empty($requestMonth)) {
                    $month = Carbon::now()->startOfMonth();
                    \Log::info('Attendance Overview - Monthly: No month provided, using current month');
                } else {
                    try {
                        // $requestMonth expected like "2025-12" (from input type month)
                        $month = Carbon::parse($requestMonth . '-01');
                        \Log::info('Attendance Overview - Monthly: Selected month: ' . $month->format('Y-m') . ', Employee ID: ' . $employeeId);
                    } catch (\Exception $e) {
                        \Log::error('Attendance Overview - Monthly: Invalid month format: ' . $requestMonth);
                        $month = Carbon::now()->startOfMonth();
                    }
                }
    
                $startOfMonth = $month->copy()->startOfMonth();
                $endOfMonth = $month->copy()->endOfMonth();
    
                \Log::info('Attendance Overview - Monthly: Querying from ' . $startOfMonth->format('Y-m-d') . ' to ' . $endOfMonth->format('Y-m-d'));
    
                // Query all attendance records for the entire month
                $attendances = AttendanceEmployee::where('employee_id', $employeeId)
                    ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                    ->orderBy('date', 'asc')
                    ->get();
    
                $hoursCompleted = 0;
                $daysWorked = 0;
    
                // Calculate total working hours for the month by adding all clock_in and clock_out entries
                foreach ($attendances as $attendance) {
                    if ($attendance->clock_in && $attendance->clock_in != '00:00:00') {
                        if ($attendance->clock_out && $attendance->clock_out != '00:00:00') {
                            // Both clock_in and clock_out exist
                            $clockInTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                            $clockOutTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_out);
                            
                            // Handle overnight shifts
                            if ($clockOutTime->lt($clockInTime)) {
                                $clockOutTime->addDay();
                            }
                            
                            $minutes = $clockInTime->diffInMinutes($clockOutTime);
                            $hours = $minutes / 60;
                            $hoursCompleted += $hours;
                            $daysWorked++;
                        } else {
                            // Only clock_in exists - if today, calculate to now
                            $attendanceDate = Carbon::parse($attendance->date);
                            if ($attendanceDate->isToday()) {
                                $clockInTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                                $now = Carbon::now();
                                $minutes = $clockInTime->diffInMinutes($now);
                                $hours = $minutes / 60;
                                $hoursCompleted += $hours;
                                $daysWorked++;
                            }
                        }
                    }
                }
    
                // Calculate total expected hours (working days in month * 9 hours)
                // Exclude Sundays
                $workingDays = 0;
                $current = $startOfMonth->copy();
                while ($current <= $endOfMonth) {
                    if ($current->dayOfWeek !== Carbon::SUNDAY) {
                        $workingDays++;
                    }
                    $current->addDay();
                }
    
                // Expected total hours for the month (working days * 9 hours per day)
                $totalHours = $workingDays * 9;
    
                // Get today's attendance for real-time calculation
                $todayAttendance = AttendanceEmployee::where('employee_id', $employeeId)
                    ->where('date', Carbon::today()->format('Y-m-d'))
                    ->first();
                
                $todayHours = 0;
                $todayClockIn = null;
                $todayClockOut = null;
                
                if ($todayAttendance && $todayAttendance->clock_in && $todayAttendance->clock_in != '00:00:00') {
                    $todayClockIn = Carbon::parse($todayAttendance->date . ' ' . $todayAttendance->clock_in)->format('h:i A');
                    if ($todayAttendance->clock_out && $todayAttendance->clock_out != '00:00:00') {
                        $todayClockOut = Carbon::parse($todayAttendance->date . ' ' . $todayAttendance->clock_out)->format('h:i A');
                        $clockInTime = Carbon::parse($todayAttendance->date . ' ' . $todayAttendance->clock_in);
                        $clockOutTime = Carbon::parse($todayAttendance->date . ' ' . $todayAttendance->clock_out);
                        if ($clockOutTime->lt($clockInTime)) {
                            $clockOutTime->addDay();
                        }
                        $todayHours = $clockInTime->diffInMinutes($clockOutTime) / 60;
                    }
                }
                
                $data = [
                    'hours_completed' => round($hoursCompleted, 2),
                    'total_hours' => $totalHours,
                    'days_worked' => $daysWorked,
                    'percentage' => $totalHours > 0 ? round(($hoursCompleted / $totalHours) * 100, 1) : 0,
                    'month_name' => $month->format('F Y'),
                    'clock_in' => $todayClockIn,
                    'clock_out' => $todayClockOut,
                    'today_hours' => round($todayHours, 2)
                ];
            }
    
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Attendance Overview Exception: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attendance data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Process missing punch-outs and apply half-day logic
     * 
     * This method processes all past attendance records where employees forgot to punch out.
     * Logic:
     * - First missing punch-out in a month → No action (allowed once per month)
     * - Second and subsequent missing punch-outs → Apply Half Day (4.5 hours from punch in)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function processMissingPunchOuts(Request $request)
    {
        try {
            $today = Carbon::today()->format('Y-m-d');
            
            // Find all attendance records where:
            // 1. Date is in the past (before today)
            // 2. clock_in exists and is not '00:00:00'
            // 3. clock_out is missing ('00:00:00' or null)
            $missingPunchOuts = AttendanceEmployee::where('date', '<', $today)
                ->where('clock_in', '!=', '00:00:00')
                ->where(function($query) {
                    $query->where('clock_out', '00:00:00')
                          ->orWhereNull('clock_out');
                })
                ->orderBy('employee_id')
                ->orderBy('date', 'asc')
                ->get();
            
            if ($missingPunchOuts->isEmpty()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'No missing punch-outs found to process.',
                        'processed' => 0
                    ]);
                }
                return redirect()->back()->with('success', __('No missing punch-outs found to process.'));
            }
            
            // Group by employee and month
            $groupedByEmployeeMonth = [];
            foreach ($missingPunchOuts as $attendance) {
                $monthKey = Carbon::parse($attendance->date)->format('Y-m');
                $key = $attendance->employee_id . '_' . $monthKey;
                
                if (!isset($groupedByEmployeeMonth[$key])) {
                    $groupedByEmployeeMonth[$key] = [
                        'employee_id' => $attendance->employee_id,
                        'month' => $monthKey,
                        'records' => []
                    ];
                }
                
                $groupedByEmployeeMonth[$key]['records'][] = $attendance;
            }
            
            $processedCount = 0;
            $skippedCount = 0;
            
            // Process each employee-month group
            foreach ($groupedByEmployeeMonth as $group) {
                $records = $group['records'];
                
                // Sort records by date to process chronologically
                usort($records, function($a, $b) {
                    return strcmp($a->date, $b->date);
                });
                
                // Process records in chronological order
                foreach ($records as $index => $attendance) {
                    // First missing punch-out in the month (index 0) → Skip
                    if ($index === 0) {
                        $skippedCount++;
                        continue;
                    }
                    
                    // Second and subsequent missing punch-outs → Apply Half Day
                    try {
                        // Parse clock_in time
                        $clockInTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                        
                        // Add 4.5 hours (4 hours 30 minutes) to clock_in
                        $calculatedClockOut = $clockInTime->copy()->addHours(4)->addMinutes(30);
                        
                        // If calculated time goes past midnight (next day), cap it at end of day (23:59:59)
                        $endOfDay = Carbon::parse($attendance->date . ' 23:59:59');
                        if ($calculatedClockOut->gt($endOfDay)) {
                            $calculatedClockOut = $endOfDay;
                        }
                        
                        // Format as H:i:s
                        $clockOutTime = $calculatedClockOut->format('H:i:s');
                        
                        // Update attendance record
                        $attendance->clock_out = $clockOutTime;
                        $attendance->status = AttendanceEmployee::STATUS_HALF_DAY;
                        
                        // Calculate early leaving (if applicable)
                        $endTime = Utility::getValByName('company_end_time');
                        if ($endTime) {
                            $expectedEndTime = Carbon::parse($attendance->date . ' ' . $endTime);
                            if ($calculatedClockOut->lt($expectedEndTime)) {
                                $totalEarlyLeavingSeconds = $expectedEndTime->diffInSeconds($calculatedClockOut);
                                $hours = floor($totalEarlyLeavingSeconds / 3600);
                                $mins = floor(($totalEarlyLeavingSeconds % 3600) / 60);
                                $secs = $totalEarlyLeavingSeconds % 60;
                                $attendance->early_leaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                            } else {
                                $attendance->early_leaving = '00:00:00';
                            }
                        }
                        
                        // Set overtime to 00:00:00 (no overtime for half day)
                        $attendance->overtime = '00:00:00';
                        
                        $attendance->save();
                        $processedCount++;
                        
                    } catch (\Exception $e) {
                        \Log::error('Error processing missing punch-out for attendance ID ' . $attendance->id . ': ' . $e->getMessage());
                        continue;
                    }
                }
            }
            
            $message = sprintf(
                'Processed %d missing punch-out(s). Skipped %d first occurrence(s) in their respective months.',
                $processedCount,
                $skippedCount
            );
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'processed' => $processedCount,
                    'skipped' => $skippedCount
                ]);
            }
            
            return redirect()->back()->with('success', __($message));
            
        } catch (\Exception $e) {
            \Log::error('Error in processMissingPunchOuts: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            $errorMessage = 'Error processing missing punch-outs: ' . $e->getMessage();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()->with('error', __($errorMessage));
        }
    }
    
    
   
}
