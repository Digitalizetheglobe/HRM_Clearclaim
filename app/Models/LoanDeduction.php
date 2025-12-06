<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'month',
        'emi_amount',
        'is_deducted',
        'remark',
        'moved_from_id', // Add this field

    ];

    protected $dates = [
        'month'
    ];

    public function loan()
    {
        return $this->belongsTo(EmployeeLoan::class, 'loan_id');
    }

    // In LoanDeduction.php
    protected static function booted()
    {
        static::saved(function ($deduction) {
            // Prevent recursive saving
            if (!$deduction->loan->isDirty()) {
                $deduction->loan->calculateRemainingAmount()->save();
            }
        });
        
        static::deleted(function ($deduction) {
            // Prevent recursive saving
            if (!$deduction->loan->isDirty()) {
                $deduction->loan->calculateRemainingAmount()->save();
            }
        });
    }

}