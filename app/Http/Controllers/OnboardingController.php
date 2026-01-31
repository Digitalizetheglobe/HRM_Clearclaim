<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OnboardingStage;
use App\Models\OnboardingProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class OnboardingController extends Controller
{
    public function index(Request $request)
    {
        // Check if user has permission (Company or HR)
        if (!in_array(Auth::user()->type, ['company']) && !Auth::user()->can('Manage Employee')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // Get or create default stages
        $stages = $this->getOrCreateStages();

        // Get employees for filter
        $employees = Employee::where('created_by', Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');
        $employees->prepend('All', '');

        // Set up filters
        $filter = [];
        if (isset($request->start_date) && !empty($request->start_date)) {
            $filter['start_date'] = $request->start_date;
        } else {
            // Default to 6 months ago
            $filter['start_date'] = date("Y-m-d", strtotime("-6 months"));
        }

        if (isset($request->end_date) && !empty($request->end_date)) {
            $filter['end_date'] = $request->end_date;
        } else {
            $filter['end_date'] = date("Y-m-d 23:59:59", strtotime("+1 year"));
        }

        if (isset($request->employee) && !empty($request->employee)) {
            $filter['employee'] = $request->employee;
        } else {
            $filter['employee'] = '';
        }

        return view('onboarding.index', compact('stages', 'employees', 'filter'));
    }

    private function getOrCreateStages()
    {
        $stages = OnboardingStage::where('created_by', Auth::user()->creatorId())
            ->orderBy('order', 'asc')
            ->get();

        // If no stages exist, create default ones
        if ($stages->isEmpty()) {
            $defaultStages = [
                ['title' => 'Employee Creation Verification', 'order' => 1],
                ['title' => 'Document Upload & Verification', 'order' => 2],
                ['title' => 'Employee Acknowledgement (Hard Copy)', 'order' => 3],
                ['title' => 'System & Access Provisioning', 'order' => 4],
                ['title' => 'Asset Issuance', 'order' => 5],
                ['title' => 'Training, Policy & Agreement Acknowledgement', 'order' => 6],
                ['title' => 'Onboarding Completed', 'order' => 7],
            ];

            foreach ($defaultStages as $stageData) {
                OnboardingStage::create([
                    'title' => $stageData['title'],
                    'order' => $stageData['order'],
                    'created_by' => Auth::user()->creatorId(),
                ]);
            }

            $stages = OnboardingStage::where('created_by', Auth::user()->creatorId())
                ->orderBy('order', 'asc')
                ->get();
        }

        return $stages;
    }

    public function order(Request $request)
    {
        if (!in_array(Auth::user()->type, ['company']) && !Auth::user()->can('Manage Employee')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $post = $request->all();
        if (isset($post['order']) && is_array($post['order'])) {
            foreach ($post['order'] as $key => $item) {
                $process = OnboardingProcess::where('id', '=', $item)->first();
                if ($process) {
                    $process->order = $key;
                    if (isset($post['stage_id'])) {
                        $process->stage = $post['stage_id'];
                    }
                    $process->save();
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function showStep($id, $step)
    {
        $process = OnboardingProcess::with(['employee'])
            ->where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$process) {
            return response()->json(['error' => __('Process not found.')], 404);
        }

        // Step 1 - check if it's an AJAX request (for modal) or direct access (for redirect)
        if ($step == 1) {
            // If it's not an AJAX request, redirect to employee show page
            if (!request()->ajax() && !request()->wantsJson()) {
                if ($process->employee) {
                    return redirect()->route('employee.show', \Crypt::encrypt($process->employee->id));
                }
            }
            // If it's AJAX, continue to show the modal view
        }

        // Step 2 - check if it's an AJAX request (for modal) or direct access (for redirect)
        if ($step == 2) {
            // If it's not an AJAX request, redirect to employee edit page
            if (!request()->ajax() && !request()->wantsJson()) {
                if ($process->employee) {
                    return redirect()->route('employee.edit', \Crypt::encrypt($process->employee->id));
                }
            }
            // If it's AJAX, continue to show the modal view
        }

        $viewFile = 'onboarding.steps.step_' . $step;
        if (!view()->exists($viewFile)) {
            return response()->json(['error' => __('Step view not found.')], 404);
        }

        return view($viewFile, compact('process'));
    }

    public function updateStep(Request $request, $id, $step)
    {
        $process = OnboardingProcess::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$process) {
            return response()->json(['error' => __('Process not found.')], 404);
        }

        // Get all stages for reference
        $stages = OnboardingStage::where('created_by', Auth::user()->creatorId())
            ->orderBy('order', 'asc')
            ->get()
            ->keyBy('order');

        switch ($step) {
            case 1: // Employee Creation Verification
                // Check if this is a confirmation request
                if ($request->has('confirmed') && $request->confirmed == 'yes') {
                    $process->employee_created_verified = true;
                    $process->employee_created_verified_by = Auth::user()->id;
                    $process->employee_created_verified_at = now();
                    
                    if (isset($stages[2])) {
                        $process->stage = $stages[2]->id; // Move to next stage
                    }
                } else {
                    // If not confirmed, redirect to employee show page
                    if ($process->employee) {
                        return response()->json(['redirect' => route('employee.show', \Crypt::encrypt($process->employee->id))]);
                    }
                }
                break;

            case 2: // Document Upload & Verification
                // Check if this is a confirmation request
                if ($request->has('confirmed') && $request->confirmed == 'yes') {
                    $process->document_upload_verified = true;
                    $process->document_upload_verified_by = Auth::user()->id;
                    $process->document_upload_verified_at = now();
                    
                    if (isset($stages[3])) {
                        $process->stage = $stages[3]->id; // Move to next stage
                    }
                } else {
                    // If not confirmed, redirect to employee edit page
                    if ($process->employee) {
                        return response()->json(['redirect' => route('employee.edit', \Crypt::encrypt($process->employee->id))]);
                    }
                }
                break;

            case 3: // Employee Acknowledgement (Hard Copy)
                if ($request->has('confirmed') && $request->confirmed == 'yes') {
                    $process->employee_acknowledgement_received = true;
                    $process->employee_acknowledgement_received_by = Auth::user()->id;
                    $process->employee_acknowledgement_received_at = now();
                    
                    if (isset($stages[4])) {
                        $process->stage = $stages[4]->id; // Move to next stage
                    }
                }
                break;

            case 4: // System & Access Provisioning
                $checklist = $request->checklist ?? [];
                // Convert to array format
                $checklistArray = [];
                $defaultItems = ['biometric', 'email', 'crm', 'whatsapp', 'internal_tools', 'other'];
                
                if (is_array($checklist)) {
                    foreach ($checklist as $key => $item) {
                        if (is_array($item)) {
                            if (!isset($item['key'])) {
                                $item['key'] = $key;
                            }
                            $checklistArray[] = $item;
                        }
                    }
                }
                
                $process->system_access_checklist = $checklistArray;
                
                // Check if all items are done
                $allDone = true;
                $checkedItems = [];
                
                foreach ($checklistArray as $item) {
                    if (isset($item['key']) && isset($item['done']) && $item['done']) {
                        $checkedItems[] = $item['key'];
                    }
                }
                
                // Check if all default items are checked
                foreach ($defaultItems as $defaultKey) {
                    if (!in_array($defaultKey, $checkedItems)) {
                        $allDone = false;
                        break;
                    }
                }
                
                if ($allDone) {
                    $process->system_access_status = 'done';
                    $process->system_access_completed_by = Auth::user()->id;
                    $process->system_access_completed_at = now();
                    
                    if (isset($stages[5])) {
                        $process->stage = $stages[5]->id; // Move to next stage
                    }
                } else {
                    $process->system_access_status = 'pending';
                    return response()->json(['error' => __('Please complete all checklist items before proceeding.')], 400);
                }
                break;

            case 5: // Asset Issuance
                $checklist = $request->checklist ?? [];
                // Convert to array format
                $checklistArray = [];
                $defaultItems = ['laptop', 'chargers', 'mobile', 'mouse', 'sim_card', 'id_card', 'other'];
                
                if (is_array($checklist)) {
                    foreach ($checklist as $key => $item) {
                        if (is_array($item)) {
                            if (!isset($item['key'])) {
                                $item['key'] = $key;
                            }
                            $checklistArray[] = $item;
                        }
                    }
                }
                
                $process->asset_issuance_checklist = $checklistArray;
                
                // Check if all items are issued
                $allIssued = true;
                $issuedItems = [];
                
                foreach ($checklistArray as $item) {
                    if (isset($item['key']) && isset($item['issued']) && $item['issued']) {
                        $issuedItems[] = $item['key'];
                    }
                }
                
                // Check if all default items are issued
                foreach ($defaultItems as $defaultKey) {
                    if (!in_array($defaultKey, $issuedItems)) {
                        $allIssued = false;
                        break;
                    }
                }
                
                if ($allIssued) {
                    $process->asset_issuance_status = 'issued';
                    $process->asset_issuance_completed_by = Auth::user()->id;
                    $process->asset_issuance_completed_at = now();
                    
                    if (isset($stages[6])) {
                        $process->stage = $stages[6]->id; // Move to next stage
                    }
                } else {
                    $process->asset_issuance_status = 'not_issued';
                    return response()->json(['error' => __('Please mark all assets as issued before proceeding.')], 400);
                }
                break;

            case 6: // Training, Policy & Agreement Acknowledgement
                if ($request->has('confirmed') && $request->confirmed == 'yes') {
                    $process->training_policy_acknowledgement = true;
                    $process->training_policy_acknowledged_by = Auth::user()->id;
                    $process->training_policy_acknowledged_at = now();
                    
                    if (isset($stages[7])) {
                        $process->stage = $stages[7]->id; // Move to completed stage
                        $process->onboarding_completed = true;
                        $process->onboarding_completed_by = Auth::user()->id;
                        $process->onboarding_completed_at = now();
                    }
                }
                break;
        }

        $process->save();

        return response()->json(['success' => true, 'message' => __('Step updated successfully.')]);
    }
}








