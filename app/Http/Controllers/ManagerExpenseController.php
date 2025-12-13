<?php

namespace App\Http\Controllers;

use App\Models\EmployeeExpense;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerExpenseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee profile not found.'));
        }

        // Get expenses from employees in the same department
        $expenses = EmployeeExpense::whereHas('employee', function($query) use ($employee) {
                $query->where('department_id', $employee->department_id);
            })
            ->where('status', 'pending_manager')
            ->with(['employee', 'category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('expenses.manager.index', compact('expenses'));
    }

    public function approved()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee profile not found.'));
        }

        $expenses = EmployeeExpense::whereHas('employee', function($query) use ($employee) {
                $query->where('department_id', $employee->department_id);
            })
            ->where('status', 'pending_hr')
            ->where('manager_id', $user->id)
            ->with(['employee', 'category'])
            ->orderBy('manager_approved_at', 'desc')
            ->get();

        return view('expenses.manager.approved', compact('expenses'));
    }

    public function rejected()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee profile not found.'));
        }

        $expenses = EmployeeExpense::whereHas('employee', function($query) use ($employee) {
                $query->where('department_id', $employee->department_id);
            })
            ->where('status', 'rejected_manager')
            ->where('manager_id', $user->id)
            ->with(['employee', 'category'])
            ->orderBy('manager_approved_at', 'desc')
            ->get();

        return view('expenses.manager.rejected', compact('expenses'));
    }

    public function approve(Request $request, $id)
    {
        $expense = EmployeeExpense::findOrFail($id);
        
        $expense->update([
            'status' => 'pending_hr',
            'manager_id' => Auth::id(),
            'manager_remark' => $request->remark,
            'manager_approved_at' => now(),
        ]);

        return redirect()->route('manager.expenses.index')->with('success', __('Expense approved successfully.'));
    }

    public function reject(Request $request, $id)
    {
        $expense = EmployeeExpense::findOrFail($id);
        
        $expense->update([
            'status' => 'rejected_manager',
            'manager_id' => Auth::id(),
            'manager_remark' => $request->remark,
            'manager_approved_at' => now(),
        ]);

        return redirect()->route('manager.expenses.index')->with('success', __('Expense rejected.'));
    }

    public function show($id)
    {
        $expense = EmployeeExpense::with(['employee', 'category', 'manager'])
            ->findOrFail($id);

        return view('expenses.manager.show', compact('expense'));
    }
}
