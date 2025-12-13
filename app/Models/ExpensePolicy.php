<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpensePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_name',
        'value',
        'days_limit',
        'created_by',
    ];

    public static function getPolicy($policyName, $default = null)
    {
        $policy = self::where('policy_name', $policyName)
            ->where('created_by', \Auth::user()->creatorId())
            ->first();

        return $policy ? $policy->value : $default;
    }

    public static function getDaysLimit($default = 30)
    {
        $policy = self::where('policy_name', 'submit_within_days')
            ->where('created_by', \Auth::user()->creatorId())
            ->first();

        return $policy ? $policy->days_limit : $default;
    }
}
