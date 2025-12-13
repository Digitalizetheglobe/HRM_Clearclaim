<?php

namespace App\Http\Controllers;
use App\Models\Unit;
use App\Models\Employee;
use App\Models\TimeSheet;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TimeSheetExport;
use Illuminate\Support\Facades\Validator;

class TimeSheetController extends Controller
{
    
    public function index(Request $request)
    {
        $query = TimeSheet::with(['employee', 'project', 'assignedEmployee']);

        if (Auth::user()->type == 'employee') {
                $userId = Auth::id();
                $employeeId = Auth::user()->employee->id ?? null;
                
                $query->where(function($q) use ($userId, $employeeId) {
                    // Timesheets where current user is the assigned_to employee
                    $q->where('assigned_to', $userId)
                    
                    // OR timesheets where current user is the creator AND not assigned to anyone
                    ->orWhere(function($subQ) use ($userId) {
                        $subQ->where('employee_id', $userId)
                            ->whereNull('assigned_to');
                    })
                    
                    // OR timesheets for projects where user is site head (read-only access)
                    ->orWhereHas('project', function($projectQuery) use ($employeeId) {
                        $projectQuery->where(function($pq) use ($employeeId) {
                            $pq->whereJsonContains('site_heads', (string)$employeeId)
                                ->orWhereJsonContains('site_heads', $employeeId)
                                ->orWhereJsonContains('site_heads', (int)$employeeId);
                        });
                    });
                });
            }   

        // Rest of your filter code remains the same...
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('project') && $request->project != '') {
            $query->where('project_id', $request->project);
        }

        if ($request->filled('client_status') && $request->client_status != 'Select Client Status') {
            $query->where('client_status', $request->client_status);
        }

        // Get results ordered by latest first
        $timeSheets = $query->latest()->get();

        // Store filtered timesheets in session for export
        session(['export_timesheets' => $timeSheets]);

        $employeesList = Employee::pluck('name', 'id')->prepend(__('Select Employee'), '');
        
        // Modified projects list query for site heads (using employee ID)
        if (Auth::user()->type == 'employee') {
            $userId = Auth::id();
            $employeeId = Auth::user()->employee->id ?? null;
            
            $projectsQuery = Project::query();
            
            // Site head projects (using employee ID)
            if ($employeeId) {
                $projectsQuery->where(function($q) use ($employeeId) {
                    $q->whereJsonContains('site_heads', (string)$employeeId)
                    ->orWhereJsonContains('site_heads', $employeeId)
                    ->orWhereJsonContains('site_heads', (int)$employeeId);
                });
            }
            
            // Also include projects where assigned (if needed)
            if ($employeeId) {
                $projectsQuery->orWhere(function($q) use ($employeeId) {
                    $q->whereJsonContains('assigned_data', ['employee_ids' => [(string)$employeeId]])
                    ->orWhereJsonContains('assigned_data', ['employee_ids' => [$employeeId]])
                    ->orWhereJsonContains('assigned_data', ['employee_ids' => (string)$employeeId])
                    ->orWhereJsonContains('assigned_data', ['employee_ids' => $employeeId]);
                });
            }
            
            $projectsList = $projectsQuery->pluck('project_name', 'id')->prepend(__('Select Project'), '');
        } else {
            $projectsList = Project::pluck('project_name', 'id')->prepend(__('Select Project'), '');
        }

        if ($request->ajax()) {
            return view('timesheet.partials.table', compact('timeSheets'));
        }

        return view('timeSheet.index', compact('timeSheets', 'employeesList', 'projectsList'));
    }

    public function create()
    {
        if (Auth::user()->can('Create TimeSheet')) {
            $employees = Employee::pluck('name', 'user_id')->prepend('Select Employee', '');
            $allEmployees = Employee::pluck('name', 'id')->prepend('Select Presale Employee', '');
            
            // Initialize projects array
            $projects = [];
            
            if (Auth::user()->type == 'employee') {
                // Get current employee ID (from employees table id column)
                $employeeId = Auth::user()->employee->id; // Assuming you have employee relationship
                
                // Get all projects and filter those assigned to this employee
                $allProjects = Project::all();
                
                foreach ($allProjects as $project) {
                    if (empty($project->assigned_data)) {
                        continue;
                    }
                    
                    // Check if this employee is assigned to the project
                    $isAssigned = false;
                    $assignedData = $project->assigned_data; // Already an array due to JSON casting
                    
                    foreach ($assignedData as $assignment) {
                        if (isset($assignment['employee_ids']) && 
                            is_array($assignment['employee_ids']) &&
                            in_array($employeeId, $assignment['employee_ids'])) {
                            $isAssigned = true;
                            break;
                        }
                    }
                    
                    if ($isAssigned) {
                        $projects[$project->id] = $project->project_name;
                    }
                }
            } else {
                // For admins/managers, get all projects
                $projects = Project::pluck('project_name', 'id');
            }

            return view('timeSheet.create', compact('employees', 'projects', 'allEmployees'));
        }

        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function store(Request $request)
    {
        \Log::info('Store method called');
        \Log::info($request->all());

        if (!Auth::user()->can('Create TimeSheet')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'full_name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'email_id' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'recommended_by' => 'nullable|string|in:CP,Digital,Hoarding,WayBoard,Walking,LeafLet,Expo,Refrence,Others',
            'cp_data' => 'required_if:recommended_by,CP|nullable|string|max:255',
            'refrence_data' => 'required_if:recommended_by,Refrence|nullable|string|max:255',
            'other_data' => 'required_if:recommended_by,Others|nullable|string|max:255',
            'feedback_information' => 'nullable|array',
            'feedback_followup_date' => 'nullable|array|size:'.count($request->feedback_information ?? []),
            'feedback_followup_date.*' => 'nullable|date',
            'presale_employee_id' => 'nullable|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $timeSheetData = $request->only([
                'employee_id', 'project_id', 'unit_id', 'date', 
                'full_name', 'mobile_no', 'email_id', 'address',
                'recommended_by', 'refrence_data', 'other_data', 'cp_data',
                'primary_reason', 'square_feet_range', 'price_range',
                'client_status', 'executive_remark', 'presale_employee_id', 'assigned_to',
            ]);

            // Set original creator
            $timeSheetData['employee_id'] = Auth::id();
            
            // If assigned_to is empty, set it to null (not the current user)
            if (empty($timeSheetData['assigned_to'])) {
                $timeSheetData['assigned_to'] = null;
            }

            // Process feedback data
            $feedbackArray = [];
            if ($request->has('feedback_information')) {
                foreach ($request->feedback_information as $index => $feedback) {
                    if (!empty(trim($feedback))) {
                        $feedbackArray[] = [
                            'description' => trim($feedback),
                            'followup_date' => $request->feedback_followup_date[$index] ?? null,
                            'created_at' => now()->toDateTimeString(),
                            'added_by' => Auth::user()->employee->name ?? Auth::user()->name
                        ];
                    }
                }
                
                $timeSheetData['feedback_information'] = !empty($feedbackArray) ? json_encode($feedbackArray) : null;
            }

            TimeSheet::create($timeSheetData);

            return redirect()->route('timesheet.index')
                ->with('success', __('Timesheet created successfully'));

        } catch (\Exception $e) {
            \Log::error('Timesheet creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating timesheet: ' . $e->getMessage())
                ->withInput();
        }
    }


    public function edit(TimeSheet $timeSheet)
    {
        if (Auth::user()->can('Edit TimeSheet')) {
            $employees = Employee::pluck('name', 'user_id')->toArray();
            $allEmployees = Employee::pluck('name', 'id')->toArray();
            
            // Initialize projects array
            $projects = [];
            
            if (Auth::user()->type == 'employee') {
                // Get current employee ID (from employees table id column)
                $employeeId = Auth::user()->employee->id; // Assuming you have employee relationship
                
                // Get all projects and filter those assigned to this employee
                $allProjects = Project::all();
                
                foreach ($allProjects as $project) {
                    if (empty($project->assigned_data)) {
                        continue;
                    }
                    
                    // Check if this employee is assigned to the project
                    $isAssigned = false;
                    $assignedData = $project->assigned_data; // Already an array due to JSON casting
                    
                    foreach ($assignedData as $assignment) {
                        if (isset($assignment['employee_ids']) && 
                            is_array($assignment['employee_ids']) &&
                            in_array($employeeId, $assignment['employee_ids'])) {
                            $isAssigned = true;
                            break;
                        }
                    }
                    
                    if ($isAssigned) {
                        $projects[$project->id] = $project->project_name;
                    }
                }
            } else {
                // For admins/managers, get all projects
                $projects = Project::pluck('project_name', 'id');
            }
            
            // Decode the feedback information
            $feedbacks = [];
            if (!empty($timeSheet->feedback_information)) {
                $feedbacks = json_decode($timeSheet->feedback_information, true);
            }
            
            return view('timeSheet.edit', compact(
                'timeSheet',
                'employees',
                'projects',
                'allEmployees',
                'feedbacks' // Pass the decoded feedbacks to the view
            ));
        }
        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function update(Request $request, $id)
    {
        \Log::info('Update request data:', $request->all());

        if (!Auth::user()->can('Edit TimeSheet')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $timeSheet = TimeSheet::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            // 'employee_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'date' => 'required|date',
            'full_name' => 'required|string|max:255',
            'mobile_no' => 'string|max:20',
            'email_id' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'recommended_by' => 'nullable|string|in:CP,Digital,Hoarding,WayBoard,Walking,LeafLet,Expo,Refrence,Others',
            'cp_data' => 'required_if:recommended_by,CP|nullable|string|max:255',
            'refrence_data' => 'required_if:recommended_by,Refrence|nullable|string|max:255',
            'other_data' => 'required_if:recommended_by,Others|nullable|string|max:255',
            'primary_reason' => 'nullable|string|in:Investment,Construction',
            'square_feet_range' => 'nullable|string|max:50',
            'price_range' => 'nullable|string|in:10 To 15 lakh,15 To 20 lakh,20 To 30 lakh,30 To 40 lakh,40 To 50 lakh,50 Lakh Above',
            'client_status' => 'nullable|string|in:Intrested,Not-Intrested,Call-Back,Hold,Booked',
            'executive_remark' => 'nullable|string|max:1000',
            'presale_employee_id' => 'nullable|exists:employees,id',
            'feedback_information' => 'nullable|array',
            'feedback_information.*' => 'nullable|string|max:1000',
            'feedback_followup_date' => 'nullable|array',
            'feedback_followup_date.*' => 'nullable|date',
        ], [
            // 'employee_id.exists' => 'The selected employee does not exist.',
            'project_id.exists' => 'The selected project does not exist.',
            'presale_employee_id.exists' => 'The selected presale employee does not exist.',
            'required_if' => 'The :attribute field is required when :other is :value.',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->all());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed. Please check your inputs: ' . implode(' ', $validator->errors()->all()));
        }

        try {
            $timeSheetData = $request->only([
                'employee_id', 'project_id', 'unit_id', 'date', 
                'full_name', 'mobile_no', 'email_id', 'address',
                'recommended_by', 'primary_reason', 'square_feet_range',
                'price_range', 'client_status', 'executive_remark', 
                'presale_employee_id',  'assigned_to',
            ]);



            // Handle client source specific data
            $timeSheetData['cp_data'] = $request->recommended_by === 'CP' ? $request->cp_data : null;
            $timeSheetData['refrence_data'] = $request->recommended_by === 'Refrence' ? $request->refrence_data : null;
            $timeSheetData['other_data'] = $request->recommended_by === 'Others' ? $request->other_data : null;

            // Process feedback data
            $feedbackArray = [];
            // In your update method:
            if ($request->has('feedback_information')) {
                $feedbackArray = [];
                
                // Get existing feedbacks to preserve original creators
                $existingFeedbacks = [];
                if (!empty($timeSheet->feedback_information)) {
                    $existingFeedbacks = json_decode($timeSheet->feedback_information, true);
                }

                foreach ($request->feedback_information as $index => $feedback) {
                    if (!empty(trim($feedback))) {
                        // Check if this is an existing feedback being edited
                        if (isset($existingFeedbacks[$index])) {
                            // Preserve original creator info, add updated info
                            $feedbackArray[] = [
                                'description' => trim($feedback),
                                'followup_date' => $request->feedback_followup_date[$index] ?? null,
                                'created_at' => $existingFeedbacks[$index]['created_at'],
                                'added_by' => $existingFeedbacks[$index]['added_by'],
                                'updated_at' => now()->toDateTimeString(),
                                'updated_by' => Auth::user()->employee->name ?? Auth::user()->name
                            ];
                        } else {
                            // This is a new feedback
                            $feedbackArray[] = [
                                'description' => trim($feedback),
                                'followup_date' => $request->feedback_followup_date[$index] ?? null,
                                'created_at' => now()->toDateTimeString(),
                                'added_by' => Auth::user()->employee->name ?? Auth::user()->name
                            ];
                        }
                    }
                }
                
                $timeSheetData['feedback_information'] = !empty($feedbackArray) ? json_encode($feedbackArray) : null;
            }

            $timeSheet->update($timeSheetData);

            return redirect()->route('timesheet.index')
                ->with('success', __('Timesheet updated successfully'));

        } catch (\Exception $e) {
            \Log::error('Timesheet update failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Error updating timesheet: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('Delete TimeSheet')) {
            $timeSheet = TimeSheet::findOrFail($id);
            $timeSheet->delete();
            return redirect()->route('timesheet.index')->with('success', 'Timesheet deleted successfully!');
        }

        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function show(TimeSheet $timeSheet)
    {
        try {
            // Load relationships with error handling
            $timeSheet->load([
                'employee' => function($query) {
                    $query->select('id', 'name');
                },
                'project' => function($query) {
                    $query->select('id', 'project_name');
                },
                'presaleEmployee' => function($query) {
                    $query->select('id', 'name');
                }
            ]);
            
            // Decode feedback information
            $feedbacks = [];
            if (!empty($timeSheet->feedback_information)) {
                $feedbacks = json_decode($timeSheet->feedback_information, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $feedbacks = [];
                }
            }
            
            return view('timeSheet.show', compact('timeSheet', 'feedbacks'));
            
        } catch (\Exception $e) {
            \Log::error("Error showing timesheet: " . $e->getMessage());
            abort(500, 'Error loading timesheet details');
        }
    }

    public function export(Request $request)
    {
        // Check if we have filtered timesheets in session
        if (session()->has('export_timesheets')) {
            $timeSheets = session('export_timesheets');
            
            // Clear the session after use
            session()->forget('export_timesheets');
        } else {
            // Fallback - get all visible timesheets if no filters were applied
            $query = TimeSheet::with(['employee', 'project']);

            if (Auth::user()->type == 'employee') {
                $userId = Auth::id();
                $employeeId = Auth::user()->employee->id;
                
                $query->where(function($q) use ($userId, $employeeId) {
                    $q->where('employee_id', $userId)
                    ->orWhere('assigned_to', $userId)
                    ->orWhereHas('project', function($projectQuery) use ($userId) {
                        $projectQuery->whereJsonContains('site_heads', (string)$userId)
                                    ->orWhereJsonContains('site_heads', $userId);
                    })
                    ->orWhereHas('project', function($projectQuery) use ($employeeId) {
                        $projectQuery->whereJsonContains('assigned_data', 
                            ['employee_ids' => [(string)$employeeId]]
                        )->orWhereJsonContains('assigned_data', 
                            ['employee_ids' => $employeeId]
                        );
                    });
                });
            }

            $timeSheets = $query->latest()->get();
        }

        // Generate file name with timestamp
        $fileName = 'enquiries_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new TimeSheetExport($timeSheets), $fileName);
    }
        
    public function getEmployeeProjects($userId)
    {
        try {
            // Get the employee record from employees table where user_id matches
            $employee = Employee::where('user_id', $userId)->first();
            
            if (!$employee) {
                return response()->json(['projects' => []]);
            }

            // Get all projects
            $allProjects = Project::all();
            $assignedProjects = [];

            foreach ($allProjects as $project) {
                if (empty($project->assigned_data)) {
                    continue;
                }

                // Check if this employee is assigned to the project
                $isAssigned = false;
                $assignedData = $project->assigned_data;

                foreach ($assignedData as $assignment) {
                    if (isset($assignment['employee_ids']) && 
                        is_array($assignment['employee_ids']) &&
                        in_array($employee->id, $assignment['employee_ids'])) {
                        $isAssigned = true;
                        break;
                    }
                }

                if ($isAssigned) {
                    $assignedProjects[] = [
                        'id' => $project->id,
                        'project_name' => $project->project_name
                    ];
                }
            }

            return response()->json(['projects' => $assignedProjects]);
        } catch (\Exception $e) {
            \Log::error("Error getting employee projects: " . $e->getMessage());
            return response()->json(['projects' => []], 500);
        }
    }

    public function getProjectEmployees($projectId)
    {
        try {
            \Log::info("Fetching employees for project: $projectId");
            
            $project = Project::find($projectId);
            
            if (!$project) {
                \Log::error("Project not found: $projectId");
                return response()->json(['error' => 'Project not found'], 404);
            }

            \Log::debug('Project assigned_data:', [$project->assigned_data]);

            $employeeIds = [];
            
            // Safely extract employee IDs
            if ($project->assigned_data && is_array($project->assigned_data)) {
                foreach ($project->assigned_data as $assignment) {
                    if (isset($assignment['employee_ids']) && is_array($assignment['employee_ids'])) {
                        $employeeIds = array_merge($employeeIds, $assignment['employee_ids']);
                    }
                }
            }

            \Log::debug('Extracted employee IDs:', $employeeIds);

            // Convert all IDs to strings for comparison (since JSON might store them as strings)
            $employeeIds = array_map('strval', array_unique($employeeIds));

            // Get employees with their user accounts
            $employees = Employee::whereIn('id', $employeeIds)
                ->with('user:id,name')
                ->get()
                ->map(function ($employee) {
                    return [
                        'id' => $employee->user->id,
                        'name' => $employee->user->name
                    ];
                });

            \Log::debug('Found employees:', [$employees]);

            return response()->json([
                'employees' => $employees,
                'project' => $project->project_name
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error in getProjectEmployees: " . $e->getMessage());
            return response()->json([
                'error' => 'Error fetching employees',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}




