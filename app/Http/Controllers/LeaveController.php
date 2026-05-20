<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Leave as LocalLeave;
use App\Models\LeaveType;
use App\Mail\LeaveActionSend;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Imports\EmployeesImport;
use App\Exports\LeaveExport;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\GoogleCalendar\Event as GoogleEvent;
use App\Models\EmployeeLeaveBalance;
use App\Models\User;
use App\Notifications\LeaveActionNotification;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'Approved');
        $user = \Auth::user();
        $employee = Employee::where('user_id', '=', $user->id)->first();
        $isManager = false;
        
        if ($employee && $employee->designation) {
            $isManager = (str_contains(strtolower($employee->designation->name), 'manager'));
        }

        if ($user->can('Manage Leave') || $isManager) {
            $leaveBalance = null;
            
            if ($user->type == 'employee') {
                $leaves = LocalLeave::where('employee_id', '=', $employee->id)
                    ->where('status', $status)
                    ->orderBy('id', 'desc')->get();
                
                // Calculate leave balance for employee
                if ($employee) {
                    $leave_type = $this->getDefaultLeaveType();
                    $proRataLeaves = $this->calculateProRataLeaves($employee->id);
                    $now = now();
                    
                    // Monthly limit: 2 paid leaves per month
                    $monthlyLimit = 2;
                    
                    // This month paid leaves used (approved)
                    $thisMonthPaidLeaves = LocalLeave::where('employee_id', $employee->id)
                        ->where('leave_type_id', $leave_type->id)
                        ->where('is_paid', true)
                        ->where('status', 'Approved')
                        ->whereYear('start_date', $now->year)
                        ->whereMonth('start_date', $now->month)
                        ->sum('total_leave_days');
                    
                    // This month total leaves used (paid + LWP, approved)
                    $thisMonthTotalLeaves = LocalLeave::where('employee_id', $employee->id)
                        ->where('leave_type_id', $leave_type->id)
                        ->where('status', 'Approved')
                        ->whereYear('start_date', $now->year)
                        ->whereMonth('start_date', $now->month)
                        ->sum('total_leave_days');
                    
                    // Remaining paid leaves for this month
                    $remainingPaidLeaves = max(0, $monthlyLimit - $thisMonthPaidLeaves);
                    
                    // Yearly total PAID leaves used (approved) - LOP leaves are NOT counted
                    $date = Utility::AnnualLeaveCycle();
                    $yearlyUsed = LocalLeave::where('employee_id', $employee->id)
                        ->where('leave_type_id', $leave_type->id)
                        ->where('is_paid', true) // Only count PAID leaves
                        ->where('status', 'Approved')
                        ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                        ->sum('total_leave_days');
                    
                    // Yearly remaining leaves
                    $yearlyRemaining = $proRataLeaves - $yearlyUsed;
                    
                    $leaveBalance = [
                        'total_year_leaves' => $proRataLeaves,
                        'monthly_limit' => $monthlyLimit,
                        'this_month_paid_used' => $thisMonthPaidLeaves,
                        'this_month_total_used' => $thisMonthTotalLeaves,
                        'remaining_paid_this_month' => $remainingPaidLeaves,
                        'yearly_used' => $yearlyUsed,
                        'yearly_remaining' => $yearlyRemaining > 0 ? $yearlyRemaining : 0
                    ];
                }
            } else {
                $leaves = LocalLeave::where('created_by', '=', $user->creatorId())
                    ->where('status', $status)
                    ->with(['employees', 'leaveType'])->orderBy('id', 'desc')->get();
            }

            return view('leave.index', compact('leaves', 'leaveBalance', 'isManager', 'employee', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function leaveRequest()
    {
        $user = \Auth::user();
        $employee = Employee::where('user_id', '=', $user->id)->first();
        $isManager = false;
        
        if ($employee && $employee->designation) {
            $isManager = (str_contains(strtolower($employee->designation->name), 'manager'));
        }

        if ($isManager && $employee) {
            // Manager sees all leaves in their department except their own
            $departmentEmployeeIds = Employee::where('department_id', '=', $employee->department_id)
                ->where('id', '!=', $employee->id)
                ->pluck('id')
                ->toArray();
                
            $leaves = LocalLeave::whereIn('employee_id', $departmentEmployeeIds)
                ->with(['employees', 'leaveType'])
                ->orderBy('id', 'desc')
                ->get();

            return view('leave.leave_request', compact('leaves', 'isManager', 'employee'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

   public function create()
    {
        if (\Auth::user()->can('Create Leave')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('user_id', '=', \Auth::user()->id)->first();
            } else {
                $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            }
            
            // Check monthly paid leaves for employee
            $monthlyLeaveInfo = null;
            if (Auth::user()->type == 'employee' && $employees) {
                $leave_type = $this->getDefaultLeaveType();
                $now = now();
                $monthlyPaidLeavesUsed = LocalLeave::where('employee_id', $employees->id)
                    ->where('leave_type_id', $leave_type->id)
                    ->where('is_paid', true)
                    ->where('status', 'Approved')
                    ->whereYear('start_date', $now->year)
                    ->whereMonth('start_date', $now->month)
                    ->sum('total_leave_days');
                
                $monthlyLimit = 2;
                $monthlyLeaveInfo = [
                    'used' => $monthlyPaidLeavesUsed,
                    'limit' => $monthlyLimit,
                    'remaining' => max(0, $monthlyLimit - $monthlyPaidLeavesUsed),
                    'exceeded' => $monthlyPaidLeavesUsed >= $monthlyLimit
                ];
            }
            
            return view('leave.create', compact('employees', 'monthlyLeaveInfo'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Get or create default leave type for the company
     */
    private function getDefaultLeaveType()
    {
        $creatorId = \Auth::user()->creatorId();
        $defaultLeaveType = LeaveType::where('created_by', $creatorId)
            ->where('title', 'General Leave')
            ->first();
            
        if (!$defaultLeaveType) {
            $defaultLeaveType = new LeaveType();
            $defaultLeaveType->title = 'General Leave';
            $defaultLeaveType->days = 15; // 15 leaves per year
            $defaultLeaveType->created_by = $creatorId;
            $defaultLeaveType->save();
        }
        
        return $defaultLeaveType;
    }

    /**
     * Calculate pro-rata leave entitlement based on employee joining date
     * 15 leaves per year = 1.25 leaves per month
     * If employee joins in month 4, they get (12 - 4 + 1) = 9 months * 1.25 = 11.25 leaves
     */
    private function calculateProRataLeaves($employeeId, $year = null)
    {
        $employee = Employee::find($employeeId);
        if (!$employee || !$employee->company_doj) {
            // If no joining date, assume full year entitlement
            return 15;
        }

        $year = $year ?? date('Y');
        $joinDate = \Carbon\Carbon::parse($employee->company_doj);
        $yearStart = \Carbon\Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = \Carbon\Carbon::create($year, 12, 31)->endOfDay();

        // If employee joined before the year, they get full entitlement
        if ($joinDate->lt($yearStart)) {
            return 15;
        }

        // If employee joined after the year, they get no entitlement
        if ($joinDate->gt($yearEnd)) {
            return 0;
        }

        // Get the joining month (1-12)
        $joinMonth = $joinDate->month;
        
        // Calculate remaining months from joining month to end of year
        // If joined in month 4, remaining months = 12 - 4 + 1 = 9 months (April to December inclusive)
        $remainingMonths = 12 - $joinMonth + 1;
        
        // Calculate pro-rata leaves: remaining months * 1.25
        $proRataLeaves = $remainingMonths * 1.25;
        
        return round($proRataLeaves, 2);
    }

    /**
     * Count paid leaves used by employee in current year
     */
    private function getPaidLeavesUsed($employeeId, $year = null)
    {
        $year = $year ?? date('Y');
        $date = Utility::AnnualLeaveCycle();
        
        $paidLeaves = LocalLeave::where('employee_id', $employeeId)
            ->where('is_paid', true)
            ->where('status', 'Approved')
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');
            
        return $paidLeaves;
    }

    public function store(Request $request)
{
    if (\Auth::user()->can('Create Leave')) {
        $validator = \Validator::make($request->all(), [
            'employee_id' => 'required',
            'leave_duration' => 'required|in:Full Day,Half Day',
            'start_date' => 'required',
            'end_date' => 'required',
            'leave_reason' => 'required',
        ]);

        // Validate leave_session for Half Day
        if ($request->leave_duration == 'Half Day') {
            $validator->after(function ($validator) use ($request) {
                if (empty($request->leave_session) || !in_array($request->leave_session, ['First Half', 'Second Half'])) {
                    $validator->errors()->add('leave_session', __('Please select a session for Half Day leave.'));
                }
            });
        }

        // Validate leave_type_selection is provided
        if (empty($request->leave_type_selection)) {
            return redirect()->back()->with('error', __('Please select a leave type (Paid or LOP).'));
        }

        // Check monthly paid leaves limit and validate selection
        $now = now();
        $leave_type = $this->getDefaultLeaveType();
        $monthlyPaidLeavesUsed = LocalLeave::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $leave_type->id)
            ->where('is_paid', true)
            ->where('status', 'Approved')
            ->whereYear('start_date', $now->year)
            ->whereMonth('start_date', $now->month)
            ->sum('total_leave_days');

        $monthlyLimit = 2;

        // If monthly limit exceeded, user MUST select LOP
        if ($monthlyPaidLeavesUsed >= $monthlyLimit && $request->leave_type_selection == 'paid') {
            return redirect()->back()->with('error', __('You have already used '.$monthlyPaidLeavesUsed.' paid leaves this month. You must select LOP (Loss of Pay) for additional leaves.'));
        }

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        // Calculate total leave days based on duration
        $leaveDuration = $request->leave_duration;
        if ($leaveDuration == 'Half Day') {
            $total_leave_days = 0.5;
            // For half day, start and end date should be the same
            $request->merge(['end_date' => $request->start_date]);
        } else {
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $endDate->add(new \DateInterval('P1D')); // Include end date in calculation
            $total_leave_days = $startDate->diff($endDate)->days;
        }

        // Get default leave type
        $leave_type = $this->getDefaultLeaveType();

        // Calculate pro-rata leave entitlement (15 leaves per year = 1.25 per month)
        $proRataLeaves = $this->calculateProRataLeaves($request->employee_id);
        
        // Check monthly balance if applicable
        $now = now();
        $balance = EmployeeLeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $leave_type->id)
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->first();
            
        if ($balance) {
            if ($total_leave_days > $balance->available_days) {
                return redirect()->back()->with('error', __('You only have '.$balance->available_days.' days available this month.'));
            }
        } else {
            // If no monthly balance is allocated, use pro-rata calculation
            $date = Utility::AnnualLeaveCycle();

            // Only count PAID leaves for balance calculation - LOP leaves don't count
            $leaves_used = LocalLeave::where('employee_id', '=', $request->employee_id)
                ->where('leave_type_id', $leave_type->id)
                ->where('is_paid', true) // Only count PAID leaves
                ->where('status', 'Approved')
                ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                ->sum('total_leave_days');

            $leaves_pending = LocalLeave::where('employee_id', '=', $request->employee_id)
                ->where('leave_type_id', $leave_type->id)
                ->where('is_paid', true) // Only count PAID leaves
                ->where('status', 'Pending')
                ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                ->sum('total_leave_days');

            // Use pro-rata leaves (15 per year, calculated based on joining date)
            $return = $proRataLeaves - $leaves_used;
            
            // Only check yearly balance if user is requesting PAID leave
            // LOP leaves should not be restricted by yearly balance
            if ($request->leave_type_selection == 'paid') {
                // Check if requested days exceed available days
                if ($total_leave_days > $return) {
                    return redirect()->back()->with('error', __('You cannot take more than '.$return.' paid days. Your pro-rata entitlement is '.$proRataLeaves.' days. Please select LOP if you want to proceed.'));
                }

                if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $return) {
                    return redirect()->back()->with('error', __('Multiple leave entry is pending.'));
                }
            }
        }


        // Use the user's explicit selection for leave type
        $isPaidLeave = ($request->leave_type_selection == 'paid');


        $leave = new LocalLeave();
        $leave->employee_id = $request->employee_id;
        $leave->leave_type_id = $leave_type->id; // Use default leave type
        $leave->applied_on = date('Y-m-d');
        $leave->start_date = $request->start_date;
        $leave->end_date = $request->end_date;
        $leave->total_leave_days = $total_leave_days;
        $leave->leave_duration = $request->leave_duration;
        $leave->leave_session = $request->leave_session ?? null;
        $leave->is_paid = $isPaidLeave;
        $leave->is_lop = !$isPaidLeave;
        $leave->leave_reason = $request->leave_reason;
        $leave->remark = $request->remark ?? null;
        $leave->status = 'Pending';
        $leave->created_by = \Auth::user()->creatorId();
        $leave->save();

        // Google calendar sync
        if ($request->get('synchronize_type') == 'google_calender') {
            $type = 'leave';
            $request1 = new GoogleEvent();
            $request1->title = 'Leave';
            $request1->start_date = $request->start_date;
            $request1->end_date = $request->end_date;
            Utility::addCalendarData($request1, $type);
        }

        // Send notifications to company and HR users
        $employee = Employee::find($request->employee_id);
        
        // Get all company and HR users
        $companyAndHrUsers = User::where('created_by', '=', \Auth::user()->creatorId())
            ->whereIn('type', ['company', 'hr'])
            ->get();
        
        // Also include the creator if they are company type
        $creator = User::find(\Auth::user()->creatorId());
        if ($creator && $creator->type == 'company') {
            $companyAndHrUsers->push($creator);
        }
        
        // Send notification to each company and HR user
        foreach ($companyAndHrUsers as $user) {
            $notificationData = [
                'leave_id' => $leave->id,
                'message' => $employee->name . ' has applied for leave from ' . 
                            \Auth::user()->dateFormat($leave->start_date) . ' to ' . 
                            \Auth::user()->dateFormat($leave->end_date),
                'status' => 'Pending',
                'url' => route('leave.action', $leave->id),
            ];
            
            $user->notify(new LeaveActionNotification($notificationData));
        }

        // Send notification to reporting manager if assigned
        if ($employee && $employee->reporting_manager) {
            $reportingManager = Employee::find($employee->reporting_manager);
            
            if ($reportingManager && $reportingManager->user_id) {
                $reportingManagerUser = User::find($reportingManager->user_id);
                
                if ($reportingManagerUser) {
                    $notificationData = [
                        'leave_id' => $leave->id,
                        'message' => $employee->name . ' has applied for leave from ' . 
                                    \Auth::user()->dateFormat($leave->start_date) . ' to ' . 
                                    \Auth::user()->dateFormat($leave->end_date),
                        'status' => 'Pending',
                        'url' => route('leave.view', $leave->id),
                    ];
                    
                    $reportingManagerUser->notify(new LeaveActionNotification($notificationData));
                }
            }
        }

        // Create success message based on leave type
        $successMessage = __('Leave successfully created.');
        if ($isPaidLeave) {
            $successMessage .= ' ' . __('This leave will be deducted from your annual leave balance.');
        } else {
            $successMessage .= ' ' . __('This leave is marked as LOP (Loss of Pay) as you have exceeded the monthly limit of 2 paid leaves.');
        }

        return redirect()->route('leave.index')->with('success', $successMessage);
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

    public function show(LocalLeave $leave)
    {
        return redirect()->route('leave.index');
    }

    public function edit(LocalLeave $leave)
    {
        if (\Auth::user()->can('Edit Leave')) {
            if ($leave->created_by == \Auth::user()->creatorId()) {

                if (Auth::user()->type == 'employee') {
                    $employees = Employee::where('employee_id', '=', \Auth::user()->creatorId())->first();
                } else {
                    $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                }

                return view('leave.edit', compact('leave', 'employees'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $leave)
    {
        $leave = LocalLeave::find($leave);
        if (\Auth::user()->can('Edit Leave')) {
            if ($leave->created_by == Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id' => 'required',
                        'leave_duration' => 'required|in:Full Day,Half Day',
                        'start_date' => 'required',
                        'end_date' => 'required',
                        'leave_reason' => 'required',
                    ]
                );

                // Validate leave_session for Half Day
                if ($request->leave_duration == 'Half Day') {
                    $validator->after(function ($validator) use ($request) {
                        if (empty($request->leave_session) || !in_array($request->leave_session, ['First Half', 'Second Half'])) {
                            $validator->errors()->add('leave_session', __('Please select a session for Half Day leave.'));
                        }
                    });
                }

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                // Get default leave type
                $leave_type = $this->getDefaultLeaveType();
                $employeeId = \Auth::user()->type == 'employee' 
                    ? Employee::where('user_id', '=', \Auth::user()->id)->first()->id 
                    : $request->employee_id;

                // Calculate total leave days based on duration
                $leaveDuration = $request->leave_duration;
                if ($leaveDuration == 'Half Day') {
                    $total_leave_days = 0.5;
                    $request->merge(['end_date' => $request->start_date]);
                } else {
                    $startDate = new \DateTime($request->start_date);
                    $endDate = new \DateTime($request->end_date);
                    $endDate->add(new \DateInterval('P1D'));
                    $total_leave_days = $startDate->diff($endDate)->days;
                }

                // Calculate pro-rata leave entitlement
                $proRataLeaves = $this->calculateProRataLeaves($employeeId);
                
                $date = Utility::AnnualLeaveCycle();

                // Leave day calculations (excluding current leave) - Only count PAID leaves
                $leaves_used = LocalLeave::whereNotIn('id', [$leave->id])
                    ->where('employee_id', '=', $employeeId)
                    ->where('leave_type_id', $leave_type->id)
                    ->where('is_paid', true) // Only count PAID leaves
                    ->where('status', 'Approved')
                    ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                    ->sum('total_leave_days');

                $leaves_pending = LocalLeave::whereNotIn('id', [$leave->id])
                    ->where('employee_id', '=', $employeeId)
                    ->where('leave_type_id', $leave_type->id)
                    ->where('is_paid', true) // Only count PAID leaves
                    ->where('status', 'Pending')
                    ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                    ->sum('total_leave_days');

                // Use pro-rata leaves (15 per year, calculated based on joining date)
                $return = $proRataLeaves - $leaves_used;

                if ($total_leave_days > $return) {
                    return redirect()->back()->with('error', __('You are not eligible for leave. Your pro-rata entitlement is '.$proRataLeaves.' days.'));
                }

                if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $return) {
                    return redirect()->back()->with('error', __('Multiple leave entry is pending.'));
                }


                // Use the user's explicit selection for leave type
                $isPaidLeave = ($request->leave_type_selection == 'paid');

                if ($proRataLeaves >= $total_leave_days) {
                    if (\Auth::user()->type == 'employee') {
                        $employee = Employee::where('user_id', '=', \Auth::user()->id)->first();
                        $leave->employee_id = $employee->id;
                    } else {
                        $leave->employee_id = $request->employee_id;
                    }
                    $leave->leave_type_id = $leave_type->id; // Use default leave type
                    $leave->start_date = $request->start_date;
                    $leave->end_date = $request->end_date;
                    $leave->total_leave_days = $total_leave_days;
                    $leave->leave_duration = $request->leave_duration;
                    $leave->leave_session = $request->leave_session ?? null;
                    $leave->is_paid = $isPaidLeave;
                    $leave->is_lop = !$isPaidLeave;
                    $leave->leave_reason = $request->leave_reason;
                    $leave->remark = $request->remark ?? null;

                    $leave->save();

                    return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
                } else {
                    return redirect()->back()->with('error', __('You cannot take more than '.$proRataLeaves.' days. Your pro-rata entitlement is '.$proRataLeaves.' days.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(LocalLeave $leave)
    {
        if (\Auth::user()->can('Delete Leave')) {
            if ($leave->created_by == \Auth::user()->creatorId()) {
                $leave->delete();

                return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export()
    {
        $name = 'leave_' . date('Y-m-d i:h:s');
        $data = Excel::download(new LeaveExport(), $name . '.xlsx');

        return $data;
    }

    public function action($id)
    {
        $leave     = LocalLeave::find($id);
        $employee  = Employee::find($leave->employee_id);
        $currentUser = \Auth::user();
        
        $currentEmployee = Employee::where('user_id', $currentUser->id)->first();
        $isDepartmentManager = false;
        if ($currentEmployee && $currentEmployee->designation) {
            $isDepartmentManager = str_contains(strtolower($currentEmployee->designation->name), 'manager');
        }

        // Check if the leave belongs to someone in their own department
        if ($isDepartmentManager && $employee && $employee->department_id == $currentEmployee->department_id) {
            // Cannot approve their own leave request
            if ($leave->employee_id == $currentEmployee->id) {
                return redirect()->route('leave.index')->with('error', __('You cannot approve your own leave request.'));
            }
            // Allow access to action page for department employees
            return view('leave.action', compact('employee', 'leave', 'isDepartmentManager'));
        }

        // Check if current user is a reporting manager (view-only access)
        if ($employee && $employee->reporting_manager) {
            $reportingManager = Employee::find($employee->reporting_manager);
            if ($reportingManager && $reportingManager->user_id == \Auth::user()->id) {
                // Redirect reporting managers to view-only page
                return redirect()->route('leave.view', $id);
            }
        }

        return view('leave.action', compact('employee', 'leave', 'isDepartmentManager'));
    }

    public function view($id)
    {
        $leave     = LocalLeave::find($id);
        $employee  = Employee::find($leave->employee_id);

        // Verify that current user is the reporting manager for this leave
        if ($employee && $employee->reporting_manager) {
            $reportingManager = Employee::find($employee->reporting_manager);
            if ($reportingManager && $reportingManager->user_id == \Auth::user()->id) {
                return view('leave.view', compact('employee', 'leave'));
            }
        }

        // If not the reporting manager, redirect to leave index
        return redirect()->route('leave.index')->with('error', __('Permission denied.'));
    }

    public function changeaction(Request $request)
    {
        $leave = LocalLeave::find($request->leave_id);
        $currentUser = \Auth::user();
        
        $currentEmployee = Employee::where('user_id', $currentUser->id)->first();
        $isDepartmentManager = false;
        if ($currentEmployee && $currentEmployee->designation) {
            $isDepartmentManager = str_contains(strtolower($currentEmployee->designation->name), 'manager');
        }
        
        // If department manager, authorize they can only approve/reject department employee leaves (not themselves)
        if ($isDepartmentManager) {
            $leaveEmployee = Employee::find($leave->employee_id);
            if (!$leaveEmployee || $leaveEmployee->department_id != $currentEmployee->department_id || $leave->employee_id == $currentEmployee->id) {
                return redirect()->route('leave.index')->with('error', __('Permission denied.'));
            }
        }

        $leave->status = $request->status;
        
        if ($leave->status == 'Approved') {
            $total_leave_days = $leave->total_leave_days;
            
            // Check monthly paid leaves limit (only 2 paid leaves per month allowed)
            $now = now();
            $monthlyPaidLeavesUsed = LocalLeave::whereNotIn('id', [$leave->id])
                ->where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('is_paid', true)
                ->where('status', 'Approved')
                ->whereYear('start_date', $now->year)
                ->whereMonth('start_date', $now->month)
                ->sum('total_leave_days');

            $monthlyLimit = 2;
            // Determine if this leave should be paid or LOP
            $isPaidLeave = true;
            if ($monthlyPaidLeavesUsed >= $monthlyLimit) {
                $isPaidLeave = false;
            } else {
                // Check if this leave would exceed 2 paid leaves this month
                if ($monthlyPaidLeavesUsed + $total_leave_days > $monthlyLimit) {
                    $isPaidLeave = false;
                }
            }

            // Update paid/LOP status
            $leave->is_paid = $isPaidLeave;
            $leave->is_lop = !$isPaidLeave;
            
            $now = now();
            $balance = EmployeeLeaveBalance::where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', $now->year)
                ->where('month', $now->month)
                ->first();
                
            if ($balance) {
                $balance->used_days += $total_leave_days;
                $balance->save();
            }
        }
        
        $leave->save();

        // twilio
        $setting = Utility::settings(\Auth::user()->creatorId());
        $emp = Employee::find($leave->employee_id);
        if (isset($setting['twilio_leave_approve_notification']) && $setting['twilio_leave_approve_notification'] == 1) {
            // $msg = __("Your leave has been") . ' ' . $leave->status . '.';

            $uArr = [
                'leave_status' => $leave->status,
            ];


            Utility::send_twilio_msg($emp->phone, 'leave_approve_reject', $uArr);
        }

        // Send in-app notification to the employee about leave status change
        if ($emp && $emp->user_id) {
            $employeeUser = User::find($emp->user_id);
            if ($employeeUser) {
                $leaveType = LeaveType::find($leave->leave_type_id);
                $notificationData = [
                    'leave_id' => $leave->id,
                    'message' => 'Your leave request for ' . $leaveType->title . ' from ' . 
                                \Auth::user()->dateFormat($leave->start_date) . ' to ' . 
                                \Auth::user()->dateFormat($leave->end_date) . ' has been ' . $leave->status,
                    'status' => $leave->status,
                    'url' => route('leave.index'),
                ];
                
                $employeeUser->notify(new LeaveActionNotification($notificationData));
            }
        }

        $setings = Utility::settings();

        if ($setings['leave_status'] == 1) {
            $employee     = Employee::where('id', $leave->employee_id)->where('created_by', '=', \Auth::user()->creatorId())->first();

            $uArr = [
                'leave_email' => $employee->email,
                'leave_status_name' => $employee->name,
                'leave_status' => $request->status,
                'leave_reason' => $leave->leave_reason,
                'leave_start_date' => $leave->start_date,
                'leave_end_date' => $leave->end_date,
                'total_leave_days' => $leave->total_leave_days,

            ];
            $resp = Utility::sendEmailTemplate('leave_status', [$employee->email], $uArr);
            return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.'));
    }

    public function bulkApprove(Request $request)
    {
        $currentUser = \Auth::user();
        $currentEmployee = Employee::where('user_id', $currentUser->id)->first();
        $isDepartmentManager = false;
        if ($currentEmployee && $currentEmployee->designation) {
            $isDepartmentManager = str_contains(strtolower($currentEmployee->designation->name), 'manager');
        }

        if ($currentUser->type != 'company' && $currentUser->type != 'hr' && !$isDepartmentManager) {
            $errorMsg = __('Permission denied.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 403);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $ids = $request->ids;
        if (empty($ids) || !is_array($ids)) {
            $errorMsg = __('No requests selected.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $successCount = 0;
        foreach ($ids as $id) {
            $leave = LocalLeave::find($id);
            if (!$leave) {
                continue;
            }

            if ($isDepartmentManager) {
                $leaveEmployee = Employee::find($leave->employee_id);
                if (!$leaveEmployee || $leaveEmployee->department_id != $currentEmployee->department_id || $leave->employee_id == $currentEmployee->id) {
                    continue;
                }
            } else {
                $leaveEmployee = Employee::find($leave->employee_id);
                if (!$leaveEmployee || $leaveEmployee->created_by != \Auth::user()->creatorId()) {
                    continue;
                }
            }

            if ($leave->status != 'Pending') {
                continue;
            }

            $leave->status = 'Approved';
            
            // Re-apply the logic from changeaction for 'Approved' status
            $total_leave_days = $leave->total_leave_days;
            
            $now = now();
            $monthlyPaidLeavesUsed = LocalLeave::whereNotIn('id', [$leave->id])
                ->where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('is_paid', true)
                ->where('status', 'Approved')
                ->whereYear('start_date', $now->year)
                ->whereMonth('start_date', $now->month)
                ->sum('total_leave_days');

            $monthlyLimit = 2;
            $isPaidLeave = true;
            if ($monthlyPaidLeavesUsed >= $monthlyLimit) {
                $isPaidLeave = false;
            } else {
                if ($monthlyPaidLeavesUsed + $total_leave_days > $monthlyLimit) {
                    $isPaidLeave = false;
                }
            }

            $leave->is_paid = $isPaidLeave;
            $leave->is_lop = !$isPaidLeave;
            
            $balance = EmployeeLeaveBalance::where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', $now->year)
                ->where('month', $now->month)
                ->first();
                
            if ($balance) {
                $balance->used_days += $total_leave_days;
                $balance->save();
            }
            
            $leave->save();

            // notifications
            $setting = Utility::settings(\Auth::user()->creatorId());
            $emp = Employee::find($leave->employee_id);
            if (isset($setting['twilio_leave_approve_notification']) && $setting['twilio_leave_approve_notification'] == 1) {
                $uArr = [
                    'leave_status' => $leave->status,
                ];
                Utility::send_twilio_msg($emp->phone, 'leave_approve_reject', $uArr);
            }

            if ($emp && $emp->user_id) {
                $employeeUser = User::find($emp->user_id);
                if ($employeeUser) {
                    $leaveType = LeaveType::find($leave->leave_type_id);
                    $notificationData = [
                        'leave_id' => $leave->id,
                        'message' => 'Your leave request for ' . ($leaveType ? $leaveType->title : 'Leave') . ' from ' . 
                                    \Auth::user()->dateFormat($leave->start_date) . ' to ' . 
                                    \Auth::user()->dateFormat($leave->end_date) . ' has been ' . $leave->status,
                        'status' => $leave->status,
                        'url' => route('leave.index'),
                    ];
                    
                    $employeeUser->notify(new LeaveActionNotification($notificationData));
                }
            }

            $successCount++;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __(':count leave requests approved successfully.', ['count' => $successCount]),
                'redirect' => route('leave.index')
            ]);
        }

        return redirect()->route('leave.index')->with('success', __(':count leave requests approved successfully.', ['count' => $successCount]));
    }

    public function jsoncount(Request $request)
    {
        $date = Utility::AnnualLeaveCycle();
        $leave_counts = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_types.title, leave_types.days,leave_types.id'))
            ->leftjoin(
                'leaves',
                function ($join) use ($request, $date) {
                    $join->on('leaves.leave_type_id', '=', 'leave_types.id');
                    $join->where('leaves.employee_id', '=', $request->employee_id);
                    $join->where('leaves.is_paid', '=', true); // Only count PAID leaves
                    $join->where('leaves.status', '=', 'Approved');
                    $join->whereBetween('leaves.created_at', [$date['start_date'],$date['end_date']]);
                }
            )->where('leave_types.created_by', '=', \Auth::user()->creatorId())->groupBy('leave_types.id')->get();
        return $leave_counts;
    }

    public function calender(Request $request)
    {
        $created_by = \Auth::user()->creatorId();
        $Meetings = LocalLeave::where('created_by', $created_by)->get();

        $today_date = date('m');
        $current_month_event = LocalLeave::select('id', 'start_date', 'employee_id', 'created_at')->whereRaw('MONTH(start_date)=' . $today_date)->get();

        $arrMeeting = [];

        foreach ($Meetings as $meeting) {
            $arr['id']        = $meeting['id'];
            $arr['employee_id']     = $meeting['employee_id'];
            // $arr['leave_type_id']     = date('Y-m-d', strtotime($meeting['start_date']));
        }

        $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->get();
        if (\Auth::user()->type == 'employee') {
            $user     = \Auth::user();
            $employee = Employee::where('user_id', '=', $user->id)->first();
            $leaves   = LocalLeave::where('employee_id', '=', $employee->id)->get();
        } else {
            $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->get();
        }

        return view('leave.calender', compact('leaves'));
    }

    public function get_leave_data(Request $request)
    {
        $arrayJson = [];
        if ($request->get('calender_type') == 'google_calender') {
            $type = 'leave';
            $arrayJson =  Utility::getCalendarData($type);
        } else {
            $data = LocalLeave::where('created_by', \Auth::user()->creatorId())->get();

            foreach ($data as $val) {
                $end_date = date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => !empty(\Auth::user()->getLeaveType($val->leave_type_id)) ? \Auth::user()->getLeaveType($val->leave_type_id)->title : '',
                    "start" => $val->start_date,
                    "end" => date_format($end_date, "Y-m-d H:i:s"),
                    "className" => $val->color,
                    "textColor" => '#FFF',
                    "allDay" => true,
                    "url" => route('leave.action', $val['id']),
                ];
            }
        }

        return $arrayJson;
    }

    public function leaveDetails(Request $request)
    {
        $currentUser = \Auth::user();
        $isManagerAccess = false;
        $managerDepartmentId = null;
        
        // Check if user has permission (Company user or HR department)
        if ($currentUser->type != 'company' && 
            !($currentUser->type == 'employee' && $currentUser->employee && $currentUser->employee->department && strcasecmp($currentUser->employee->department->name, 'Human Resources') == 0)) {
            
            // Check if current user is a reporting manager or has Manager designation
            $currentEmployee = Employee::where('user_id', $currentUser->id)->first();
            if ($currentEmployee) {
                // Check if employee has Manager designation
                $isManager = $currentEmployee->designation && 
                            strcasecmp($currentEmployee->designation->name, 'Manager') == 0;
                
                $reportingEmployees = Employee::where('reporting_manager', $currentEmployee->id)
                    ->where('created_by', $currentUser->creatorId())
                    ->exists();
                
                if ($isManager || $reportingEmployees) {
                    $isManagerAccess = true;
                    // For Manager designation, restrict to their own department
                    if ($isManager) {
                        $managerDepartmentId = $currentEmployee->department_id;
                    }
                } else {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        $departments = \App\Models\Department::where('created_by', $currentUser->creatorId())->get();
        
        // If this is a Manager designation user, only show their own department
        if ($isManagerAccess && $managerDepartmentId) {
            $departments = $departments->where('id', $managerDepartmentId);
        }
        $employees = [];
        $leaveDetails = [];

        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));
        $selectedDepartment = $request->get('department');
        $selectedEmployee = $request->get('employee');

        // Get employees based on filters and user permissions
        $query = Employee::where('created_by', $currentUser->creatorId());
        
        // Exclude terminated employees
        $terminatedEmployees = \App\Models\Termination::pluck('employee_id')->toArray();
        if (!empty($terminatedEmployees)) {
            $query->whereNotIn('id', $terminatedEmployees);
        }
        
        // If manager access, exclude the manager's own record
        if ($isManagerAccess) {
            $currentEmployee = Employee::where('user_id', $currentUser->id)->first();
            if ($currentEmployee) {
                $query->where('id', '!=', $currentEmployee->id);
            }
        }
        
        // If manager access, apply appropriate restrictions
        if ($isManagerAccess) {
            $currentEmployee = Employee::where('user_id', $currentUser->id)->first();
            
            // Check if this is a Manager designation (not just reporting manager)
            $isManagerDesignation = $currentEmployee->designation && 
                                  strcasecmp($currentEmployee->designation->name, 'Manager') == 0;
            
            if ($isManagerDesignation && $managerDepartmentId) {
                // For Manager designation, restrict to their own department
                $query->where('department_id', $managerDepartmentId);
            } else {
                // For reporting managers, show their reporting employees
                $query->where('reporting_manager', $currentEmployee->id);
            }
        }
        
        if ($selectedDepartment) {
            $query->where('department_id', $selectedDepartment);
            $employees = $query->get();
        } elseif ($selectedEmployee) {
            $query->where('id', $selectedEmployee);
            $employees = $query->get();
        } else {
            $employees = $query->get();
        }

        // Get leave type for calculations
        $leaveType = $this->getDefaultLeaveType();

        foreach ($employees as $employee) {
            // Calculate yearly leaves (pro-rata)
            $yearlyLeaves = $this->calculateProRataLeaves($employee->id);
            
            // Monthly leaves (fixed at 2)
            $monthlyLeaves = 2;
            
            // Leaves taken this month
            $leavesTakenThisMonth = LocalLeave::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'Approved')
                ->whereYear('start_date', $selectedYear)
                ->whereMonth('start_date', $selectedMonth)
                ->sum('total_leave_days');
            
            // Pending leaves
            $pendingLeaves = LocalLeave::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'Pending')
                ->whereYear('start_date', $selectedYear)
                ->whereMonth('start_date', $selectedMonth)
                ->sum('total_leave_days');

            // Yearly total PAID leaves used (approved) - LOP leaves are NOT counted
            $date = \App\Models\Utility::AnnualLeaveCycle();
            $yearlyUsed = LocalLeave::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('is_paid', true) // Only count PAID leaves
                ->where('status', 'Approved')
                ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                ->sum('total_leave_days');

            $leaveDetails[] = [
                'employee' => $employee,
                'yearly_leaves' => $yearlyLeaves,
                'monthly_leaves' => $monthlyLeaves,
                'leaves_taken' => $leavesTakenThisMonth,
                'pending_leaves' => $pendingLeaves,
                'remaining_monthly' => $monthlyLeaves - $leavesTakenThisMonth,
                'remaining_yearly' => $yearlyLeaves - $yearlyUsed
            ];
        }

        return view('leave.leave_details', compact(
            'leaveDetails', 
            'departments', 
            'employees',
            'selectedMonth',
            'selectedYear',
            'selectedDepartment',
            'selectedEmployee',
            'isManagerAccess'
        ));
    }

    public function getEmployeesByDepartment(Request $request)
    {
        $currentUser = \Auth::user();
        $isManagerAccess = false;
        $managerDepartmentId = null;
        
        // Check if this is manager access
        if ($currentUser->type != 'company' && 
            !($currentUser->type == 'employee' && $currentUser->employee && $currentUser->employee->department && strcasecmp($currentUser->employee->department->name, 'Human Resources') == 0)) {
            
            $currentEmployee = Employee::where('user_id', $currentUser->id)->first();
            if ($currentEmployee) {
                // Check if employee has Manager designation
                $isManager = $currentEmployee->designation && 
                            strcasecmp($currentEmployee->designation->name, 'Manager') == 0;
                
                $reportingEmployees = Employee::where('reporting_manager', $currentEmployee->id)
                    ->where('created_by', $currentUser->creatorId())
                    ->exists();
                
                if ($isManager || $reportingEmployees) {
                    $isManagerAccess = true;
                    // For Manager designation, restrict to their own department
                    if ($isManager) {
                        $managerDepartmentId = $currentEmployee->department_id;
                    }
                }
            }
        }
        
        $query = Employee::where('department_id', $request->get('department_id'))
            ->where('created_by', $currentUser->creatorId());
        
        // Exclude terminated employees
        $terminatedEmployees = \App\Models\Termination::pluck('employee_id')->toArray();
        if (!empty($terminatedEmployees)) {
            $query->whereNotIn('id', $terminatedEmployees);
        }
        
        // If manager access, exclude the manager's own record
        if ($isManagerAccess) {
            $currentEmployee = Employee::where('user_id', $currentUser->id)->first();
            if ($currentEmployee) {
                $query->where('id', '!=', $currentEmployee->id);
            }
        }
            
        // If manager access, apply appropriate restrictions
        if ($isManagerAccess) {
            $currentEmployee = Employee::where('user_id', $currentUser->id)->first();
            
            // Check if this is a Manager designation (not just reporting manager)
            $isManagerDesignation = $currentEmployee->designation && 
                                  strcasecmp($currentEmployee->designation->name, 'Manager') == 0;
            
            if ($isManagerDesignation && $managerDepartmentId) {
                // For Manager designation, only allow their own department
                if ($request->get('department_id') != $managerDepartmentId) {
                    return response()->json([]); // Return empty if trying to access other departments
                }
            } else {
                // For reporting managers, show their reporting employees
                $query->where('reporting_manager', $currentEmployee->id);
            }
        }
        
        $employees = $query->get(['id', 'name']);
        
        return response()->json($employees);
    }
}
