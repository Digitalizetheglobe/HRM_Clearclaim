<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeLoan extends Model
{
    protected $fillable = [
        'employee_id',
        'total_amount',
        'number_of_months',
        'monthly_emi',
        'start_month',
        'remaining_amount',
        'reason',
        'created_by',
    ];

    protected $dates = [
        'start_month'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function deductions()
    {
        return $this->hasMany(LoanDeduction::class, 'loan_id'); // ✅ correct column name
    }

        protected static function booted()
        {
            static::saving(function ($loan) {
                // Use where('is_deducted', true) instead of where('is_deducted', 1)
                $deducted = $loan->deductions()->where('is_deducted', true)->sum('emi_amount');
                $loan->remaining_amount = $loan->total_amount - $deducted;
            });
            
            // Add this to update when deductions change
            static::updated(function ($loan) {
                if ($loan->isDirty('remaining_amount')) {
                    $loan->calculateRemainingAmount()->save();
                }
            });
        }

        // Make sure this method is used consistently
        public function calculateRemainingAmount()
        {
            $totalDeducted = $this->deductions()
                ->where('is_deducted', true)
                ->sum('emi_amount');
            
            $this->remaining_amount = $this->total_amount - $totalDeducted;
            return $this;
        }

        public function scopeWithDeductions($query)
        {
            return $query->with(['deductions' => function($q) {
                $q->orderBy('month', 'asc');
            }]);
        }

    public function updateRemainingAmount()
    {
        $totalDeducted = $this->deductions()
            ->where('is_deducted', true)
            ->sum('emi_amount');
            
        $this->remaining_amount = $this->total_amount - $totalDeducted;
        $this->save();
    }

    public function getTotalDeductedAttribute()
    {
        return $this->deductions()->where('is_deducted', true)->sum('emi_amount');
    }

    public function getOriginalMonthCountAttribute()
    {
        return $this->number_of_months;
    }

    public function getActualMonthCountAttribute()
    {
        return $this->number_of_months + $this->extended_months;
    }

    public function getDeductedMonthCountAttribute()
    {
        return $this->deductions()->where('is_deducted', true)->count();
    }

}