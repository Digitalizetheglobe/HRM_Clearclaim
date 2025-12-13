<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeNotification extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'message',
        'rejection_reason',
        'created_by',
        'seen',
    ];

    protected $casts = [
        'seen' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

