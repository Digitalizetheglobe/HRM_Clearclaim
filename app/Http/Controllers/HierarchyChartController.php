<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HierarchyChartController extends Controller
{
    /**
     * Display the hierarchy chart page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        // Get the employee's department
        $department = $employee->department;
        
        if (!$department) {
            return redirect()->back()->with('error', 'Department not found.');
        }

        // Build the hierarchy tree for the department
        $hierarchyData = $this->buildDepartmentHierarchy($department, $employee);

        return view('hierarchy.chart', compact('hierarchyData', 'department', 'employee'));
    }

    /**
     * Build the department hierarchy tree.
     *
     * @param \App\Models\Department $department
     * @param \App\Models\Employee $currentEmployee
     * @return array
     */
    private function buildDepartmentHierarchy($department, $currentEmployee)
    {
        // Get all employees in this department
        $employees = Employee::where('department_id', $department->id)
            ->with(['designation', 'reportingManager', 'department'])
            ->get();

        // Group employees by designation level
        $hierarchy = $this->groupEmployeesByLevel($employees, $currentEmployee);

        return [
            'department' => $department,
            'current_employee' => $currentEmployee,
            'hierarchy_levels' => $hierarchy,
            'executive_level' => $this->getExecutiveLevel(),
        ];
    }

    /**
     * Group employees by their designation level.
     *
     * @param \Illuminate\Support\Collection $employees
     * @param \App\Models\Employee $currentEmployee
     * @return array
     */
    private function groupEmployeesByLevel($employees, $currentEmployee)
    {
        $levels = [];
        
        // Define designation levels in order
        $designationLevels = [
            'CEO' => 1,
            'COO' => 2,
            'CFO' => 2,
            'CHRO' => 2,
            'CSO' => 2,
            'CAO' => 2,
            'VP' => 3,
            'Vice President' => 3,
            'Head' => 4,
            'Head of' => 4,
            'Sr. Manager' => 5,
            'Senior Manager' => 5,
            'Manager' => 6,
            'Assistant Manager' => 7,
            'Team Lead' => 8,
            'Team Leader' => 8,
            'Specialist' => 9,
            'Executive' => 10,
            'Intern' => 11,
        ];

        // Group employees by level
        foreach ($employees as $employee) {
            $level = $this->getEmployeeLevel($employee, $designationLevels);
            
            if (!isset($levels[$level])) {
                $levels[$level] = [];
            }
            
            $levels[$level][] = [
                'employee' => $employee,
                'is_current_user' => $employee->id === $currentEmployee->id,
                'subordinates' => $this->getDirectSubordinates($employee, $employees),
                'peers' => $this->getPeersAtSameLevel($employee, $employees, $level),
            ];
        }

        // Sort levels by key (ascending)
        ksort($levels);

        return $levels;
    }

    /**
     * Get the level of an employee based on their designation.
     *
     * @param \App\Models\Employee $employee
     * @param array $designationLevels
     * @return int
     */
    private function getEmployeeLevel($employee, $designationLevels)
    {
        $designationName = strtolower($employee->designation->name ?? '');
        
        foreach ($designationLevels as $pattern => $level) {
            if (strpos($designationName, strtolower($pattern)) !== false) {
                return $level;
            }
        }
        
        // Default level if no pattern matches
        return 10;
    }

    /**
     * Get direct subordinates of an employee.
     *
     * @param \App\Models\Employee $manager
     * @param \Illuminate\Support\Collection $allEmployees
     * @return array
     */
    private function getDirectSubordinates($manager, $allEmployees)
    {
        return $allEmployees
            ->where('reporting_manager', $manager->id)
            ->values()
            ->toArray();
    }

    /**
     * Get peers at the same level.
     *
     * @param \App\Models\Employee $employee
     * @param \Illuminate\Support\Collection $allEmployees
     * @param int $level
     * @return array
     */
    private function getPeersAtSameLevel($employee, $allEmployees, $level)
    {
        return $allEmployees
            ->where('id', '!=', $employee->id)
            ->filter(function ($emp) use ($employee, $level) {
                return $this->getEmployeeLevel($emp, $this->getDesignationLevels()) === $level &&
                       $emp->reporting_manager === $employee->reporting_manager;
            })
            ->values()
            ->toArray();
    }

    /**
     * Get the executive level (C-level executives).
     *
     * @return array
     */
    private function getExecutiveLevel()
    {
        return [
            'CEO' => Employee::whereHas('designation', function($query) {
                $query->where('name', 'like', '%CEO%');
            })->with(['designation', 'department'])->first(),
            'COO' => Employee::whereHas('designation', function($query) {
                $query->where('name', 'like', '%COO%');
            })->with(['designation', 'department'])->first(),
            'CFO' => Employee::whereHas('designation', function($query) {
                $query->where('name', 'like', '%CFO%');
            })->with(['designation', 'department'])->first(),
            'CHRO' => Employee::whereHas('designation', function($query) {
                $query->where('name', 'like', '%CHRO%');
            })->with(['designation', 'department'])->first(),
            'CSO' => Employee::whereHas('designation', function($query) {
                $query->where('name', 'like', '%CSO%');
            })->with(['designation', 'department'])->first(),
            'CAO' => Employee::whereHas('designation', function($query) {
                $query->where('name', 'like', '%CAO%');
            })->with(['designation', 'department'])->first(),
        ];
    }

    /**
     * Get designation levels mapping.
     *
     * @return array
     */
    private function getDesignationLevels()
    {
        return [
            'CEO' => 1,
            'COO' => 2,
            'CFO' => 2,
            'CHRO' => 2,
            'CSO' => 2,
            'CAO' => 2,
            'VP' => 3,
            'Vice President' => 3,
            'Head' => 4,
            'Head of' => 4,
            'Sr. Manager' => 5,
            'Senior Manager' => 5,
            'Manager' => 6,
            'Assistant Manager' => 7,
            'Team Lead' => 8,
            'Team Leader' => 8,
            'Specialist' => 9,
            'Executive' => 10,
            'Intern' => 11,
        ];
    }

    /**
     * Get executive department name.
     *
     * @param string $executive
     * @return string
     */
    public static function getExecutiveDepartment($executive)
    {
        $departments = [
            'COO' => '(Operations)',
            'CFO' => '(Finance)',
            'CHRO' => '(People)',
            'CSO' => '(Sales)',
            'CAO' => '(Analysis)'
        ];
        
        return $departments[$executive] ?? '';
    }

    /**
     * Get level name by level number.
     *
     * @param int $level
     * @return string
     */
    public static function getLevelName($level)
    {
        $levels = [
            1 => 'CEO Level',
            2 => 'C-Level Executives',
            3 => 'Vice Presidents',
            4 => 'Department Heads',
            5 => 'Senior Managers',
            6 => 'Managers',
            7 => 'Assistant Managers',
            8 => 'Team Leads',
            9 => 'Specialists',
            10 => 'Executives',
            11 => 'Interns'
        ];
        
        return $levels[$level] ?? 'Other Levels';
    }
}
