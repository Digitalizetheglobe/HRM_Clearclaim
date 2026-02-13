<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpRestrict extends Model
{
    protected $fillable = [
        'ip',
        'type',
        'created_by',
    ];
}
