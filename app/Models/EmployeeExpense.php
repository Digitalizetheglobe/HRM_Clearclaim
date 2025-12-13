<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeExpense extends Model
{
    use HasFactory;

    protected $table = 'employee_expenses';

    protected $fillable = [
        'employee_id',
        'category_id',
        'amount',
        'expense_date',
        'description',
        'receipt_file',
        'submitted_at',
        'status',
        'manager_id',
        'manager_remark',
        'manager_approved_at',
        'hr_id',
        'hr_remark',
        'hr_approved_at',
        'finance_id',
        'paid_date',
        'payment_mode',
        'payment_proof',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'paid_date' => 'date',
        'submitted_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
        'receipt_file' => 'array',
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function hr()
    {
        return $this->belongsTo(User::class, 'hr_id');
    }

    public function finance()
    {
        return $this->belongsTo(User::class, 'finance_id');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending_manager' => '<span class="badge bg-warning">Pending (Manager)</span>',
            'rejected_manager' => '<span class="badge bg-danger">Rejected (Manager)</span>',
            'pending_hr' => '<span class="badge bg-info">Pending (HR Approval)</span>',
            'rejected_hr' => '<span class="badge bg-danger">Rejected (HR)</span>',
            'approved_hr' => '<span class="badge bg-success">Approved (HR)</span>',
            'pending_finance' => '<span class="badge bg-primary">Pending (Finance)</span>',
            'paid' => '<span class="badge bg-success">Paid</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . $this->status . '</span>';
    }
}
