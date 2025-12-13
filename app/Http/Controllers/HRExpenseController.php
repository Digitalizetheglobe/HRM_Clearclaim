<?php

namespace App\Http\Controllers;

use App\Models\EmployeeExpense;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HRExpenseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->creatorId();
        
        // Debug information
        $debug = [
            'user_id' => $user->id,
            'user_type' => $user->type,
            'user_created_by' => $user->created_by,
            'company_id' => $companyId,
        ];
        
        // Get ALL expenses for debugging (any status)
        $allExpenses = EmployeeExpense::all();
        $debug['total_expenses'] = $allExpenses->count();
        $debug['all_expenses_details'] = $allExpenses->map(function($e) {
            return [
                'id' => $e->id,
                'status' => $e->status,
                'created_by' => $e->created_by,
                'employee_id' => $e->employee_id,
            ];
        })->toArray();
        
        // Get ALL pending_hr expenses
        $allPending = EmployeeExpense::where('status', 'pending_hr')->get();
        $debug['all_pending_count'] = $allPending->count();
        $debug['all_pending_details'] = $allPending->map(function($e) {
            return [
                'id' => $e->id,
                'created_by' => $e->created_by,
                'employee_id' => $e->employee_id,
                'status' => $e->status,
            ];
        })->toArray();
        
        // Get expenses filtered by company (try both methods)
        // Method 1: Filter by expense created_by
        $expenses1 = EmployeeExpense::where('status', 'pending_hr')
            ->where('created_by', $companyId)
            ->with(['employee', 'category'])
            ->orderBy('submitted_at', 'desc')
            ->get();
        
        // Method 2: Filter by employee's created_by (through relationship)
        $expenses2 = EmployeeExpense::where('status', 'pending_hr')
            ->whereHas('employee', function($query) use ($companyId) {
                $query->where('created_by', $companyId);
            })
            ->with(['employee', 'category'])
            ->orderBy('submitted_at', 'desc')
            ->get();
        
        $debug['filtered_by_expense_created_by'] = $expenses1->count();
        $debug['filtered_by_employee_created_by'] = $expenses2->count();
        
        // Use the method that returns results, or prefer method 2 (employee relationship)
        $expenses = $expenses2->count() > 0 ? $expenses2 : $expenses1;
        
        $debug['final_count'] = $expenses->count();
        
        // If still no expenses, get all pending to see what we have
        if ($expenses->count() == 0) {
            $expensesWithoutFilter = EmployeeExpense::where('status', 'pending_hr')
                ->with(['employee', 'category'])
                ->get();
            $debug['without_filter_count'] = $expensesWithoutFilter->count();
            $debug['all_pending_with_employee'] = $expensesWithoutFilter->map(function($e) {
                $emp = Employee::find($e->employee_id);
                return [
                    'expense_id' => $e->id,
                    'expense_created_by' => $e->created_by,
                    'employee_id' => $e->employee_id,
                    'employee_created_by' => $emp ? $emp->created_by : 'N/A',
                ];
            })->toArray();
        }

        // Pass debug info to view (remove in production)
        return view('expenses.hr.index', compact('expenses', 'debug'));
    }

    public function approved()
    {
        $companyId = Auth::user()->creatorId();
        
        $expenses = EmployeeExpense::where('created_by', $companyId)
            ->where(function($query) {
                $query->where('status', 'pending_finance')
                      ->orWhere('status', 'approved_hr');
            })
            ->with(['employee', 'category', 'hr'])
            ->orderBy('hr_approved_at', 'desc')
            ->get();

        return view('expenses.hr.approved', compact('expenses'));
    }

    public function rejected()
    {
        $companyId = Auth::user()->creatorId();
        
        $expenses = EmployeeExpense::where('status', 'rejected_hr')
            ->where('created_by', $companyId)
            ->where('hr_id', Auth::id())
            ->with(['employee', 'category', 'hr'])
            ->orderBy('hr_approved_at', 'desc')
            ->get();

        return view('expenses.hr.rejected', compact('expenses'));
    }

    public function approve(Request $request, $id)
    {
        $companyId = Auth::user()->creatorId();
        
        $expense = EmployeeExpense::where('created_by', $companyId)
            ->findOrFail($id);
        
        $expense->update([
            'status' => 'pending_finance',
            'hr_id' => Auth::id(),
            'hr_remark' => $request->remark,
            'hr_approved_at' => now(),
        ]);

        return redirect()->route('hr.expenses.index')->with('success', __('Expense approved by HR.'));
    }

    public function reject(Request $request, $id)
    {
        $companyId = Auth::user()->creatorId();
        
        $expense = EmployeeExpense::where('created_by', $companyId)
            ->findOrFail($id);
        
        $expense->update([
            'status' => 'rejected_hr',
            'hr_id' => Auth::id(),
            'hr_remark' => $request->remark,
            'hr_approved_at' => now(),
        ]);

        return redirect()->route('hr.expenses.index')->with('success', __('Expense rejected by HR.'));
    }

    public function show($id)
    {
        $companyId = Auth::user()->creatorId();
        
        $expense = EmployeeExpense::where('created_by', $companyId)
            ->with(['employee', 'category', 'hr'])
            ->findOrFail($id);

        return view('expenses.hr.show', compact('expense'));
    }
}
