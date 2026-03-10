<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRegularisation;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\AttendanceRegularisationNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceRegularisationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (\Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', \Auth::user()->id)->first();
            if (!$employee) {
                return redirect()->back()->with('error', __('Employee not found.'));
            }
            
            $regularisations = AttendanceRegularisation::where('employee_id', $employee->id)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Company/HR users can see all regularisations
            $regularisations = AttendanceRegularisation::where('created_by', \Auth::user()->creatorId())
                ->orWhereHas('employee', function($query) {
                    $query->where('created_by', \Auth::user()->creatorId());
                })
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->with('employee')
                ->get();
        }

        return view('attendance.regularisation.index', compact('regularisations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (\Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', \Auth::user()->id)->first();
            if (!$employee) {
                return redirect()->back()->with('error', __('Employee not found.'));
            }
            $employees = collect([$employee]);
        } else {
            return redirect()->back()->with('error', __('Only employees can create regularisation requests.'));
        }

        return view('attendance.regularisation.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'date' => 'required|date',
                'punch_in_time' => 'required',
                'punch_out_time' => 'required',
                'reason' => 'required|in:Missed Punch,Technical Error,Other',
                'remarks' => 'nullable|string|max:1000',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // Get employee
        if (\Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', \Auth::user()->id)->first();
            if (!$employee) {
                return redirect()->back()->with('error', __('Employee not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Only employees can create regularisation requests.'));
        }

        // Check if attendance already exists for this date (we'll allow it now)
        $existingAttendance = AttendanceEmployee::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->first();

        // Store whether this is an update to existing attendance
        $isUpdatingExisting = !empty($existingAttendance);

        // Check if a regularisation request already exists for this date
        $existingRegularisation = AttendanceRegularisation::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->where('status', 'Pending')
            ->first();

        if ($existingRegularisation) {
            return redirect()->back()->with('error', __('A pending regularisation request already exists for this date.'));
        }

        // Format times
        $punchInTime = date('H:i:s', strtotime($request->punch_in_time));
        $punchOutTime = date('H:i:s', strtotime($request->punch_out_time));

        // Validate that punch out is after punch in
        if (strtotime($punchOutTime) <= strtotime($punchInTime)) {
            return redirect()->back()->with('error', __('Punch out time must be after punch in time.'));
        }

        // Create regularisation request
        $regularisation = new AttendanceRegularisation();
        $regularisation->employee_id = $employee->id;
        $regularisation->date = $request->date;
        $regularisation->punch_in_time = $punchInTime;
        $regularisation->punch_out_time = $punchOutTime;
        $regularisation->reason = $request->reason;
        $regularisation->remarks = $request->remarks;
        $regularisation->status = AttendanceRegularisation::STATUS_PENDING;
        $regularisation->created_by = \Auth::user()->creatorId();
        $regularisation->is_updating_existing = $isUpdatingExisting;
        $regularisation->save();

        // Send notifications to Company and HR users
        $this->sendNotifications($regularisation, $employee);

        return redirect()->route('attendance-regularisation.index')->with('success', __('Attendance regularisation request submitted successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $regularisation = AttendanceRegularisation::with('employee')->findOrFail($id);

        // Check permissions
        if (\Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', \Auth::user()->id)->first();
            if ($regularisation->employee_id != $employee->id) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            if ($regularisation->created_by != \Auth::user()->creatorId() && 
                $regularisation->employee->created_by != \Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        return view('attendance.regularisation.show', compact('regularisation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Only employees can update their own requests
        if (\Auth::user()->type != 'employee') {
            $errorMsg = __('Permission denied. Only employees can edit their requests.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 403);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'date' => 'required|date',
                'punch_in_time' => 'required',
                'punch_out_time' => 'required',
                'reason' => 'required|in:Missed Punch,Technical Error,Other',
                'remarks' => 'nullable|string|max:1000',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            $errorMsg = $messages->first();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Get employee
        $employee = Employee::where('user_id', \Auth::user()->id)->first();
        if (!$employee) {
            $errorMsg = __('Employee not found.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 404);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Find the regularisation request
        $regularisation = AttendanceRegularisation::where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$regularisation) {
            $errorMsg = __('Regularisation request not found or you do not have permission to edit it.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 404);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Check if request is still pending
        if ($regularisation->status != AttendanceRegularisation::STATUS_PENDING) {
            $errorMsg = __('Only pending requests can be edited.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Check if attendance already exists for this date (we'll allow it for editing)
        $existingAttendance = AttendanceEmployee::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->first();

        // Store whether this is an update to existing attendance
        $isUpdatingExisting = !empty($existingAttendance);

        // Check if another regularisation request already exists for this date (excluding current one)
        $existingRegularisation = AttendanceRegularisation::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->where('status', 'Pending')
            ->where('id', '!=', $id)
            ->first();

        if ($existingRegularisation) {
            $errorMsg = __('A pending regularisation request already exists for this date.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Format times
        $punchInTime = date('H:i:s', strtotime($request->punch_in_time));
        $punchOutTime = date('H:i:s', strtotime($request->punch_out_time));

        // Validate that punch out is after punch in
        if (strtotime($punchOutTime) <= strtotime($punchInTime)) {
            $errorMsg = __('Punch out time must be after punch in time.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Update regularisation request
        $regularisation->date = $request->date;
        $regularisation->punch_in_time = $punchInTime;
        $regularisation->punch_out_time = $punchOutTime;
        $regularisation->reason = $request->reason;
        $regularisation->remarks = $request->remarks;
        $regularisation->is_updating_existing = $isUpdatingExisting;
        $regularisation->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Attendance regularisation request updated successfully.'),
                'redirect' => route('attendance-regularisation.index')
            ]);
        }

        return redirect()->route('attendance-regularisation.index')->with('success', __('Attendance regularisation request updated successfully.'));
    }

    /**
     * Approve the regularisation request
     */
    public function approve(Request $request, $id)
    {
        // Only Company and HR users can approve
        if (\Auth::user()->type != 'company' && \Auth::user()->type != 'hr') {
            $errorMsg = __('Permission denied. Only Company and HR users can approve requests.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 403);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $regularisation = AttendanceRegularisation::with('employee')->findOrFail($id);

        // Check if request belongs to the same company
        if ($regularisation->employee->created_by != \Auth::user()->creatorId()) {
            $errorMsg = __('Permission denied.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 403);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        if ($regularisation->status != AttendanceRegularisation::STATUS_PENDING) {
            $errorMsg = __('This request has already been processed.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Check if attendance already exists for this date
        $existingAttendance = AttendanceEmployee::where('employee_id', $regularisation->employee_id)
            ->where('date', $regularisation->date)
            ->first();

        if ($existingAttendance) {
            // Update existing attendance record
            $this->updateAttendanceFromRegularisation($regularisation, $existingAttendance);
        } else {
            // Create new attendance record
            $this->createAttendanceFromRegularisation($regularisation);
        }

        // Update regularisation status
        $regularisation->status = AttendanceRegularisation::STATUS_APPROVED;
        $regularisation->approved_by = \Auth::user()->id;
        $regularisation->approved_at = now();
        $regularisation->save();

        // Send notification to employee
        $this->sendApprovalNotification($regularisation);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Attendance regularisation request approved successfully.'),
                'redirect' => route('attendance-regularisation.index')
            ]);
        }

        return redirect()->route('attendance-regularisation.index')->with('success', __('Attendance regularisation request approved successfully.'));
    }

    /**
     * Reject the regularisation request
     */
    public function reject(Request $request, $id)
    {
        // Only Company and HR users can reject
        if (\Auth::user()->type != 'company' && \Auth::user()->type != 'hr') {
            $errorMsg = __('Permission denied. Only Company and HR users can reject requests.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 403);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'rejection_reason' => 'required|string|max:1000',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            $errorMsg = $messages->first();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $regularisation = AttendanceRegularisation::with('employee')->findOrFail($id);

        // Check if request belongs to the same company
        if ($regularisation->employee->created_by != \Auth::user()->creatorId()) {
            $errorMsg = __('Permission denied.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 403);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        if ($regularisation->status != AttendanceRegularisation::STATUS_PENDING) {
            $errorMsg = __('This request has already been processed.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Update regularisation status
        $regularisation->status = AttendanceRegularisation::STATUS_REJECTED;
        $regularisation->approved_by = \Auth::user()->id;
        $regularisation->approved_at = now();
        $regularisation->rejection_reason = $request->rejection_reason;
        $regularisation->save();

        // Send notification to employee
        $this->sendRejectionNotification($regularisation);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Attendance regularisation request rejected.'),
                'redirect' => route('attendance-regularisation.index')
            ]);
        }

        return redirect()->route('attendance-regularisation.index')->with('success', __('Attendance regularisation request rejected.'));
    }

    /**
     * Send notifications to Company and HR users
     */
    private function sendNotifications($regularisation, $employee)
    {
        // Get all company and HR users
        $companyAndHrUsers = User::where('created_by', '=', \Auth::user()->creatorId())
            ->whereIn('type', ['company', 'hr'])
            ->get();

        // Also include the creator if they are company type
        $creator = User::find(\Auth::user()->creatorId());
        if ($creator && $creator->type == 'company') {
            $companyAndHrUsers->push($creator);
        }

        // Remove duplicates
        $companyAndHrUsers = $companyAndHrUsers->unique('id');

        // Send notification to each company and HR user
        foreach ($companyAndHrUsers as $user) {
            $notificationData = [
                'regularisation_id' => $regularisation->id,
                'message' => $employee->name . ' has submitted an attendance regularisation request for ' . 
                            \Carbon\Carbon::parse($regularisation->date)->format('d M Y'),
                'status' => 'Pending',
                'url' => route('attendance-regularisation.show', $regularisation->id),
            ];

            $user->notify(new AttendanceRegularisationNotification($notificationData));
        }
    }

    /**
     * Send approval notification to employee
     */
    private function sendApprovalNotification($regularisation)
    {
        $employee = $regularisation->employee;
        if ($employee && $employee->user) {
            $notificationData = [
                'regularisation_id' => $regularisation->id,
                'message' => 'Your attendance regularisation request for ' . 
                            \Carbon\Carbon::parse($regularisation->date)->format('d M Y') . ' has been approved.',
                'status' => 'Approved',
                'url' => route('attendance-regularisation.show', $regularisation->id),
            ];

            $employee->user->notify(new AttendanceRegularisationNotification($notificationData));
        }
    }

    /**
     * Send rejection notification to employee
     */
    private function sendRejectionNotification($regularisation)
    {
        $employee = $regularisation->employee;
        if ($employee && $employee->user) {
            $notificationData = [
                'regularisation_id' => $regularisation->id,
                'message' => 'Your attendance regularisation request for ' . 
                            \Carbon\Carbon::parse($regularisation->date)->format('d M Y') . ' has been rejected.',
                'status' => 'Rejected',
                'url' => route('attendance-regularisation.show', $regularisation->id),
            ];

            $employee->user->notify(new AttendanceRegularisationNotification($notificationData));
        }
    }

    /**
     * Update existing attendance record from approved regularisation
     */
    private function updateAttendanceFromRegularisation($regularisation, $existingAttendance)
    {
        // Get date as string (Y-m-d format)
        $date = is_string($regularisation->date) ? $regularisation->date : $regularisation->date->format('Y-m-d');
        
        // Get times as strings (H:i:s format)
        $clockIn = is_string($regularisation->punch_in_time) ? $regularisation->punch_in_time : $regularisation->punch_in_time;
        $clockOut = is_string($regularisation->punch_out_time) ? $regularisation->punch_out_time : $regularisation->punch_out_time;
        
        // Ensure times are in H:i:s format
        if (strlen($clockIn) == 5) {
            $clockIn = $clockIn . ':00'; // Add seconds if missing
        }
        if (strlen($clockOut) == 5) {
            $clockOut = $clockOut . ':00'; // Add seconds if missing
        }

        // Calculate late time based on department-specific punch-in time
        $late = $this->calculateLateTime($clockIn, $date, $regularisation->employee_id);

        // Calculate early leaving
        $endTime = \App\Models\Utility::getValByName('company_end_time');
        $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($date . ' ' . $clockOut);
        $hours = floor($totalEarlyLeavingSeconds / 3600);
        $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
        $secs = floor($totalEarlyLeavingSeconds % 60);
        $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        // Calculate overtime
        if (strtotime($date . ' ' . $clockOut) > strtotime($date . ' ' . $endTime)) {
            $totalOvertimeSeconds = strtotime($date . ' ' . $clockOut) - strtotime($date . ' ' . $endTime);
            $hours = floor($totalOvertimeSeconds / 3600);
            $mins = floor($totalOvertimeSeconds / 60 % 60);
            $secs = floor($totalOvertimeSeconds % 60);
            $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        } else {
            $overtime = '00:00:00';
        }

        // Calculate status using the same logic as AttendanceEmployeeController
        $status = $this->calculateAttendanceStatus($clockIn, $clockOut, $date, $regularisation->employee_id);

        // Update existing attendance record
        $existingAttendance->status = $status;
        $existingAttendance->clock_in = $clockIn;
        $existingAttendance->clock_out = $clockOut;
        $existingAttendance->late = $late;
        $existingAttendance->early_leaving = ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00';
        $existingAttendance->overtime = $overtime;
        $existingAttendance->save();
    }

    /**
     * Create attendance record from approved regularisation
     */
    private function createAttendanceFromRegularisation($regularisation)
    {
        // Get date as string (Y-m-d format)
        $date = is_string($regularisation->date) ? $regularisation->date : $regularisation->date->format('Y-m-d');
        
        // Get times as strings (H:i:s format)
        $clockIn = is_string($regularisation->punch_in_time) ? $regularisation->punch_in_time : $regularisation->punch_in_time;
        $clockOut = is_string($regularisation->punch_out_time) ? $regularisation->punch_out_time : $regularisation->punch_out_time;
        
        // Ensure times are in H:i:s format
        if (strlen($clockIn) == 5) {
            $clockIn = $clockIn . ':00'; // Add seconds if missing
        }
        if (strlen($clockOut) == 5) {
            $clockOut = $clockOut . ':00'; // Add seconds if missing
        }

        // Calculate late time based on department-specific punch-in time
        $late = $this->calculateLateTime($clockIn, $date, $regularisation->employee_id);

        // Calculate early leaving
        $endTime = \App\Models\Utility::getValByName('company_end_time');
        $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($date . ' ' . $clockOut);
        $hours = floor($totalEarlyLeavingSeconds / 3600);
        $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
        $secs = floor($totalEarlyLeavingSeconds % 60);
        $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        // Calculate overtime
        if (strtotime($date . ' ' . $clockOut) > strtotime($date . ' ' . $endTime)) {
            $totalOvertimeSeconds = strtotime($date . ' ' . $clockOut) - strtotime($date . ' ' . $endTime);
            $hours = floor($totalOvertimeSeconds / 3600);
            $mins = floor($totalOvertimeSeconds / 60 % 60);
            $secs = floor($totalOvertimeSeconds % 60);
            $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        } else {
            $overtime = '00:00:00';
        }

        // Calculate status using the same logic as AttendanceEmployeeController
        $status = $this->calculateAttendanceStatus($clockIn, $clockOut, $date, $regularisation->employee_id);

        // Create attendance record
        $attendance = new AttendanceEmployee();
        $attendance->employee_id = $regularisation->employee_id;
        $attendance->date = $date;
        $attendance->status = $status;
        $attendance->clock_in = $clockIn;
        $attendance->clock_out = $clockOut;
        $attendance->late = $late;
        $attendance->early_leaving = ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00';
        $attendance->overtime = $overtime;
        $attendance->total_rest = '00:00:00';
        $attendance->created_by = \Auth::user()->creatorId();
        $attendance->save();
    }

    /**
     * Calculate late time based on department-specific punch-in time
     */
    private function calculateLateTime($clockIn, $date, $employeeId = null)
    {
        if (empty($clockIn) || $clockIn == '00:00:00') {
            return '00:00:00';
        }

        // Ensure date is in Y-m-d format
        if (!is_string($date)) {
            $date = $date->format('Y-m-d');
        }

        // Ensure clockIn is a string in H:i:s format
        $clockInStr = is_string($clockIn) ? $clockIn : $clockIn;
        if (strlen($clockInStr) == 5) {
            $clockInStr = $clockInStr . ':00'; // Add seconds if missing
        }

        // Get department-specific punch-in time if employee ID is provided
        if ($employeeId) {
            $lateMarkTime = AttendanceEmployee::getEmployeePunchInTime($employeeId);
        } else {
            $lateMarkTime = AttendanceEmployee::LATE_MARK_TIME; // Fallback to default
        }
        
        $expectedTime = $date . ' ' . $lateMarkTime;
        $actualTime = $date . ' ' . $clockInStr;

        $totalLateSeconds = max(strtotime($actualTime) - strtotime($expectedTime), 0);

        $hours = floor($totalLateSeconds / 3600);
        $mins = floor($totalLateSeconds / 60 % 60);
        $secs = $totalLateSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    }

    /**
     * Calculate attendance status using new rules
     */
    private function calculateAttendanceStatus($clockIn, $clockOut, $date, $employeeId)
    {
        // If no clock in at all, return Absent
        if (empty($clockIn) || $clockIn == '00:00:00') {
            return AttendanceEmployee::STATUS_ABSENT;
        }

        // If clocked in but not out, return Half Day
        if (empty($clockOut) || $clockOut == '00:00:00') {
            return AttendanceEmployee::STATUS_HALF_DAY;
        }

        // Ensure date is in Y-m-d format
        if (!is_string($date)) {
            $date = $date->format('Y-m-d');
        }

        // Ensure times are strings in H:i:s format
        $clockInStr = is_string($clockIn) ? $clockIn : $clockIn;
        $clockOutStr = is_string($clockOut) ? $clockOut : $clockOut;
        
        if (strlen($clockInStr) == 5) {
            $clockInStr = $clockInStr . ':00';
        }
        if (strlen($clockOutStr) == 5) {
            $clockOutStr = $clockOutStr . ':00';
        }

        // Calculate total worked hours
        $start = Carbon::parse($date . ' ' . $clockInStr);
        $end = Carbon::parse($date . ' ' . $clockOutStr);

        // Handle case where clock out might be next day
        if ($end->lt($start)) {
            $end->addDay();
        }

        $totalMinutes = $end->diffInMinutes($start);
        $workedHours = $totalMinutes / 60;

        // Check if worked hours < 4.5 hours
        if ($workedHours < AttendanceEmployee::HALF_DAY_HOURS_THRESHOLD) {
            return AttendanceEmployee::STATUS_HALF_DAY;
        }

        // Check if employee has more than 3 late marks in the month
        $lateMarksCount = $this->countLateMarksInMonth($employeeId, $date);
        if ($lateMarksCount > AttendanceEmployee::MAX_LATE_MARKS_PER_MONTH) {
            return AttendanceEmployee::STATUS_HALF_DAY;
        }

        // If all conditions pass, check if it's a full day (8.5 hours)
        if ($totalMinutes >= 510) { // 8.5 hours = 510 minutes
            return AttendanceEmployee::STATUS_PRESENT;
        } else {
            return AttendanceEmployee::STATUS_HALF_DAY;
        }
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
     * Fetch existing attendance data for a selected date
     */
    public function fetchAttendanceData(Request $request)
    {
        // Only employees can fetch their own attendance data
        if (\Auth::user()->type != 'employee') {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'date' => 'required|date',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json(['success' => false, 'message' => $messages->first()], 422);
        }

        // Get employee
        $employee = Employee::where('user_id', \Auth::user()->id)->first();
        if (!$employee) {
            return response()->json(['success' => false, 'message' => __('Employee not found.')], 404);
        }

        // Check if attendance already exists for this date
        $existingAttendance = AttendanceEmployee::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => true,
                'has_attendance' => true,
                'data' => [
                    'punch_in_time' => date('H:i', strtotime($existingAttendance->clock_in)),
                    'punch_out_time' => date('H:i', strtotime($existingAttendance->clock_out)),
                    'status' => $existingAttendance->status,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'has_attendance' => false,
            'message' => __('No attendance found for this date.')
        ]);
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
}

