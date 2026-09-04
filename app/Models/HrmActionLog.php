<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrmActionLog extends Model
{
    protected $fillable = [
        'created_by',
        'module',
        'action',
        'description',
        'actor_id',
        'actor_name',
        'actor_type',
        'employee_id',
        'employee_name',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public static function modules()
    {
        return [
            'bulk_attendance' => 'Bulk Attendance',
            'attendance' => 'Marked Attendance',
            'attendance_regularisation' => 'Attendance Regularisation',
            'leave' => 'Manage Leave',
            'set_salary' => 'Set Salary',
            'payslip' => 'Payslip',
            'salary_arrears' => 'Salary Arrears',
            'salary_processing' => 'Salary Processing',
            'expenses' => 'Expenses',
        ];
    }

    public function moduleLabel()
    {
        $modules = self::modules();

        return $modules[$this->module] ?? ucwords(str_replace('_', ' ', $this->module));
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
