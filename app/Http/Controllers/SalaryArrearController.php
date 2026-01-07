<?php

namespace App\Http\Controllers;

use App\Models\SalaryArrear;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryArrearController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Set Salary') || \Auth::user()->type == 'company') {
            $arrears = SalaryArrear::where('created_by', \Auth::user()->creatorId())
                ->with('employee')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('salary-arrears.index', compact('arrears'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Manage Set Salary') || \Auth::user()->type == 'company') {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'pending_month' => 'required|date',
                'payment_month' => 'required|date|after_or_equal:pending_month',
                'amount' => 'required|numeric|min:0.01',
            ]);

            // Convert month inputs (YYYY-MM) to first day of month (YYYY-MM-01)
            $pendingMonth = $request->pending_month . '-01';
            $paymentMonth = $request->payment_month . '-01';

            SalaryArrear::create([
                'employee_id' => $request->employee_id,
                'pending_month' => $pendingMonth,
                'payment_month' => $paymentMonth,
                'amount' => $request->amount,
                'created_by' => \Auth::user()->creatorId(),
            ]);

            return redirect()->route('salary-arrears.index')
                ->with('success', __('Salary arrears added successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getDepartments()
    {
        $departments = Department::where('created_by', \Auth::user()->creatorId())
            ->get()
            ->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->name,
                ];
            });

        return response()->json($departments);
    }

    public function getEmployeesByDepartment(Request $request)
    {
        $query = Employee::where('created_by', \Auth::user()->creatorId());

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('employee_id', 'LIKE', "%{$search}%");
            });
        }

        $employees = $query->get()->map(function ($emp) {
            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'email' => $emp->email,
                'employee_id' => $emp->employee_id,
            ];
        });

        return response()->json($employees);
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('Manage Set Salary') || \Auth::user()->type == 'company') {
            $arrear = SalaryArrear::where('id', $id)
                ->where('created_by', \Auth::user()->creatorId())
                ->firstOrFail();

            $arrear->delete();

            return redirect()->route('salary-arrears.index')
                ->with('success', __('Salary arrears deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
