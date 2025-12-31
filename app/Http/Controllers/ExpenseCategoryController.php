<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        // Allow only company and super admin users
        if (!in_array(Auth::user()->type, ['company', 'super admin'])) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $categories = ExpenseCategory::where('created_by', Auth::user()->creatorId())
            ->orderBy('name', 'asc')
            ->get();

        return view('expenses.categories.index', compact('categories'));
    }

    public function create()
    {
        // Allow only company and super admin users
        if (!in_array(Auth::user()->type, ['company', 'super admin'])) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('expenses.categories.create');
    }

    public function store(Request $request)
    {
        // Allow only company and super admin users
        if (!in_array(Auth::user()->type, ['company', 'super admin'])) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withInput()->with('error', $messages->first());
        }

        ExpenseCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => Auth::user()->creatorId(),
        ]);

        return redirect()->route('expense-categories.index')->with('success', __('Category created successfully.'));
    }

    public function edit($id)
    {
        // Allow only company and super admin users
        if (!in_array(Auth::user()->type, ['company', 'super admin'])) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $category = ExpenseCategory::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->firstOrFail();

        return view('expenses.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        // Allow only company and super admin users
        if (!in_array(Auth::user()->type, ['company', 'super admin'])) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $category = ExpenseCategory::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->firstOrFail();

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withInput()->with('error', $messages->first());
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('expense-categories.index')->with('success', __('Category updated successfully.'));
    }

    public function destroy($id)
    {
        // Allow only company and super admin users
        if (!in_array(Auth::user()->type, ['company', 'super admin'])) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $category = ExpenseCategory::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->firstOrFail();

        // Check if category is used
        if ($category->expenses()->count() > 0) {
            return redirect()->back()->with('error', __('Cannot delete category. It is being used by expense requests.'));
        }

        $category->delete();

        return redirect()->route('expense-categories.index')->with('success', __('Category deleted successfully.'));
    }
}
