<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSystemAccess extends Model
{
    protected $table = 'employee_system_accesses';

    protected $fillable = [
        'employee_id',
        'biometric',
        'email',
        'crm',
        'whatsapp',
        'internal_tools',
        'other',
        'created_by',
    ];

    protected $casts = [
        'biometric' => 'boolean',
        'email' => 'boolean',
        'crm' => 'boolean',
        'whatsapp' => 'boolean',
        'internal_tools' => 'boolean',
        'other' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
