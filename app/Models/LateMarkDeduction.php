<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateMarkDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'payment_month',
        'amount',
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
