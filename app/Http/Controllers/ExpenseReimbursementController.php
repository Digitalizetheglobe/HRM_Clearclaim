<?php

namespace App\Http\Controllers;

use App\Models\EmployeeExpense;
use App\Models\ExpenseCategory;
use App\Models\ExpensePolicy;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpenseReimbursementController extends Controller
{
    /**
     * Helper method to find HR Department
     */
    private function findHRDepartment($companyId)
    {
        // More flexible matching for HR department names
        $hrDepartment = Department::where('created_by', $companyId)
            ->where(function($q) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%human resource%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%humanresource%'])  // No space
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%hr%'])
                  ->orWhereRaw('LOWER(name) = ?', ['human resource'])
                  ->orWhereRaw('LOWER(name) = ?', ['humanresource'])
                  ->orWhereRaw('LOWER(name) = ?', ['hr'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%human resources%'])  // Plural
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%humanresources%']);  // Plural no space
            })
            ->first();
        
        // Log all departments for this company to help debug
        if (!$hrDepartment) {
            $allDepartments = Department::where('created_by', $companyId)->get(['id', 'name']);
            Log::warning('HR Department not found. Available departments:', [
                'company_id' => $companyId,
                'departments' => $allDepartments->toArray(),
            ]);
        } else {
            Log::debug('HR Department found', [
                'company_id' => $companyId,
                'hr_department_id' => $hrDepartment->id,
                'hr_department_name' => $hrDepartment->name,
            ]);
        }
        
        return $hrDepartment;
    }
    
    /**
     * Helper method to find Finance Department
     */
    private function findFinanceDepartment($companyId)
    {
        return Department::where('created_by', $companyId)
            ->where(function($q) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%finance%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%finanace%'])  // Handle misspelling
                  ->orWhereRaw('LOWER(name) = ?', ['finance'])
                  ->orWhereRaw('LOWER(name) = ?', ['finanace']); // Handle misspelling
            })
            ->first();
    }
    
    /**
     * Helper method to check if employee belongs to HR department
     */
    private function isHREmployee($employee, $companyId)
    {
        if (!$employee) {
            Log::debug('isHREmployee: No employee record found', ['company_id' => $companyId]);
            return false;
        }
        
        $hrDepartment = $this->findHRDepartment($companyId);
        if (!$hrDepartment) {
            Log::debug('isHREmployee: HR Department not found', [
                'company_id' => $companyId,
                'employee_id' => $employee->id,
                'employee_department_id' => $employee->department_id,
            ]);
            return false;
        }
        
        $isMatch = $employee->department_id == $hrDepartment->id;
        
        Log::debug('isHREmployee: Department check', [
            'employee_id' => $employee->id,
            'employee_department_id' => $employee->department_id,
            'hr_department_id' => $hrDepartment->id,
            'hr_department_name' => $hrDepartment->name,
            'is_match' => $isMatch,
        ]);
        
        return $isMatch;
    }
    
    /**
     * Helper method to check if employee belongs to Finance department
     */
    private function isFinanceEmployee($employee, $companyId)
    {
        if (!$employee) {
            return false;
        }
        
        $financeDepartment = $this->findFinanceDepartment($companyId);
        if (!$financeDepartment) {
            return false;
        }
        
        return $employee->department_id == $financeDepartment->id;
    }
    /**
     * Employee: View their expenses
     */
    public function index()
    {
        $user = Auth::user();
        
        // Check if user is Company/Admin
        if (in_array($user->type, ['company', 'super admin'])) {
            return $this->hrIndex();
        }
        
        // Get employee record
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee profile not found.'));
        }

        $companyId = $user->creatorId();
        
        // Check if employee belongs to HR or Finance department
        if ($this->isHREmployee($employee, $companyId) || $this->isFinanceEmployee($employee, $companyId)) {
            return $this->hrIndex();
        }
        
        // Regular employee: Show their own expenses
        $expenses = EmployeeExpense::where('employee_id', $employee->id)
            ->with(['category', 'hr', 'finance'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalReimbursed = EmployeeExpense::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->sum('amount');

        $pendingAmount = EmployeeExpense::where('employee_id', $employee->id)
            ->whereIn('status', ['pending_hr', 'pending_finance'])
            ->sum('amount');

        return view('expenses.employee.index', compact('expenses', 'totalReimbursed', 'pendingAmount'));
    }

    /**
     * HR/Admin/Finance: View expenses management
     * 
     * WORKFLOW:
     * 1. Employee submits expense → status: 'pending_hr'
     * 2. HR department employees approve → status: 'pending_finance'
     * 3. Finance department employees process payment → status: 'paid'
     */
    public function hrIndex()
    {
        $companyId = Auth::user()->creatorId();
        $user = Auth::user();
        
        // Get current employee (if logged in as employee)
        $currentEmployee = Employee::where('user_id', $user->id)->first();
        
        // Determine if current user is HR or Finance employee
        $isHREmployee = $this->isHREmployee($currentEmployee, $companyId);
        $isFinanceEmployee = $this->isFinanceEmployee($currentEmployee, $companyId);
        
        // STEP 1: Get expenses pending HR approval (status: 'pending_hr')
        // These are shown to HR department employees and Admin
        // These expenses are submitted by employees and waiting for HR approval
        $pending = EmployeeExpense::where('status', 'pending_hr')
            ->where(function($query) use ($companyId) {
                $query->where('created_by', $companyId)
                      ->orWhereHas('employee', function($q) use ($companyId) {
                          $q->where('created_by', $companyId);
                      });
            })
            ->with(['employee', 'category'])
            ->orderBy('submitted_at', 'desc')
            ->get();

        // STEP 2: Get expenses pending Finance processing (status: 'pending_finance')
        // These are shown to Finance department employees and Admin
        // These expenses have been approved by HR and are waiting for payment processing
        $financePending = EmployeeExpense::where('status', 'pending_finance')
            ->where(function($query) use ($companyId) {
                $query->where('created_by', $companyId)
                      ->orWhereHas('employee', function($q) use ($companyId) {
                          $q->where('created_by', $companyId);
                      });
            })
            ->with(['employee', 'category', 'hr'])
            ->orderBy('hr_approved_at', 'desc')
            ->get();

        $rejected = EmployeeExpense::where('status', 'rejected_hr')
            ->where(function($query) use ($companyId) {
                $query->where('created_by', $companyId)
                      ->orWhereHas('employee', function($q) use ($companyId) {
                          $q->where('created_by', $companyId);
                      });
            })
            ->with(['employee', 'category', 'hr'])
            ->orderBy('hr_approved_at', 'desc')
            ->get();

        $paid = EmployeeExpense::where('status', 'paid')
            ->where(function($query) use ($companyId) {
                $query->where('created_by', $companyId)
                      ->orWhereHas('employee', function($q) use ($companyId) {
                          $q->where('created_by', $companyId);
                      });
            })
            ->with(['employee', 'category', 'hr', 'finance'])
            ->orderBy('paid_date', 'desc')
            ->get();

        // Statistics for Admin/HR/Finance
        $stats = [
            'total_pending' => $pending->count(),
            'total_finance_pending' => $financePending->count(),
            'total_rejected' => $rejected->count(),
            'total_paid' => $paid->count(),
            'pending_amount' => $pending->sum('amount'),
            'finance_pending_amount' => $financePending->sum('amount'),
        ];

        return view('expenses.hr.index', compact('pending', 'rejected', 'financePending', 'paid', 'stats', 'user', 'isHREmployee', 'isFinanceEmployee'));
    }

    /**
     * Employee: Create expense form
     */
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

    /**
     * Employee: Store expense
     */
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
            'receipt_file.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
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
            
            if (!is_array($files)) {
                $files = [$files];
            }
            
            foreach ($files as $index => $file) {
                $filenameWithExt = $file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '_' . uniqid() . '.' . $extension;
                
                $path = Utility::upload_coustom_file($request, 'receipt_file', $fileNameToStore, $dir, $index, []);
                if ($path['flag'] == 1) {
                    $receiptFiles[] = $fileNameToStore;
                }
            }
        }

        // STEP 1: Employee submits expense → Status set to 'pending_hr'
        // This expense will now appear in HR department's "Pending Approvals" list
        EmployeeExpense::create([
            'employee_id' => $employee->id,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'receipt_file' => !empty($receiptFiles) ? $receiptFiles : null,
            'submitted_at' => now(),
            'status' => 'pending_hr', // Initial status: waiting for HR approval
            'created_by' => Auth::user()->creatorId(),
        ]);

        return redirect()->route('expenses.index')->with('success', __('Expense request submitted successfully. It will be reviewed by Human Resources department.'));
    }

    /**
     * Show expense details
     */
    public function show($id)
    {
        $user = Auth::user();
        $companyId = $user->creatorId();
        
        $expense = EmployeeExpense::where(function($query) use ($companyId) {
                $query->where('created_by', $companyId)
                      ->orWhereHas('employee', function($q) use ($companyId) {
                          $q->where('created_by', $companyId);
                      });
            })
            ->with(['employee', 'category', 'hr', 'finance'])
            ->findOrFail($id);

        // Check if user is Company/Admin
        $isAdmin = in_array($user->type, ['company', 'super admin']);
        
        // Get employee record
        $employee = Employee::where('user_id', $user->id)->first();
        
        // Check if user is HR or Finance employee
        $isHREmployee = $this->isHREmployee($employee, $companyId);
        $isFinanceEmployee = $this->isFinanceEmployee($employee, $companyId);
        
        // If user is Admin, HR, or Finance employee, they can view any expense
        if ($isAdmin || $isHREmployee || $isFinanceEmployee) {
            return view('expenses.hr.show', compact('expense'));
        }
        
        // Regular employee: can only view their own expenses
        if ($user->type == 'employee' && $employee) {
            if ($expense->employee_id != $employee->id) {
                return redirect()->back()->with('error', __('Permission denied. You can only view your own expenses.'));
            }
            return view('expenses.employee.show', compact('expense'));
        }

        // Default: Permission denied
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * HR/Admin: Approve expense
     * 
     * STEP 2: HR approves expense → Status changes from 'pending_hr' to 'pending_finance'
     * After approval, the expense moves to Finance department for payment processing
     */
    public function approve(Request $request, $id)
    {
        // Log immediately when method is called
        Log::info('=== EXPENSE APPROVE METHOD CALLED ===', [
            'expense_id' => $id,
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
        ]);
        
        $user = Auth::user();
        
        if (!$user) {
            Log::error('No authenticated user found');
            return redirect()->back()->with('error', __('You must be logged in to perform this action.'));
        }
        
        $companyId = $user->creatorId();
        
        // Check if user is Company/Admin
        $isAdmin = in_array($user->type, ['company', 'super admin']);
        
        // Check if user is HR department employee
        $employee = Employee::where('user_id', $user->id)->first();
        
        // Log all employee records for this user to help debug
        $allEmployees = Employee::where('user_id', $user->id)->get(['id', 'name', 'department_id', 'user_id']);
        Log::info('All Employee Records for User', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'employees' => $allEmployees->toArray(),
        ]);
        
        if (!$employee) {
            Log::error('No employee record found for user', [
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);
            return redirect()->back()->with('error', __('Employee profile not found. Please ensure you have an employee record.'));
        }
        
        // Log the employee's department details
        $employeeDepartment = $employee->department_id ? Department::find($employee->department_id) : null;
        Log::info('Employee Department Details', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'employee_department_id' => $employee->department_id,
            'employee_department_name' => $employeeDepartment ? $employeeDepartment->name : 'Not Found',
        ]);
        
        $isHREmployee = $this->isHREmployee($employee, $companyId);
        
        // Debug logging to help diagnose permission issues
        Log::info('Expense Approval Attempt', [
            'user_id' => $user->id,
            'user_type' => $user->type,
            'user_name' => $user->name,
            'company_id' => $companyId,
            'employee_id' => $employee ? $employee->id : null,
            'employee_department_id' => $employee ? $employee->department_id : null,
            'is_admin' => $isAdmin,
            'is_hr_employee' => $isHREmployee,
            'expense_id' => $id,
        ]);
        
        // Only HR employees or Admin can approve expenses
        if (!$isAdmin && !$isHREmployee) {
            // Additional debug info for permission denied
            $hrDepartment = $this->findHRDepartment($companyId);
            Log::warning('Expense Approval Permission Denied', [
                'user_id' => $user->id,
                'user_type' => $user->type,
                'employee_id' => $employee ? $employee->id : null,
                'employee_department_id' => $employee ? $employee->department_id : null,
                'hr_department_id' => $hrDepartment ? $hrDepartment->id : null,
                'hr_department_name' => $hrDepartment ? $hrDepartment->name : null,
                'company_id' => $companyId,
            ]);
            
            return redirect()->back()->with('error', __('Permission denied. Only Human Resource department employees or Admin can approve expenses.'));
        }
        
        // Only expenses with status 'pending_hr' can be approved
        // This ensures the workflow: Employee → HR → Finance
        $expense = EmployeeExpense::where(function($query) use ($companyId) {
                $query->where('created_by', $companyId)
                      ->orWhereHas('employee', function($q) use ($companyId) {
                          $q->where('created_by', $companyId);
                      });
            })
            ->where('status', 'pending_hr') // Only pending HR expenses can be approved
            ->findOrFail($id);
        
        // STEP 2: Change status to 'pending_finance' - expense now goes to Finance department
        $expense->update([
            'status' => 'pending_finance', // Status changed: now waiting for Finance processing
            'hr_id' => Auth::id(),
            'hr_remark' => $request->remark,
            'hr_approved_at' => now(),
        ]);

        return redirect()->route('expenses.index')->with('success', __('Expense approved successfully. It has been forwarded to Finance department for payment processing.'));
    }

    /**
     * HR/Admin: Reject expense
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $companyId = $user->creatorId();
        
        // Check if user is Company/Admin
        $isAdmin = in_array($user->type, ['company', 'super admin']);
        
        // Check if user is HR department employee
        $employee = Employee::where('user_id', $user->id)->first();
        $isHREmployee = $this->isHREmployee($employee, $companyId);
        
        // Only HR employees or Admin can reject
        if (!$isAdmin && !$isHREmployee) {
            return redirect()->back()->with('error', __('Permission denied. Only Human Resource department employees or Admin can reject expenses.'));
        }
        
        $expense = EmployeeExpense::where(function($query) use ($companyId) {
                $query->where('created_by', $companyId)
                      ->orWhereHas('employee', function($q) use ($companyId) {
                          $q->where('created_by', $companyId);
                      });
            })
            ->where('status', 'pending_hr')
            ->findOrFail($id);
        
        $expense->update([
            'status' => 'rejected_hr',
            'hr_id' => Auth::id(),
            'hr_remark' => $request->remark,
            'hr_approved_at' => now(),
        ]);

        return redirect()->route('expenses.index')->with('success', __('Expense rejected.'));
    }

    /**
     * Finance: Mark as paid
     * 
     * STEP 3: Finance processes payment → Status changes from 'pending_finance' to 'paid'
     * Only expenses that have been approved by HR (status: 'pending_finance') can be processed
     */
    public function markPaid(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'paid_date' => 'required|date',
            'payment_mode' => 'required|in:bank,upi,cash',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $user = Auth::user();
        $companyId = $user->creatorId();
        
        // Check if user is Company/Admin
        $isAdmin = in_array($user->type, ['company', 'super admin']);
        
        // Check if user is Finance department employee
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee profile not found. Please ensure you have an employee record.'));
        }
        
        $isFinanceEmployee = $this->isFinanceEmployee($employee, $companyId);
        
        // Only Finance employees or Admin can process payments
        if (!$isAdmin && !$isFinanceEmployee) {
            return redirect()->back()->with('error', __('Permission denied. Only Finance department employees or Admin can process payments.'));
        }
        
        // Only expenses with status 'pending_finance' (approved by HR) can be processed
        // This ensures the workflow: Employee → HR → Finance
        $expense = EmployeeExpense::where('status', 'pending_finance') // Only HR-approved expenses can be processed
            ->where(function($query) use ($companyId) {
                $query->where('created_by', $companyId)
                      ->orWhereHas('employee', function($q) use ($companyId) {
                          $q->where('created_by', $companyId);
                      });
            })
            ->findOrFail($id);

        $paymentProof = null;
        if ($request->hasFile('payment_proof')) {
            $dir = 'uploads/payment_proofs/';
            $file = $request->file('payment_proof');
            $filenameWithExt = $file->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            
            $path = Utility::upload_file($request, 'payment_proof', $fileNameToStore, $dir, []);
            if ($path['flag'] == 1) {
                $paymentProof = $fileNameToStore;
            }
        }

        // STEP 3: Change status to 'paid' - expense is now fully processed
        $expense->update([
            'status' => 'paid', // Final status: payment completed
            'finance_id' => Auth::id(),
            'paid_date' => $request->paid_date,
            'payment_mode' => $request->payment_mode,
            'payment_proof' => $paymentProof,
        ]);

        return redirect()->route('expenses.index')->with('success', __('Payment marked as completed. The employee has been reimbursed.'));
    }

    /**
     * Delete expense (Company/Admin only)
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $companyId = $user->creatorId();
        
        // Only Company/Admin can delete
        if (!in_array($user->type, ['company', 'super admin'])) {
            return redirect()->back()->with('error', __('Permission denied. Only Admin can delete expenses.'));
        }
        
        $expense = EmployeeExpense::where(function($query) use ($companyId) {
                $query->where('created_by', $companyId)
                      ->orWhereHas('employee', function($q) use ($companyId) {
                          $q->where('created_by', $companyId);
                      });
            })
            ->findOrFail($id);
        
        // Delete receipt files
        if ($expense->receipt_file && is_array($expense->receipt_file)) {
            $settings = Utility::getStorageSetting();
            $storageSetting = $settings['storage_setting'] ?? 'local';
            
            foreach ($expense->receipt_file as $file) {
                if ($storageSetting == 'local') {
                    $filePath = storage_path('app/public/uploads/expense_receipts/' . $file);
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                } else {
                    // For cloud storage (wasabi, s3), try to delete using Storage facade
                    try {
                        Storage::disk($storageSetting)->delete('uploads/expense_receipts/' . $file);
                    } catch (\Exception $e) {
                        // Silently fail if file doesn't exist or can't be deleted
                    }
                }
            }
        }
        
        // Delete payment proof if exists
        if ($expense->payment_proof) {
            $settings = Utility::getStorageSetting();
            $storageSetting = $settings['storage_setting'] ?? 'local';
            
            if ($storageSetting == 'local') {
                $paymentProofPath = storage_path('app/public/uploads/payment_proofs/' . $expense->payment_proof);
                if (file_exists($paymentProofPath)) {
                    @unlink($paymentProofPath);
                }
            } else {
                // For cloud storage (wasabi, s3), try to delete using Storage facade
                try {
                    Storage::disk($storageSetting)->delete('uploads/payment_proofs/' . $expense->payment_proof);
                } catch (\Exception $e) {
                    // Silently fail if file doesn't exist or can't be deleted
                }
            }
        }
        
        $expense->delete();
        
        return redirect()->route('expenses.index')->with('success', __('Expense deleted successfully.'));
    }
}
