<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryArrear extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'pending_month',
        'payment_month',
        'amount',
        'created_by',
    ];

    protected $casts = [
        'pending_month' => 'date',
        'payment_month' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
