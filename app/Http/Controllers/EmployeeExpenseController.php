<?php

namespace App\Http\Controllers;

use App\Models\EmployeeExpense;
use App\Models\ExpenseCategory;
use App\Models\ExpensePolicy;
use App\Models\Employee;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeExpenseController extends Controller
{
    public function index()
    {
        // Redirect HR/Company users to their approval page
        if (Auth::user()->type == 'company' || Auth::user()->type == 'hr') {
            return redirect()->route('hr.expenses.index');
        }

        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee profile not found.'));
        }

        $expenses = EmployeeExpense::where('employee_id', $employee->id)
            ->with(['category', 'hr', 'finance'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals
        $totalReimbursed = EmployeeExpense::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->sum('amount');

        $pendingAmount = EmployeeExpense::where('employee_id', $employee->id)
            ->whereIn('status', ['pending_manager', 'pending_hr', 'approved_hr', 'pending_finance'])
            ->sum('amount');

        return view('expenses.employee.index', compact('expenses', 'totalReimbursed', 'pendingAmount'));
    }

    public function create()
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee profile not found.'));
        }

        $categories = ExpenseCategory::where('status', 'active')
            ->where('created_by', Auth::user()->creatorId())
            ->get();

        return view('expenses.employee.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee profile not found.'));
        }

        // Get policies
        $noBillNoReimbursement = ExpensePolicy::getPolicy('no_bill_no_reimbursement', 'off');
        $daysLimit = ExpensePolicy::getDaysLimit(30);

        // Validate expense date
        $expenseDate = Carbon::parse($request->expense_date);
        $daysDiff = Carbon::now()->diffInDays($expenseDate);
        
        if ($daysDiff > $daysLimit) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('Expense date is more than :days days old. Please submit within :days days.', ['days' => $daysLimit]));
        }

        $validator = \Validator::make($request->all(), [
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'receipt_file.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withInput()->with('error', $messages->first());
        }

        // Check if receipt is required
        if ($noBillNoReimbursement == 'on' && (!$request->hasFile('receipt_file') || count($request->file('receipt_file', [])) == 0)) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('Receipt is required. No Bill → No Reimbursement policy is enabled.'));
        }

        // Handle file uploads
        $receiptFiles = [];
        if ($request->hasFile('receipt_file')) {
            $dir = 'uploads/expense_receipts/';
            $files = $request->file('receipt_file');
            
            // Handle both single file and multiple files
            if (!is_array($files)) {
                $files = [$files];
            }
            
            foreach ($files as $index => $file) {
                $filenameWithExt = $file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '_' . uniqid() . '.' . $extension;
                
                // Use upload_coustom_file for array files
                $path = Utility::upload_coustom_file($request, 'receipt_file', $fileNameToStore, $dir, $index, []);
                if ($path['flag'] == 1) {
                    $receiptFiles[] = $fileNameToStore;
                }
            }
        }

        $expense = EmployeeExpense::create([
            'employee_id' => $employee->id,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'receipt_file' => !empty($receiptFiles) ? $receiptFiles : null,
            'submitted_at' => now(),
            'status' => 'pending_hr', // Directly goes to HR/Company User
            'created_by' => Auth::user()->creatorId(),
        ]);

        return redirect()->route('expenses.index')->with('success', __('Expense request submitted successfully.'));
    }

    public function show($id)
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee profile not found.'));
        }

        $expense = EmployeeExpense::where('id', $id)
            ->where('employee_id', $employee->id)
            ->with(['category', 'employee', 'manager', 'hr', 'finance'])
            ->firstOrFail();

        return view('expenses.employee.show', compact('expense'));
    }

}
