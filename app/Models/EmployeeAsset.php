<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAsset extends Model
{
    protected $table = 'employee_assets';

    protected $fillable = [
        'employee_id',
        'laptop',
        'chargers',
        'mobile',
        'mouse',
        'sim_card',
        'id_card',
        'other',
        'created_by',
    ];

    protected $casts = [
        'laptop' => 'boolean',
        'chargers' => 'boolean',
        'mobile' => 'boolean',
        'mouse' => 'boolean',
        'sim_card' => 'boolean',
        'id_card' => 'boolean',
        'other' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
