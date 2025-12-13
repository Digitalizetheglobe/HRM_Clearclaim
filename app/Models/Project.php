<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_name', 'location', 'project_type',
        'project_startdate', 'project_enddate',
        'assigned_data', 'created_by', 'site_heads'
    ];

    protected $casts = [
        'assigned_data' => 'array', // This will automatically handle JSON decode/encode
        'site_heads' => 'array',
        // Remove the date casts for project_startdate and project_enddate
    ];

        // Add these constants for project types
    const PROJECT_TYPE_RESIDENTIAL = 1;
    const PROJECT_TYPE_COMMERCIAL = 2;
    const PROJECT_TYPE_PLOTTING = 3;

    // Add this method to get project type options
    public static function getProjectTypeOptions()
    {
        return [
            self::PROJECT_TYPE_RESIDENTIAL => 'Residential Project',
            self::PROJECT_TYPE_COMMERCIAL => 'Commercial Project',
            self::PROJECT_TYPE_PLOTTING => 'Plotting Project',
        ];
    }

    // Add this method to get the project type name
    public function getProjectTypeNameAttribute()
    {
        $options = self::getProjectTypeOptions();
        return $options[$this->project_type] ?? 'Unknown';
    }


    // Rest of your model code remains the same...
    public function getDepartmentNames()
    {
        $departmentIds = collect($this->assigned_data)->pluck('department_id')->toArray();
        return Department::whereIn('id', $departmentIds)->pluck('name', 'id');
    }

    public function getEmployeeNames()
    {
        $employeeIds = collect($this->assigned_data)
            ->pluck('employee_ids')
            ->flatten()
            ->unique()
            ->toArray();
            
        return Employee::whereIn('id', $employeeIds)->pluck('name', 'id');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'project_department')
                    ->withTimestamps();
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'project_employee')
                    ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeAssignedToEmployee($query, $employeeId)
    {
        return $query->where(function($q) use ($employeeId) {
            $q->whereJsonContains('assigned_data', [['employee_ids' => [(string)$employeeId]]])
              ->orWhereJsonContains('assigned_data', [['employee_ids' => [$employeeId]]])
              ->orWhereJsonContains('assigned_data', ['employee_ids' => (string)$employeeId])
              ->orWhereJsonContains('assigned_data', ['employee_ids' => $employeeId]);
        });
    }

    public function siteHeads()
    {
        return $this->belongsToMany(Employee::class, 'project_site_head', 'project_id', 'employee_id');
    }

    public function scopeWhereSiteHead($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->whereJsonContains('site_heads', (string)$userId)
            ->orWhereJsonContains('site_heads', $userId);
        });
    }

    
}