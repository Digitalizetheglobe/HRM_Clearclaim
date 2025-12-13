<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ProjectController extends Controller
{
    
    public function index()
    {
        $projects = Project::all();
        
        // Preload all needed departments, employees, and site heads
        $departmentIds = [];
        $employeeIds = [];
        $siteHeadIds = [];
        
        foreach ($projects as $project) {
            if (is_array($project->assigned_data)) {
                foreach ($project->assigned_data as $assignment) {
                    if (!empty($assignment['department_id'])) {
                        $departmentIds[] = $assignment['department_id'];
                    }
                    if (!empty($assignment['employee_ids']) && is_array($assignment['employee_ids'])) {
                        $employeeIds = array_merge($employeeIds, $assignment['employee_ids']);
                    }
                }
            }
            
            // Get site head IDs - ensure this is properly formatted
            if ($project->site_heads && is_array($project->site_heads)) {
                $siteHeadIds = array_merge($siteHeadIds, $project->site_heads);
            }
        }
        
        // Get unique IDs
        $departmentIds = array_unique($departmentIds);
        $employeeIds = array_unique($employeeIds);
        $siteHeadIds = array_unique($siteHeadIds);
        
        // Preload data
        $departments = Department::whereIn('id', $departmentIds)->get()->keyBy('id');
        $employees = Employee::with('user')->whereIn('id', $employeeIds)->get()->keyBy('id');
        $siteHeads = Employee::with('user')->whereIn('id', $siteHeadIds)->get()->keyBy('id');
        
        return view('projects.index', compact('projects', 'departments', 'employees', 'siteHeads'));
    }

    public function create()
    {
        if (Auth::user()->can('Create Employee')) {
            $branches = Branch::all(); // Add this line
            $departments = Department::all();
            $employees = Employee::with('user')->get();
            return view('projects.create', compact('branches', 'departments', 'employees')); // Add branches
        }
        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function store(Request $request)
    {
        \Log::debug('Request data:', $request->all()); // Add this line
        try {
            // Validate with proper error messages
            $validated = $request->validate([
                'project_name' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'project_type' => 'required|integer|in:1,2,3',
                'project_startdate' => 'nullable|date',
                'project_enddate' => 'nullable|date|after_or_equal:project_startdate',
                'assigned_data' => 'required|json',
                'site_heads' => 'nullable|array',
                'site_heads.*' => 'exists:employees,id',
            ], [
                'project_name.required' => 'The project name field is required.',
                'assigned_data.required' => 'You must assign at least one employee.',
                'assigned_data.json' => 'Invalid assignment data format.',
                'project_enddate.after_or_equal' => 'The end date must be after or equal to the start date.',
            ]);

            \Log::debug('Validated data:', $validated); // Add this line

            // Ensure required fields exist before using them
            if (!isset($validated['project_name'])) {
                throw new \Exception('Project name is missing');
            }

            DB::beginTransaction();

            $assignedData = json_decode($validated['assigned_data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON in assigned_data');
            }

            $projectData = [
                'project_name' => $validated['project_name'],
                'location' => $validated['location'] ?? null,
                'project_type' => $validated['project_type'],
                'assigned_data' => $assignedData,
                'created_by' => auth()->id(),
                'site_heads' => $validated['site_heads'] ?? null,
            ];

            // Only add dates if they are provided
            if (!empty($validated['project_startdate'])) {
                $projectData['project_startdate'] = $validated['project_startdate'];
            }

            if (!empty($validated['project_enddate'])) {
                $projectData['project_enddate'] = $validated['project_enddate'];
            }

            $project = Project::create($projectData);

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => route('projects.index'),
                'message' => 'Project created successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Project creation failed: '.$e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }


    public function edit($id)
    {
        if (Auth::user()->can('Edit Employee')) {
            $project = Project::findOrFail($id);
            $branches = Branch::all(); // Add this line
            $departments = Department::all();
            $employees = Employee::with('user')->get();
            
            if (is_string($project->assigned_data)) {
                $decoded = json_decode($project->assigned_data, true);
                $project->assigned_data = (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
            } elseif (!is_array($project->assigned_data)) {
                $project->assigned_data = [];
            }
            
            return view('projects.edit', compact('project', 'branches', 'departments', 'employees')); // Add branches
        }
        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'project_name' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'project_type' => 'required|integer|in:1,2,3',
                'project_startdate' => 'nullable|date',
                'project_enddate' => 'nullable|date|after_or_equal:project_startdate',
                'assigned_data' => 'required|json',
                'site_heads' => 'nullable|array',
                'site_heads.*' => 'exists:employees,id',
            ]);

            DB::beginTransaction();

            $project = Project::findOrFail($id);
            $assignedData = json_decode($request->assigned_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON in assigned_data');
            }

            // Prepare update data
            $updateData = [
                'project_name' => $validated['project_name'],
                'location' => $validated['location'],
                'project_type' => $validated['project_type'], // Add this line
                'assigned_data' => $assignedData,
                'site_heads' => $validated['site_heads'] ?? null,
            ];

            // Only update dates if they are provided
            if (!empty($validated['project_startdate'])) {
                $updateData['project_startdate'] = $validated['project_startdate'];
            } else {
                $updateData['project_startdate'] = null;
            }

            if (!empty($validated['project_enddate'])) {
                $updateData['project_enddate'] = $validated['project_enddate'];
            } else {
                $updateData['project_enddate'] = null;
            }

            $project->update($updateData);

            DB::commit();

            return redirect()->route('projects.index');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Project update failed: '.$e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the project');
        }
    }


    public function getEmployeesByDepartment($id, Request $request)
    {
        try {
            // Remove the exclusion logic completely - this is the key change
            $employees = Employee::where('department_id', $id)
                ->orderBy('name')
                ->get(['id', 'name', 'department_id']);
                
            return response()->json($employees);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load employees',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getDepartmentsByBranch($branchId)
    {
        try {
            $departments = Department::where('branch_id', $branchId)
                ->orderBy('name')
                ->get(['id', 'name']);
                
            return response()->json($departments);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load departments',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    

 
    public function destroy(Project $project)
    {
        if (!Auth::user()->can('Delete Employee')) {
            abort(403, 'Permission Denied');
        }

        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully!');
    }

    public function getDepartmentsById(Request $request)
{
    try {
        $departmentIds = $request->input('department_ids', []);
        
        if (empty($departmentIds)) {
            return response()->json([]);
        }

        $departments = Department::whereIn('id', $departmentIds)
            ->orderBy('name')
            ->get(['id', 'name']);
            
        return response()->json($departments);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to load departments',
            'message' => $e->getMessage()
        ], 500);
    }
}
}