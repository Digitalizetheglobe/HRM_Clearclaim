<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlip extends Model
{
    protected $fillable = [
        'employee_id',
        'net_payble',
        'basic_salary',
        'salary_month',
        'status',
        'allowance',
        'commission',
        'loan',
        'saturation_deduction',
        'other_payment',
        'overtime',
        'created_by',
    ];

    
    public static function employee($id)
    {
        return Employee::find($id);
    }

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    /**
     * Payslips for people who were still employed in the salary month.
     * Someone who had already left before that month is omitted.
     * An inactive login is omitted from the current month onward.
     * Earlier months stay available as past payroll.
     */
    public function scopeForEmployedDuring($query, $salaryMonth)
    {
        try {
            $monthStart = \Carbon\Carbon::parse($salaryMonth . '-01')->startOfMonth();
        } catch (\Exception $e) {
            return $query;
        }

        $query->whereNotIn('pay_slips.employee_id', function ($subquery) use ($monthStart) {
            $subquery->select('employee_id')
                ->from('terminations')
                ->whereDate('termination_date', '<', $monthStart->toDateString());
        });

        if ($monthStart->greaterThanOrEqualTo(now()->startOfMonth())) {
            $query->whereNotIn('pay_slips.employee_id', function ($subquery) {
                $subquery->select('employees.id')
                    ->from('employees')
                    ->join('users', 'users.id', '=', 'employees.user_id')
                    ->where('users.is_active', 0);
            });
        }

        return $query;
    }
}
