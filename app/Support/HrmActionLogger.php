<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\HrmActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HrmActionLogger
{
    public static function record($module, $action, $description, array $extra = [])
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return;
            }

            $employeeId = $extra['employee_id'] ?? null;
            $employeeName = $extra['employee_name'] ?? null;
            if ($employeeId && empty($employeeName)) {
                $employeeName = optional(Employee::find($employeeId))->name;
            }

            HrmActionLog::create([
                'created_by' => $user->creatorId(),
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'actor_id' => $user->id,
                'actor_name' => $user->name,
                'actor_type' => $user->type,
                'employee_id' => $employeeId,
                'employee_name' => $employeeName,
                'subject_type' => $extra['subject_type'] ?? null,
                'subject_id' => $extra['subject_id'] ?? null,
                'properties' => $extra['properties'] ?? null,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('HRM action log failed: ' . $e->getMessage());
        }
    }
}
