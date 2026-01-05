<?php
// app/Console/Commands/AllocateMonthlyLeaves.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\EmployeeLeaveBalance;
use Carbon\Carbon;


class AllocateMonthlyLeaves extends Command
{
    protected $signature = 'leaves:allocate-monthly';
    protected $description = 'Allocate monthly leaves to all employees';

    public function handle()
    {
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;
        
        $employees = Employee::all();
        $leaveTypes = LeaveType::where('unlimited', 0)->get();
        
        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                // Get previous month's balance
                $previousMonth = $now->copy()->subMonth();
                $prevBalance = EmployeeLeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('year', $previousMonth->year)
                    ->where('month', $previousMonth->month)
                    ->first();
                
                $carryForward = $prevBalance ? 
                    max(0, ($prevBalance->allocated_days + $prevBalance->carry_forward_days) - $prevBalance->used_days) : 0;
                
                // Create new monthly allocation
                EmployeeLeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                    'month' => $month,
                    'allocated_days' => $leaveType->days, // Monthly allocation
                    'carry_forward_days' => $carryForward
                ]);
            }
        }
        
        $this->info('Monthly leaves allocated successfully.');
    }
}