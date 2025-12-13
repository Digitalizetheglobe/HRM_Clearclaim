<?php

namespace App\Http\Controllers;

use App\Models\EmployeeExpense;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceExpenseController extends Controller
{
    public function index()
    {
        $expenses = EmployeeExpense::where('status', 'pending_finance')
            ->with(['employee', 'category', 'manager', 'hr'])
            ->orderBy('hr_approved_at', 'desc')
            ->get();

        return view('expenses.finance.index', compact('expenses'));
    }

    public function completed()
    {
        $expenses = EmployeeExpense::where('status', 'paid')
            ->with(['employee', 'category', 'manager', 'hr', 'finance'])
            ->orderBy('paid_date', 'desc')
            ->get();

        return view('expenses.finance.completed', compact('expenses'));
    }

    public function show($id)
    {
        $expense = EmployeeExpense::with(['employee', 'category', 'manager', 'hr', 'finance'])
            ->findOrFail($id);

        return view('expenses.finance.show', compact('expense'));
    }

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

        $expense = EmployeeExpense::findOrFail($id);

        // Handle payment proof upload
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

        return redirect()->route('finance.expenses.index')->with('success', __('Payment marked as completed.'));
    }
}
