<?php

namespace App\Http\Controllers;

use App\Models\AccountList;
use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\Event;
use App\Models\LandingPageSection;
use App\Models\Meeting;
use App\Models\Job;
use App\Models\Order;
use App\Models\Payees;
use App\Models\Payer;
use App\Models\Plan;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyQuote;  
use App\Models\Department; 
use App\Models\Site;
use App\Models\LeaveType;  
use App\Models\ToDoList;  
use Carbon\Carbon;
use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Holiday;
use App\Models\Notice;
use App\Models\TimeSheet; // Make sure to import the TimeSheet model at the top
use App\Models\Leave;
use Illuminate\Http\Request;
use App\Models\Termination;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->type == 'employee') {
                $emp = Employee::with(['user', 'designation'])->where('user_id', '=', $user->id)->first();
                
                $announcements = Announcement::orderBy('announcements.id', 'desc')
                    ->take(5)
                    ->leftJoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')
                    ->where('announcement_employees.employee_id', '=', $emp->id)
                    ->orWhere(function ($q) {
                        $q->where('announcements.department_id', 0)->where('announcements.employee_id', 0);
                    })
                    ->get();
                
                $employees = Employee::get();
                $meetings = Meeting::orderBy('meetings.id', 'desc')
                        ->leftJoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')
                        ->where('meeting_employees.employee_id', '=', $emp->id)
                        ->orWhere(function ($q) {
                            $q->where('meetings.department_id', 0)->where('meetings.employee_id');
                        })
                        ->take(5)
                        ->get();
                
                
                $events = Event::select('events.*', 'events.id as event_id', 'event_employees.*')
                    ->leftJoin('event_employees', 'events.id', '=', 'event_employees.event_id')
                    ->where('event_employees.employee_id', '=', $emp->id)
                    ->orWhere(function ($q) {
                        $q->where('events.department_id', 0)->where('events.employee_id', 0);
                    })
                    ->get();
                
                $arrEvents = [];
                
                foreach ($events as $event) {
                    $arr['id'] = $event['event_id'];
                    $arr['title'] = $event['title'];
                    $arr['start'] = $event['start_date'];
                    $arr['end'] = $event['end_date'];
                    $arr['className'] = $event['color'];
                    $arr['url'] = (!empty($event['event_id'])) ? route('eventsshow', $event['event_id']) : '0';
                    $arrEvents[] = $arr;
                }
                
                $date = date("Y-m-d");

                // Fetch the latest attendance record for today
                $employeeAttendance = AttendanceEmployee::where('employee_id', '=', $emp->id ?? 0)
                    ->where('date', '=', $date)
                    ->first();

                // Pass clock-in time if available
                $clockInTime = $employeeAttendance ? $employeeAttendance->clock_in : null;    
                

                $officeTime['startTime'] = Utility::getValByName('company_start_time');
                $officeTime['endTime'] = Utility::getValByName('company_end_time');
                
                // Fetch a random daily quote
                $quote = DailyQuote::inRandomOrder()->first();

                $todos = ToDoList::where('user_id', Auth::id())
                ->whereDate('created_at', Carbon::today()) // Filter by today's date
                ->get();

                $today = Carbon::today();

                $notices = Notice::select('title', 'notice_startdate', 'notice_enddate')
                    ->where('created_by', '=', \Auth::user()->creatorId())
                    ->whereDate('notice_enddate', '>=', $today) // Show only notices with an end date today or in the future
                    ->orderBy('notice_startdate', 'asc') // Sort by start date in ascending order
                    ->take(5) // Limit to the latest 5 notices
                    ->get();
                
                // Add employeesNotWorkingToday logic for employee dashboard
                $currentDate = Carbon::today()->format('Y-m-d');
                
                // Get employees who have clocked in today
                $clockedInEmployees = AttendanceEmployee::where('date', '=', $currentDate)
                    ->whereNotNull('clock_in')
                    ->where('clock_in', '!=', '00:00:00')
                    ->pluck('employee_id');

                $notClockIn = AttendanceEmployee::where('date', '=', $currentDate)->pluck('employee_id');


                // Get employees on approved leave today (excluding current employee)
                $employeesOnLeaveToday = Leave::where('created_by', \Auth::user()->creatorId())
                    ->where('start_date', '<=', $currentDate)
                    ->where('end_date', '>=', $currentDate)
                    ->where('status', 'approved')
                    ->where('employee_id', '!=', $emp->id) // Exclude current employee
                    ->pluck('employee_id');

                $notClockIns = Employee::where('created_by', '=', \Auth::user()->creatorId())
                    ->whereNotIn('id', $clockedInEmployees) // Not clocked in
                    ->whereNotIn('id', $employeesOnLeaveToday) // Not on leave
                    ->get();

                // Get employees on approved leave (with relationships for display)
                $onLeaveEmployees = Leave::with(['employees', 'leaveType'])
                    ->where('created_by', \Auth::user()->creatorId())
                    ->where('start_date', '<=', $currentDate)
                    ->where('end_date', '>=', $currentDate)
                    ->where('status', 'approved')
                    ->where('employee_id', '!=', $emp->id) // Exclude current employee
                    ->get();

                // Prepare the final list for display
                $employeesNotWorkingToday = collect();

                // Add leave employees
                foreach ($onLeaveEmployees as $leave) {
                    if ($leave->employees) {
                        $employeesNotWorkingToday->push([
                            'employee_name' => $leave->employees->name ?? 'N/A',
                            'status' => $leave->leaveType->title ?? 'Leave'
                        ]);
                    }
                }

                        $today = Carbon::today();
                        $currentMonth = $today->month;
                        $currentYear = $today->year;
                        
                        // Get birthdays this month (not passed yet)
                        $birthdays = Employee::where('created_by', \Auth::user()->creatorId())
                            ->whereMonth('dob', $currentMonth)
                            ->get()
                            ->map(function ($employee) use ($today, $currentYear) {
                                $birthdayThisYear = Carbon::create($currentYear, date('m', strtotime($employee->dob)), date('d', strtotime($employee->dob)));
                                if ($birthdayThisYear >= $today) {
                                    return [
                                        'title' => $employee->name . "'s Birthday",
                                        'start' => $birthdayThisYear->format('Y-m-d'),
                                        'className' => 'bg-success',
                                        'allDay' => true,
                                        'url' => route('employee.show', $employee->id),
                                        'type' => 'birthday'
                                    ];
                                }
                                return null;
                            })->filter()->values()->toArray();
                        
                        // Get anniversaries this month (completed 1 year or more and not passed yet)
                        $anniversaries = Employee::where('created_by', \Auth::user()->creatorId())
                            ->whereMonth('company_doj', $currentMonth)
                            ->whereYear('company_doj', '<=', $currentYear - 1)
                            ->get()
                            ->map(function ($employee) use ($today, $currentYear) {
                                $anniversaryThisYear = Carbon::create($currentYear, date('m', strtotime($employee->company_doj)), date('d', strtotime($employee->company_doj)));
                                if ($anniversaryThisYear >= $today) {
                                    return [
                                        'title' => $employee->name . "'s Anniversary",
                                        'start' => $anniversaryThisYear->format('Y-m-d'),
                                        'className' => 'bg-primary',
                                        'allDay' => true,
                                        'url' => route('employee.show', $employee->id),
                                        'type' => 'anniversary'
                                    ];
                                }
                                return null;
                            })->filter()->values()->toArray();
                        
                        // Filter existing events to only current month and future dates
                        $filteredEvents = [];
                        foreach ($events as $event) {
                            $eventDate = Carbon::parse($event->start_date);
                            if ($eventDate->month == $currentMonth && $eventDate >= $today) {
                                $filteredEvents[] = [
                                    'id' => $event['id'],
                                    'title' => $event['title'],
                                    'start' => $event['start_date'],
                                    'end' => $event['end_date'],
                                    'className' => $event['color'],
                                    'url' => route('event.edit', $event['id']),
                                    'type' => 'event'
                                ];
                            }
                        }
                        
                        // Merge all events and sort by date
                        $allEvents = array_merge($filteredEvents, $birthdays, $anniversaries);
                        usort($allEvents, function($a, $b) {
                            return strtotime($a['start']) - strtotime($b['start']);
                        });
                        
                        // Fetch attendance data for calendar (fetch 3 months for smooth navigation)
                        $attendanceData = [];
                        if ($emp) {
                            $currentDate = Carbon::now();
                            // Fetch data for previous month, current month, and next month
                            $startRange = $currentDate->copy()->subMonth()->startOfMonth();
                            $endRange = $currentDate->copy()->addMonth()->endOfMonth();
                            
                            // Get all attendance records for the range
                            $attendances = AttendanceEmployee::where('employee_id', $emp->id)
                                ->whereBetween('date', [$startRange->format('Y-m-d'), $endRange->format('Y-m-d')])
                                ->get();
                            
                            $employeeData = [];
                            foreach ($attendances as $attendance) {
                                $dateFormatted = $attendance->date;
                                $employeeData[$dateFormatted] = [
                                    'type' => 'present',
                                    'clock_in' => $attendance->clock_in,
                                    'clock_out' => $attendance->clock_out
                                ];
                            }
                            
                            // Get leaves for the range
                            $leaves = Leave::where('employee_id', $emp->id)
                                ->where('status', 'approved')
                                ->where(function($query) use ($startRange, $endRange) {
                                    $query->whereBetween('start_date', [$startRange->format('Y-m-d'), $endRange->format('Y-m-d')])
                                          ->orWhereBetween('end_date', [$startRange->format('Y-m-d'), $endRange->format('Y-m-d')])
                                          ->orWhere(function($q) use ($startRange, $endRange) {
                                              $q->where('start_date', '<=', $startRange->format('Y-m-d'))
                                                ->where('end_date', '>=', $endRange->format('Y-m-d'));
                                          });
                                })
                                ->get();
                            
                            foreach ($leaves as $leave) {
                                $start = Carbon::parse($leave->start_date);
                                $end = Carbon::parse($leave->end_date);
                                
                                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                                    $formattedDate = $date->format('Y-m-d');
                                    if ($date->between($startRange, $endRange)) {
                                        if (!isset($employeeData[$formattedDate])) {
                                            $employeeData[$formattedDate] = [
                                                'type' => 'leave',
                                                'reason' => $leave->leave_reason ?? ''
                                            ];
                                        }
                                    }
                                }
                            }
                            
                            // Mark absent for past working days only (no attendance, no leave, not a weekend)
                            $today = Carbon::today();
                            for ($date = $startRange->copy(); $date->lte($endRange); $date->addDay()) {
                                $dateFormatted = $date->format('Y-m-d');
                                if (!isset($employeeData[$dateFormatted]) && $date->lte($today)
                                    && !\App\Models\Utility::isWeekOff($date)) {
                                    $employeeData[$dateFormatted] = ['type' => 'absent'];
                                }
                            }
                            
                            $attendanceData[$emp->id] = [
                                'name' => $emp->name,
                                'data' => $employeeData
                            ];
                        }

                // Get pending leave requests count for the employee
                $pendingLeaveCount = Leave::where('employee_id', $emp->id)
                    ->where('status', 'Pending')
                    ->count();

                // Pass employee details to the dashboard
                return view('dashboard.dashboard', compact('allEvents', 'employeesNotWorkingToday', 'notices', 'arrEvents', 'announcements', 'employees', 'meetings', 'employeeAttendance', 'officeTime', 'quote', 'emp', 'clockInTime', 'todos', 'attendanceData', 'pendingLeaveCount'));
            }
            else if ($user->type == 'super admin') {
                $user                       = \Auth::user();
                $user['total_user']         = $user->countCompany();
                $user['total_paid_user']    = $user->countPaidCompany();
                $user['total_orders']       = Order::total_orders();
                $user['total_orders_price'] = Order::total_orders_price();
                $user['total_plan']         = Plan::total_plan();
                $user['most_purchese_plan'] = (!empty(Plan::most_purchese_plan()) ? Plan::most_purchese_plan()->name : '');

                $chartData = $this->getOrderChart(['duration' => 'week']);
                // **Daily Quote Logic for Super Admin Dashboard**
                $quote = DailyQuote::inRandomOrder()->first();

                return view('dashboard.super_admin', compact('user', 'chartData', 'quote'));


            } 
            else if ($user->type == 'company' || $user->type == 'hr'|| $user->type == 'Director') {

                $today = Carbon::today();
                $startOfMonth = $today->copy()->startOfMonth();
                $endOfMonth = $today->copy()->endOfMonth();

                $events = Event::where('created_by', '=', \Auth::user()->creatorId())
                    ->whereBetween('start_date', [$startOfMonth, $endOfMonth]) // Filter for current month
                    ->whereDate('start_date', '>=', $today) // Only today or future events
                    ->orderBy('start_date', 'asc') // Sort so today comes first, then future events
                    ->get();

                $arrEvents = [];

                foreach ($events as $event) {
                    $arr['id']    = $event['id'];
                    $arr['title'] = $event['title'];
                    $arr['start'] = $event['start_date'];
                    $arr['end']   = $event['end_date'];
                    $arr['className'] = $event['color'];
                    $arr['employee'] = $event['employee_id'];
                    $arr['url']   = route('event.edit', $event['id']);

                    $arrEvents[] = $arr;
                }



                $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->where('created_by', '=', \Auth::user()->creatorId())->get();

                $employees = User::where('type', '=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
                $countEmployee = count($employees);

                

                $user      = User::where('type', '!=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
                $countUser = count($user);
                $countTicket      = Ticket::where('created_by', '=', \Auth::user()->creatorId())->count();
                $countOpenTicket  = Ticket::where('status', '=', 'open')->where('created_by', '=', \Auth::user()->creatorId())->count();
                $countCloseTicket = Ticket::where('status', '=', 'close')->where('created_by', '=', \Auth::user()->creatorId())->count();

                $currentDate = Carbon::today()->format('Y-m-d');

                // Get employees who have clocked in today
                $clockedInEmployees = AttendanceEmployee::where('date', '=', $currentDate)
                    ->whereNotNull('clock_in')
                    ->where('clock_in', '!=', '00:00:00')
                    ->pluck('employee_id');

                // $employees     = User::where('type', '=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
                // $countEmployee = count($employees);
                $notClockIn = AttendanceEmployee::where('date', '=', $currentDate)->pluck('employee_id');

                // Get employees on approved leave today
                $employeesOnLeaveToday = Leave::where('created_by', \Auth::user()->creatorId())
                    ->where('start_date', '<=', $currentDate)
                    ->where('end_date', '>=', $currentDate)
                    ->where('status', 'approved')
                    ->pluck('employee_id');

                // Merge both to exclude from "not clock in" list
                $excludeIds = $notClockIn->merge($employeesOnLeaveToday)->unique();

                $terminatedEmployeeIds = Termination::where('created_by', \Auth::user()->creatorId())
                    ->pluck('employee_id')
                    ->toArray();




                $employeesOnLeaveToday = Leave::where('created_by', \Auth::user()->creatorId())
                    ->where('start_date', '<=', $currentDate)
                    ->where('end_date', '>=', $currentDate)
                    ->where('status', 'approved') // only approved leaves
                    ->pluck('employee_id');


                // Get employees on approved leave today
                $onLeaveEmployees = Leave::with(['employees', 'leaveType'])
                    ->where('created_by', \Auth::user()->creatorId())
                    ->where('start_date', '<=', $currentDate)
                    ->where('end_date', '>=', $currentDate)
                    ->where('status', 'approved')
                    ->whereNotIn('employee_id', $clockedInEmployees) // Exclude those who clocked in
                    ->get();

                $notClockIns = Employee::where('created_by', '=', \Auth::user()->creatorId())
                    ->whereNotIn('id', $clockedInEmployees) // Not clocked in
                    ->whereNotIn('id', $employeesOnLeaveToday) // Not on leave
                    ->whereNotIn('id', $terminatedEmployeeIds) // Not terminated
                    ->get();

                // Get employees on approved leave (with relationships for display)
                $onLeaveEmployees = Leave::with(['employees', 'leaveType'])
                    ->where('created_by', \Auth::user()->creatorId())
                    ->where('start_date', '<=', $currentDate)
                    ->where('end_date', '>=', $currentDate)
                    ->where('status', 'approved')
                    ->get();

                // Prepare the final list for display
                $employeesNotWorkingToday = collect();

                // Add leave employees (who didn't clock in)
                foreach ($onLeaveEmployees as $leave) {
                    if ($leave->employees) {
                        $employeesNotWorkingToday->push([
                            'employee_name' => $leave->employees->name ?? 'N/A',
                            'status' => $leave->leaveType->title ?? 'Leave'
                        ]);
                    }
                }



                // Fetch present employees based on today's date
               // Get the total number of employees
                $totalEmployees = Employee::count();
                
                

                // Get present employees for today
                $presentEmployees = AttendanceEmployee::where('date', '=', $currentDate)
                    ->whereNotNull('clock_in')
                    ->where('clock_in', '!=', '00:00:00')
                    ->get();

                // Calculate the attendance percentage
                $attendancePercentage = $totalEmployees > 0 ? (count($presentEmployees) / $totalEmployees) * 100 : 0;

                // Get employees who are present and their clock-in time
                $presentEmployeesWithClockIn = AttendanceEmployee::where('date', '=', $currentDate)
                    ->whereNotNull('clock_in')
                    ->where('clock_in', '!=', '00:00:00')
                    ->with('employee')
                    ->get()
                    ->map(function ($attendance) {
                        return [
                            'employee' => $attendance->employee,
                            'clock_in' => $attendance->clock_in,
                            'clock_out' => $attendance->clock_out ?? '--:--',
                        ];
                    });

                $accountBalance = AccountList::where('created_by', '=', \Auth::user()->creatorId())->sum('initial_balance');
                $activeJob   = Job::where('status', 'active')->where('created_by', '=', \Auth::user()->creatorId())->count();
                $inActiveJOb = Job::where('status', 'in_active')->where('created_by', '=', \Auth::user()->creatorId())->count();

                $totalPayee = Payees::where('created_by', '=', \Auth::user()->creatorId())->count();
                $totalPayer = Payer::where('created_by', '=', \Auth::user()->creatorId())->count();

                // $meetings = Meeting::where('created_by', '=', \Auth::user()->creatorId())->limit(8)->get();

                $meetings = Meeting::where('created_by', Auth::id())
                 ->whereDate('created_at', Carbon::today()) // Filter by today's date
                 ->get();
                

                $users = User::find(\Auth::user()->creatorId());
                $plan = Plan::find($users->plan);
                if ($plan->storage_limit > 0) {
                    $storage_limit = ($users->storage_limit / $plan->storage_limit) * 100;
                } else {
                    $storage_limit = 0;
                } 
                 // **Daily Quote Logic for Other Users Dashboard**
                 $quote = DailyQuote::inRandomOrder()->first();


                 $totalDepartment = Department::where('created_by', '=', \Auth::user()->creatorId())->count();

                 $totalleaves = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->count();

                 
                 $departmentIds = [];
                 $employeeIds = [];
                 

                 
                 // Get unique IDs
                 $departmentIds = array_unique($departmentIds);
                 $employeeIds = array_unique($employeeIds);
                 
                 // Preload data
                 $departments = Department::whereIn('id', $departmentIds)->get()->keyBy('id');
                 $employees = Employee::with('user')->whereIn('id', $employeeIds)->get()->keyBy('id');
                 
                 

                 $totalHolidays = Holiday::count();

                 $todos = ToDoList::where('user_id', Auth::id())
                 ->whereDate('created_at', Carbon::today()) // Filter by today's date
                 ->get();
             

                    // Fetch income and expense data for the current month
                    $currentMonth = date('m');
                    $currentYear = date('Y');

                    // Fetch income data for the current month
                    $incomeData = Deposit::where('created_by', \Auth::user()->creatorId())
                        ->whereMonth('date', $currentMonth)
                        ->whereYear('date', $currentYear)
                        ->get()
                        ->groupBy(function ($date) {
                            return \Carbon\Carbon::parse($date->date)->format('d M Y'); // Group by day
                        })
                        ->map(function ($row) {
                            return $row->sum('amount');
                        });

                    // Fetch expense data for the current month
                    $expenseData = Expense::where('created_by', \Auth::user()->creatorId())
                        ->whereMonth('date', $currentMonth)
                        ->whereYear('date', $currentYear)
                        ->get()
                        ->groupBy(function ($date) {
                            return \Carbon\Carbon::parse($date->date)->format('d M Y'); // Group by day
                        })
                        ->map(function ($row) {
                            return $row->sum('amount');
                        });

                    // Prepare labels (dates) for the chart
                    $labels = $incomeData->keys()->merge($expenseData->keys())->unique()->sort();

                    // Prepare data for the chart
                    $incomeChartData = $labels->map(function ($date) use ($incomeData) {
                        return $incomeData->has($date) ? $incomeData[$date] : 0;
                    });

                    $expenseChartData = $labels->map(function ($date) use ($expenseData) {
                        return $expenseData->has($date) ? $expenseData[$date] : 0;
                    });

                    // Format data for the chart (same as income&expense.blade.php)
                    $data = [
                        [
                            'name' => 'Income',
                            'data' => $incomeChartData->values(),
                        ],
                        [
                            'name' => 'Expense',
                            'data' => $expenseChartData->values(),
                        ],
                    ];

                    // Pass data to the view
                    $chartData = [
                        'labels' => $labels->values(),
                        'data' => $data,
                    ];

                    $notices = Notice::select('title', 'notice_startdate', 'notice_enddate')
                    ->where('created_by', '=', \Auth::user()->creatorId())
                    ->orderBy('notice_startdate', 'desc')
                    ->take(5) // Limit to the latest 5 notices
                    ->get();


                    $todayEnquiryCount = TimeSheet::whereDate('created_at', Carbon::today())->count();


                    // Get current date and month
                        $today = Carbon::today();
                        $currentMonth = $today->month;
                        $currentYear = $today->year;
                        
                        // Get birthdays this month (not passed yet)
                        $birthdays = Employee::where('created_by', \Auth::user()->creatorId())
                            ->whereMonth('dob', $currentMonth)
                            ->get()
                            ->map(function ($employee) use ($today, $currentYear) {
                                $birthdayThisYear = Carbon::create($currentYear, date('m', strtotime($employee->dob)), date('d', strtotime($employee->dob)));
                                if ($birthdayThisYear >= $today) {
                                    return [
                                        'title' => $employee->name . "'s Birthday",
                                        'start' => $birthdayThisYear->format('Y-m-d'),
                                        'className' => 'bg-success',
                                        'allDay' => true,
                                        'url' => route('employee.show', $employee->id),
                                        'type' => 'birthday'
                                    ];
                                }
                                return null;
                            })->filter()->values()->toArray();
                        
                        // Get anniversaries this month (completed 1 year or more and not passed yet)
                        $anniversaries = Employee::where('created_by', \Auth::user()->creatorId())
                            ->whereMonth('company_doj', $currentMonth)
                            ->whereYear('company_doj', '<=', $currentYear - 1)
                            ->get()
                            ->map(function ($employee) use ($today, $currentYear) {
                                $anniversaryThisYear = Carbon::create($currentYear, date('m', strtotime($employee->company_doj)), date('d', strtotime($employee->company_doj)));
                                if ($anniversaryThisYear >= $today) {
                                    return [
                                        'title' => $employee->name . "'s Anniversary",
                                        'start' => $anniversaryThisYear->format('Y-m-d'),
                                        'className' => 'bg-primary',
                                        'allDay' => true,
                                        'url' => route('employee.show', $employee->id),
                                        'type' => 'anniversary'
                                    ];
                                }
                                return null;
                            })->filter()->values()->toArray();
                        
                        // Filter existing events to only current month and future dates
                        $filteredEvents = [];
                        foreach ($events as $event) {
                            $eventDate = Carbon::parse($event->start_date);
                            if ($eventDate->month == $currentMonth && $eventDate >= $today) {
                                $filteredEvents[] = [
                                    'id' => $event['id'],
                                    'title' => $event['title'],
                                    'start' => $event['start_date'],
                                    'end' => $event['end_date'],
                                    'className' => $event['color'],
                                    'url' => route('event.edit', $event['id']),
                                    'type' => 'event'
                                ];
                            }
                        }
                        
                        // Merge all events and sort by date
                        $allEvents = array_merge($filteredEvents, $birthdays, $anniversaries);
                        usort($allEvents, function($a, $b) {
                            return strtotime($a['start']) - strtotime($b['start']);
                        });
                        

                    



                return view('dashboard.company', compact('allEvents', 'employeesNotWorkingToday', 'todayEnquiryCount','notices','totalHolidays', 'arrEvents', 'announcements', 'employees', 'activeJob', 'inActiveJOb', 'meetings', 'countEmployee', 'countUser', 'countTicket', 'countOpenTicket', 'countCloseTicket', 'notClockIns','onLeaveEmployees', 'accountBalance', 'totalPayee', 'totalPayer', 'users', 'plan', 'storage_limit', 'quote','attendancePercentage', 'presentEmployeesWithClockIn', 'totalEmployees', 'totalDepartment', 'totalleaves', 'todos','chartData',));
            }
        } 
        
        return view('welcome');
    }

    /**
     * Get organization hierarchy data for AJAX requests
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganizationHierarchy(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $creatorId = \Auth::user()->creatorId();
        
        // Get current logged-in employee
        $currentUser = Employee::where('user_id', \Auth::id())->first();
        
        if (!$currentUser) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        
        // Get ALL employees from all departments for full organizational view
        $employees = Employee::where('created_by', $creatorId)
            ->with(['designation', 'department', 'reportingManager'])
            ->get();
        
        // Find CEO (employee with CEO designation) - search across all departments
        $ceo = Employee::where('created_by', $creatorId)
            ->with(['designation', 'department', 'reportingManager'])
            ->get()
            ->first(function($employee) {
                return stripos($employee->designation->name ?? '', 'CEO') !== false;
            });
        
        // Build hierarchy data
        $hierarchyData = $this->buildHierarchyData($employees, $ceo, $employeeId);
        
        // Add department name to response
        $hierarchyData['department_name'] = 'All Departments';
        
        // Add current logged-in employee ID for highlighting
        $hierarchyData['current_employee_id'] = $currentUser->id;
        
        // Add current employee data for display
        $hierarchyData['current_employee'] = [
            'id' => $currentUser->id,
            'name' => $currentUser->name,
            'designation' => $currentUser->designation->name ?? '',
            'level' => 'current-employee',
            'avatar' => $this->getEmployeeInitials($currentUser)
        ];
        
        return response()->json($hierarchyData);
    }
    
    /**
     * Build hierarchy data based on employee selection
     *
     * @param \Illuminate\Support\Collection $employees
     * @param \App\Models\Employee|null $ceo
     * @param int|null $selectedEmployeeId
     * @return array
     */
    private function buildHierarchyData($employees, $ceo, $selectedEmployeeId = null)
    {
        // If no specific employee selected, show full hierarchy starting from CEO
        if (!$selectedEmployeeId) {
            return $this->buildFullHierarchy($employees, $ceo);
        }
        
        // Show hierarchy for selected employee
        $selectedEmployee = $employees->find($selectedEmployeeId);
        if (!$selectedEmployee) {
            return $this->buildFullHierarchy($employees, $ceo);
        }
        
        return $this->buildEmployeeHierarchy($employees, $selectedEmployee, $ceo);
    }
    
    /**
     * Build full organization hierarchy
     *
     * @param \Illuminate\Support\Collection $employees
     * @param \App\Models\Employee|null $ceo
     * @return array
     */
    private function buildFullHierarchy($employees, $ceo)
    {
        $hierarchy = [];
        
        if ($employees->isEmpty()) {
            return ['hierarchy' => $hierarchy, 'type' => 'full'];
        }
        
        // Always add CEO first if exists (even if not in same department, for reference)
        if ($ceo) {
            $hierarchy[] = [
                'id' => $ceo->id,
                'name' => $ceo->name,
                'designation' => $ceo->designation->name ?? '',
                'level' => 'ceo',
                'avatar' => $this->getEmployeeInitials($ceo)
            ];
        }
        
        // Find top person in department (person with no reporting manager or highest designation)
        $topPerson = $employees->first(function($employee) {
            return $employee->reporting_manager === null;
        });
        
        if (!$topPerson) {
            $topPerson = $this->findHighestDesignation($employees);
        }
        
        if ($topPerson && (!$ceo || $topPerson->id !== $ceo->id)) {
            $hierarchy[] = [
                'id' => $topPerson->id,
                'name' => $topPerson->name,
                'designation' => $topPerson->designation->name ?? '',
                'level' => 'manager',
                'avatar' => $this->getEmployeeInitials($topPerson)
            ];
        }
        
        // Get all employees except top person and CEO
        $otherEmployees = $employees->where('id', '!=', $topPerson->id ?? 0);
        if ($ceo) {
            $otherEmployees = $otherEmployees->where('id', '!=', $ceo->id);
        }
        
        // Group by reporting manager
        $groupedByManager = $otherEmployees->groupBy('reporting_manager');
        
        foreach ($groupedByManager as $managerId => $subordinates) {
            if ($managerId === null) {
                // Employees reporting directly to top person (no manager assigned)
                foreach ($subordinates as $employee) {
                    $hierarchy[] = [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'designation' => $employee->designation->name ?? '',
                        'level' => 'employee',
                        'avatar' => $this->getEmployeeInitials($employee)
                    ];
                }
            } else {
                // Find manager
                $manager = $employees->find($managerId);
                if ($manager && $manager->id !== $topPerson->id && (!$ceo || $manager->id !== $ceo->id)) {
                    // Add manager card if not already added
                    if (!in_array($manager->id, array_column($hierarchy, 'id'))) {
                        $hierarchy[] = [
                            'id' => $manager->id,
                            'name' => $manager->name,
                            'designation' => $manager->designation->name ?? '',
                            'level' => 'manager',
                            'avatar' => $this->getEmployeeInitials($manager)
                        ];
                    }
                    
                    // Add all subordinates of this manager
                    foreach ($subordinates as $subordinate) {
                        $hierarchy[] = [
                            'id' => $subordinate->id,
                            'name' => $subordinate->name,
                            'designation' => $subordinate->designation->name ?? '',
                            'level' => 'employee',
                            'avatar' => $this->getEmployeeInitials($subordinate)
                        ];
                    }
                }
            }
        }
        
        return ['hierarchy' => $hierarchy, 'type' => 'full'];
    }
    
    /**
     * Find employee with highest designation
     *
     * @param \Illuminate\Support\Collection $employees
     * @return \App\Models\Employee|null
     */
    private function findHighestDesignation($employees)
    {
        $designationPriority = [
            'CEO' => 1,
            'Director' => 2,
            'VP' => 3,
            'Vice President' => 3,
            'Head' => 4,
            'Manager' => 5,
            'Lead' => 6,
            'Senior' => 7,
        ];
        
        $highestEmployee = null;
        $highestPriority = 999;
        
        foreach ($employees as $employee) {
            $designation = strtolower($employee->designation->name ?? '');
            $priority = 999;
            
            foreach ($designationPriority as $pattern => $prio) {
                if (strpos($designation, strtolower($pattern)) !== false) {
                    $priority = $prio;
                    break;
                }
            }
            
            if ($priority < $highestPriority) {
                $highestPriority = $priority;
                $highestEmployee = $employee;
            }
        }
        
        return $highestEmployee;
    }
    
    /**
     * Build hierarchy for specific employee
     *
     * @param \Illuminate\Support\Collection $employees
     * @param \App\Models\Employee $selectedEmployee
     * @param \App\Models\Employee|null $ceo
     * @return array
     */
    private function buildEmployeeHierarchy($employees, $selectedEmployee, $ceo)
    {
        $hierarchy = [];
        $chain = [];
        
        // If CEO is selected, get ALL employees from all departments
        if ($ceo && $selectedEmployee->id === $ceo->id) {
            $allEmployees = Employee::where('created_by', \Auth::user()->creatorId())
                ->with(['designation', 'department', 'reportingManager'])
                ->get();
            
            $hierarchy[] = [
                'id' => $ceo->id,
                'name' => $ceo->name,
                'designation' => $ceo->designation->name ?? '',
                'level' => 'selected',
                'avatar' => $this->getEmployeeInitials($ceo)
            ];
            
            // Add all employees who report directly to CEO (no reporting_manager)
            $directReports = $allEmployees->where('reporting_manager', null);
            foreach ($directReports as $directReport) {
                if ($directReport->id !== $ceo->id) {
                    $hierarchy[] = [
                        'id' => $directReport->id,
                        'name' => $directReport->name,
                        'designation' => $directReport->designation->name ?? '',
                        'level' => 'subordinate',
                        'avatar' => $this->getEmployeeInitials($directReport)
                    ];
                }
            }
            
            return ['hierarchy' => $hierarchy, 'type' => 'employee'];
        }
        
        // For non-CEO employees, build vertical chain
        // Build reporting chain up to CEO
        $current = $selectedEmployee;
        $visitedIds = []; // Prevent infinite loops
        
        while ($current && !in_array($current->id, $visitedIds)) {
            $visitedIds[] = $current->id;
            
            $level = 'employee';
            if ($current->id === $selectedEmployee->id) {
                $level = 'selected';
            } elseif ($current->reporting_manager === null && $ceo && $current->id !== $ceo->id) {
                $level = 'manager'; // This person reports directly to CEO
            } elseif ($current->reporting_manager !== null) {
                $level = 'manager';
            }
            
            array_unshift($chain, [
                'id' => $current->id,
                'name' => $current->name,
                'designation' => $current->designation->name ?? '',
                'level' => $level,
                'avatar' => $this->getEmployeeInitials($current)
            ]);
            
            // Find next person in chain
            if ($current->reporting_manager === null) {
                // This person reports directly to CEO
                if ($ceo && $current->id !== $ceo->id) {
                    // Add CEO if not already in chain
                    if (!in_array($ceo->id, $visitedIds)) {
                        array_unshift($chain, [
                            'id' => $ceo->id,
                            'name' => $ceo->name,
                            'designation' => $ceo->designation->name ?? '',
                            'level' => 'ceo',
                            'avatar' => $this->getEmployeeInitials($ceo)
                        ]);
                    }
                }
                break;
            } else {
                $current = $employees->find($current->reporting_manager);
            }
        }
        
        $hierarchy = array_merge($hierarchy, $chain);
        
        // Add subordinates of the selected employee at the bottom
        $subordinates = $employees->where('reporting_manager', $selectedEmployee->id);
        foreach ($subordinates as $subordinate) {
            $hierarchy[] = [
                'id' => $subordinate->id,
                'name' => $subordinate->name,
                'designation' => $subordinate->designation->name ?? '',
                'level' => 'subordinate',
                'avatar' => $this->getEmployeeInitials($subordinate)
            ];
        }
        
        return ['hierarchy' => $hierarchy, 'type' => 'employee'];
    }
    
    /**
     * Get employee initials for avatar
     *
     * @param \App\Models\Employee $employee
     * @return string
     */
    private function getEmployeeInitials($employee)
    {
        $nameParts = explode(' ', trim($employee->name));
        if (count($nameParts) >= 2) {
            return strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
        } elseif (count($nameParts) === 1) {
            return strtoupper(substr($nameParts[0], 0, 2));
        }
        return 'EE';
    }

   public function filterDashboardData(Request $request)
{
    $filterType = $request->filter_type;
    $customDate = $request->custom_date ?? null;
    
    // Determine the date to filter by
    if ($filterType === 'yesterday') {
        $date = Carbon::yesterday();
    } elseif ($filterType === 'custom' && $customDate) {
        $date = Carbon::parse($customDate);
    } else {
        $date = Carbon::today(); // Default to today
    }
    
    $dateString = $date->format('Y-m-d');
    
    // Get data for the selected date
    $todayEnquiryCount = TimeSheet::whereDate('created_at', $date)->count();
    
    // Get attendance data
    $presentEmployeesWithClockIn = AttendanceEmployee::where('date', '=', $dateString)
        ->whereNotNull('clock_in')
        ->where('clock_in', '!=', '00:00:00')
        ->with('employee')
        ->get()
        ->map(function ($attendance) {
            return [
                'employee' => $attendance->employee,
                'clock_in' => $attendance->clock_in,
                'clock_out' => $attendance->clock_out ?? '--:--',
            ];
        })->toArray();
    
    // Get not clocked in employees
    $notClockIn = AttendanceEmployee::where('date', '=', $dateString)
        ->where('created_by', \Auth::user()->creatorId())
        ->pluck('employee_id');
    
    $employeesOnLeaveToday = Leave::where('created_by', \Auth::user()->creatorId())
        ->where('start_date', '<=', $dateString)
        ->where('end_date', '>=', $dateString)
        ->where('status', 'approved')
        ->pluck('employee_id');
    
    $excludeIds = $notClockIn->merge($employeesOnLeaveToday)->unique();
    
    $notClockIns = Employee::where('created_by', '=', \Auth::user()->creatorId())
        ->whereNotIn('id', $excludeIds)
        ->get()
        ->map(function ($employee) {
            return [
                'name' => $employee->name,
                'id' => $employee->id
            ];
        })->toArray();
    
    // Get employees on leave with leave type
    $onLeaveEmployees = Leave::with(['employees', 'leaveType'])
        ->where('created_by', \Auth::user()->creatorId())
        ->where('start_date', '<=', $dateString)
        ->where('end_date', '>=', $dateString)
        ->where('status', 'approved')
        ->get();
    
    // Prepare the final list for display (matching the main dashboard format)
    $employeesNotWorkingToday = collect();
    
    // Add leave employees
    foreach ($onLeaveEmployees as $leave) {
        if ($leave->employees) {
            $employeesNotWorkingToday->push([
                'employee_name' => $leave->employees->name ?? 'N/A',
                'status' => $leave->leaveType->title ?? 'Leave'
            ]);
        }
    }
    
    return response()->json([
        'success' => true,
        'todayEnquiryCount' => $todayEnquiryCount,
        'presentEmployeesWithClockIn' => $presentEmployeesWithClockIn,
        'notClockIns' => $notClockIns,
        'employeesNotWorkingToday' => $employeesNotWorkingToday->toArray(),
        'selectedDate' => $dateString,
    ]);
}
    

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee) {
            $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                ->where('date', date('Y-m-d'))
                ->first();

            if ($attendance && !$attendance->clock_out) {
                $attendance->clock_out = now()->format('H:i:s');
                $attendance->save();

                return redirect()->back()->with('success', 'You have successfully clocked out.');
            } else {
                return redirect()->back()->with('error', 'You have already clocked out today.');
            }
        }

        return redirect()->back()->with('error', 'Employee not found.');
    }

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if ($arrParam['duration']) {
            if ($arrParam['duration'] == 'week') {
                $previous_week = strtotime("-2 week +1 day");
                for ($i = 0; $i < 14; $i++) {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }

        $arrTask          = [];
        $arrTask['label'] = [];
        $arrTask['data']  = [];
        foreach ($arrDuration as $date => $label) {

            $data               = Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
            $arrTask['label'][] = $label;
            $arrTask['data'][]  = $data->total;
        }

        return $arrTask;
    }

    private function extractMainLocation($fullLocation)
{
    if (empty($fullLocation)) {
        return '';
    }

    // Example 1: If location is comma-separated (like "Building A, Floor 3, Room 101")
    $parts = explode(',', $fullLocation);
    return trim($parts[0]); // Returns "Building A"

    // OR Example 2: If you have specific logic to determine main location
    // return your_custom_logic_to_extract_main_location($fullLocation);
    
    // OR Example 3: If location is JSON, decode it first
    // $locationData = json_decode($fullLocation, true);
    // return $locationData['main_location'] ?? $locationData['building'] ?? '';
}
}