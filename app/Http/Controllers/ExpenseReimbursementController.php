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
use Carbon\Carbon;

class ExpenseReimbursementController extends Controller
{
    /**
     * Helper method to find HR Department
     */
    private function findHRDepartment($companyId)
    {
        return Department::where('created_by', $companyId)
            ->where(function($q) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%human resource%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%hr%'])
                  ->orWhereRaw('LOWER(name) = ?', ['human resource'])
                  ->orWhereRaw('LOWER(name) = ?', ['hr']);
            })
            ->first();
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
            return false;
        }
        
        $hrDepartment = $this->findHRDepartment($companyId);
        if (!$hrDepartment) {
            return false;
        }
        
        return $employee->department_id == $hrDepartment->id;
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
        
        // Get expenses where either:
        // 1. Expense created_by matches company ID, OR
        // 2. Employee's created_by matches company ID (through relationship)
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

        // Finance pending (HR approved, waiting for payment)
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

        $categories = ExpenseCategory::where('status', 1)
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

        EmployeeExpense::create([
            'employee_id' => $employee->id,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'receipt_file' => !empty($receiptFiles) ? $receiptFiles : null,
            'submitted_at' => now(),
            'status' => 'pending_hr',
            'created_by' => Auth::user()->creatorId(),
        ]);

        return redirect()->route('expenses.index')->with('success', __('Expense request submitted successfully.'));
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

        // Check if employee viewing their own expense
        if ($user->type == 'employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($expense->employee_id != $employee->id) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            return view('expenses.employee.show', compact('expense'));
        }

        // HR/Admin/Finance view
        return view('expenses.hr.show', compact('expense'));
    }

    /**
     * HR/Admin: Approve expense
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $companyId = $user->creatorId();
        
        // Check if user is Company/Admin
        $isAdmin = in_array($user->type, ['company', 'super admin']);
        
        // Check if user is HR department employee
        $employee = Employee::where('user_id', $user->id)->first();
        $isHREmployee = $this->isHREmployee($employee, $companyId);
        
        // Only HR employees or Admin can approve
        if (!$isAdmin && !$isHREmployee) {
            return redirect()->back()->with('error', __('Permission denied. Only Human Resource department employees or Admin can approve expenses.'));
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
            'status' => 'pending_finance',
            'hr_id' => Auth::id(),
            'hr_remark' => $request->remark,
            'hr_approved_at' => now(),
        ]);

        return redirect()->route('expenses.index')->with('success', __('Expense approved successfully.'));
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
        
        // Only Finance employees or Admin can mark as paid
        if (!$isAdmin && !$isFinanceEmployee) {
            return redirect()->back()->with('error', __('Permission denied. Only Finance department employees or Admin can process payments.'));
        }
        
        $expense = EmployeeExpense::where('status', 'pending_finance')
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

        $expense->update([
            'status' => 'paid',
            'finance_id' => Auth::id(),
            'paid_date' => $request->paid_date,
            'payment_mode' => $request->payment_mode,
            'payment_proof' => $paymentProof,
        ]);

        return redirect()->route('expenses.index')->with('success', __('Payment marked as completed.'));
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
