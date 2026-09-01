<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\Termination;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckTermination
{
    private static $checkedUserIds = [];

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->type == 'employee') {
            $userId = Auth::id();

            if (!isset(self::$checkedUserIds[$userId])) {
                self::$checkedUserIds[$userId] = false;

                $employee = Employee::where('user_id', $userId)->first();

                if ($employee) {
                    $termination = Termination::where('employee_id', $employee->id)
                        ->where('termination_date', '<=', now()->format('Y-m-d'))
                        ->exists();

                    self::$checkedUserIds[$userId] = $termination;
                }
            }

            if (self::$checkedUserIds[$userId]) {
                Auth::logout();

                return redirect()->route('login')->with('error', __('Your account has been terminated. Please contact administrator.'));
            }
        }

        return $next($request);
    }
}
