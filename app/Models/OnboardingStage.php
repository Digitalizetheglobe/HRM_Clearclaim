<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingStage extends Model
{
    protected $fillable = [
        'title',
        'order',
        'created_by',
    ];

    public function processes($filter = [])
    {
        $process = OnboardingProcess::where('created_by', \Auth::user()->creatorId())
            ->where('stage', $this->id);
        
        // Apply date filters only if provided
        if (isset($filter['start_date']) && !empty($filter['start_date'])) {
            $process->whereDate('created_at', '>=', $filter['start_date']);
        }
        
        if (isset($filter['end_date']) && !empty($filter['end_date'])) {
            // Parse end_date to handle both date and datetime formats
            $endDate = is_string($filter['end_date']) ? $filter['end_date'] : $filter['end_date'];
            if (strlen($endDate) <= 10) {
                // It's just a date, add time to include the whole day
                $endDate = $endDate . ' 23:59:59';
            }
            $process->where('created_at', '<=', $endDate);
        }
        
        if (isset($filter['employee']) && !empty($filter['employee'])) {
            $process->where('employee_id', $filter['employee']);
        }
        
        $process = $process->with(['employee.user'])->orderBy('order')->get();

        return $process;
    }
}





