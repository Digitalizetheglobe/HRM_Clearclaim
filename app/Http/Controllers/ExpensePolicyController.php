<?php

namespace App\Http\Controllers;

use App\Models\ExpensePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpensePolicyController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Expense Policy')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $noBillPolicy = ExpensePolicy::where('policy_name', 'no_bill_no_reimbursement')
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        $daysPolicy = ExpensePolicy::where('policy_name', 'submit_within_days')
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        return view('expenses.policies.index', compact('noBillPolicy', 'daysPolicy'));
    }

    public function update(Request $request)
    {
        if (!Auth::user()->can('Manage Expense Policy')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'no_bill_no_reimbursement' => 'nullable|in:on,off',
            'submit_within_days' => 'required|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withInput()->with('error', $messages->first());
        }

        // Update or create no_bill_no_reimbursement policy
        ExpensePolicy::updateOrCreate(
            [
                'policy_name' => 'no_bill_no_reimbursement',
                'created_by' => Auth::user()->creatorId(),
            ],
            [
                'value' => $request->no_bill_no_reimbursement ?? 'off',
            ]
        );

        // Update or create submit_within_days policy
        ExpensePolicy::updateOrCreate(
            [
                'policy_name' => 'submit_within_days',
                'created_by' => Auth::user()->creatorId(),
            ],
            [
                'days_limit' => $request->submit_within_days,
            ]
        );

        return redirect()->route('expense-policies.index')->with('success', __('Policies updated successfully.'));
    }
}
