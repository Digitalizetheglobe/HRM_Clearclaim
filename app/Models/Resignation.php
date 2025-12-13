<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\User;

class Resignation extends Model
{
    protected $fillable = [
        'employee_id',
        'notice_date', 
        'resignation_date',
        'description',
        'created_by',
        'status',
        'approved_by',
        'approved_at'
    ];

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
