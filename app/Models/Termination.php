<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\TerminationType;

class Termination extends Model
{
    protected $fillable = [
        'employee_id',
        'notice_date',
        'termination_date',
        'termination_type',
        'description',
        'created_by',
    ];

    public function terminationType()
    {
        return $this->belongsTo(TerminationType::class, 'termination_type');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
