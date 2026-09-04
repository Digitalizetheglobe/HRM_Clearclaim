<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeSheet extends Model
{
    use HasFactory;
    
    protected $table = 'time_sheets';
    
    protected $fillable = [
        'employee_id',
        'presale_employee_id',
        'project_id',
        'date',
        'full_name',
        'mobile_no',
        'email_id',
        'address',
        'recommended_by',
        'cp_data',
        'refrence_data',
        'other_data',
        'primary_reason',
        'square_feet_range',
        'price_range',
        'client_status',
        'executive_remark',
        'feedback_information',
        'assigned_to',  

        
    ];
    
    protected $casts = [
        'feedback_information' => 'array',
            'site_heads' => 'array',
            'assigned_data' => 'array',

    ];

    public function getFeedbacksAttribute()
    {
        return $this->feedback_information ?? [];
    }
    
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function presaleEmployee()
    {
        return $this->belongsTo(Employee::class, 'presale_employee_id');
    }

public function isVisibleTo($userId)
{
    // Get employee ID for the user
    $employee = Employee::where('user_id', $userId)->first();
    $employeeId = $employee ? $employee->id : null;

    // 1. First check if user is admin/director - they see everything
    if (auth()->user()->hasCompanyAccess() || auth()->user()->type == 'Director') {
        return true;
    }

    // 2. Check if user is site head for this timesheet's project
    if ($this->project && $employeeId) {
        $siteHeads = $this->project->site_heads ?? [];
        
        if (is_array($siteHeads)) {
            $normalizedHeads = array_map('strval', $siteHeads);
            
            if (in_array((string)$employeeId, $normalizedHeads)) {
                return true; // Site heads see ALL timesheets for their projects
            }
        }
    }

    // 3. Check assignment visibility (only if not already handled by site head check)
    if ($this->assigned_to) {
        return $this->assigned_to == $userId;
    }

    // 4. Original creator can always see their own timesheets
    if ($this->employee_id == $userId) {
        return true;
    }

    return false;
}



    public function assignedEmployee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }


}