<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LateMarkDeduction;
use App\Models\Employee;

class LateMarkDeductionController extends Controller
{
    public function create(Request $request)
    {
        $employee_id = $request->get('employee_id');
        $month = $request->get('month');
        
        $employee = Employee::findOrFail($employee_id);
        
        $deduction = LateMarkDeduction::where('employee_id', $employee_id)
            ->where('payment_month', $month)
            ->first();
            
        return view('late_mark_deduction.create', compact('employee', 'month', 'deduction'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'payment_month' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $deduction = LateMarkDeduction::where('employee_id', $request->employee_id)
            ->where('payment_month', $request->payment_month)
            ->first();

        if ($deduction) {
            $deduction->amount = $request->amount;
            $deduction->save();
        } else {
            LateMarkDeduction::create([
                'employee_id' => $request->employee_id,
                'payment_month' => $request->payment_month,
                'amount' => $request->amount,
                'created_by' => \Auth::user()->creatorId(),
            ]);
        }

        return redirect()->back()->with('success', __('Late mark deduction successfully added.'));
    }
}
