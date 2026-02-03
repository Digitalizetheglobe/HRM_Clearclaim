<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OffboardingStage;
use App\Models\OffboardingProcess;
use App\Models\Resignation;
use App\Models\Termination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class OffboardingController extends Controller
{
    public function index(Request $request)
    {
        // Check if user has permission (Company or HR)
        if (!in_array(Auth::user()->type, ['company']) && !Auth::user()->can('Manage Resignation')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // Get or create default stages
        $stages = $this->getOrCreateStages();

        // Get employees for filter
        $employees = Employee::where('created_by', Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');
        $employees->prepend('All', '');

        // Set up filters - make sure to include today's date and don't be too restrictive
        $filter = [];
        if (isset($request->start_date) && !empty($request->start_date)) {
            $filter['start_date'] = $request->start_date;
        } else {
            // Default to 6 months ago to catch all processes
            $filter['start_date'] = date("Y-m-d", strtotime("-6 months"));
        }

        if (isset($request->end_date) && !empty($request->end_date)) {
            $filter['end_date'] = $request->end_date;
        } else {
            // Include today and future dates - extend to next year to catch all
            $filter['end_date'] = date("Y-m-d 23:59:59", strtotime("+1 year"));
        }

        if (isset($request->employee) && !empty($request->employee)) {
            $filter['employee'] = $request->employee;
        } else {
            $filter['employee'] = '';
        }

        return view('offboarding.index', compact('stages', 'employees', 'filter'));
    }

    private function getOrCreateStages()
    {
        $stages = OffboardingStage::where('created_by', Auth::user()->creatorId())
            ->orderBy('order', 'asc')
            ->get();

        // If no stages exist, create default ones
        if ($stages->isEmpty()) {
            $defaultStages = [
                ['title' => 'Manager Approval', 'order' => 1],
                ['title' => 'Resignation / Initiated Exit', 'order' => 2],
                ['title' => 'HR Approval', 'order' => 3],
                ['title' => 'Access Removal Checklist', 'order' => 4],
                ['title' => 'Asset Collection Checklist', 'order' => 5],
                ['title' => 'Full & Final Settlement', 'order' => 6],
                ['title' => 'Relieving & Experience Letter', 'order' => 7],
                ['title' => 'HR Uploads / Downloads', 'order' => 8],
                ['title' => 'HR Records Feedback', 'order' => 9],
                ['title' => 'Offboarding Completed', 'order' => 10],
            ];

            foreach ($defaultStages as $stageData) {
                OffboardingStage::create([
                    'title' => $stageData['title'],
                    'order' => $stageData['order'],
                    'created_by' => Auth::user()->creatorId(),
                ]);
            }

            $stages = OffboardingStage::where('created_by', Auth::user()->creatorId())
                ->orderBy('order', 'asc')
                ->get();
        } else {
            // Remove old Manager Approval and HR Approval stages if they exist
            OffboardingStage::where('created_by', Auth::user()->creatorId())
                ->whereIn('title', ['Manager Approval', 'HR Approval & Notice Period'])
                ->delete();
            
            // Reorder existing stages to match new structure
            $stageTitles = [
                1 => 'Manager Approval',
                2 => 'Resignation / Initiated Exit',
                3 => 'HR Approval',
                4 => 'Access Removal Checklist',
                5 => 'Asset Collection Checklist',
                6 => 'Full & Final Settlement',
                7 => 'Relieving & Experience Letter',
                8 => 'HR Uploads / Downloads',
                9 => 'HR Records Feedback',
                10 => 'Offboarding Completed',
            ];
            
            foreach ($stageTitles as $order => $title) {
                $stage = OffboardingStage::where('created_by', Auth::user()->creatorId())
                    ->where('title', $title)
                    ->first();
                
                if ($stage) {
                    $stage->order = $order;
                    $stage->save();
                } else {
                    // Create missing stage
                    OffboardingStage::create([
                        'title' => $title,
                        'order' => $order,
                        'created_by' => Auth::user()->creatorId(),
                    ]);
                }
            }
            
            // Get updated stages
            $stages = OffboardingStage::where('created_by', Auth::user()->creatorId())
                ->orderBy('order', 'asc')
                ->get();
        }

        return $stages;
    }

    public function order(Request $request)
    {
        if (!in_array(Auth::user()->type, ['company']) && !Auth::user()->can('Manage Resignation')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $post = $request->all();
        if (isset($post['order']) && is_array($post['order'])) {
            foreach ($post['order'] as $key => $item) {
                $process = OffboardingProcess::where('id', '=', $item)->first();
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
        $process = OffboardingProcess::with(['employee', 'resignation', 'termination'])
            ->where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$process) {
            return response()->json(['error' => __('Process not found.')], 404);
        }

        // Step 1 - Manager Approval
        if ($step == 1) {
            // Continue to show the manager approval view
        }

        // Step 2 redirects to resignation page
        if ($step == 2) {
            return redirect()->route('resignation.index');
        }

        // Step 7 redirects to termination page
        if ($step == 7) {
            return redirect()->route('termination.index');
        }

        // Step 8 - check if it's an AJAX request (for modal) or direct access (for redirect)
        if ($step == 8) {
            // If it's not an AJAX request, redirect to employee page
            if (!request()->ajax() && !request()->wantsJson()) {
                if ($process->employee) {
                    return redirect()->route('employee.show', \Crypt::encrypt($process->employee->id));
                }
            }
            // If it's AJAX, continue to show the modal view (don't redirect)
        }

        $viewFile = 'offboarding.steps.step_' . $step;
        if (!view()->exists($viewFile)) {
            return response()->json(['error' => __('Step view not found.')], 404);
        }

        return view($viewFile, compact('process'));
    }

    public function updateStep(Request $request, $id, $step)
    {
        try {
            Log::info('updateStep called', [
                'id' => $id,
                'step' => $step,
                'request_data' => $request->all(),
                'user_id' => Auth::user()->id
            ]);

            $process = OffboardingProcess::findOrFail($id);
            
            // Check if user has permission
            if($process->created_by != Auth::user()->creatorId() && !Auth::user()->hasRole('company')) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }

            $stages = OffboardingStage::where('created_by', Auth::user()->creatorId())
                ->orderBy('order', 'asc')
                ->pluck('id', 'order')
                ->toArray();

            Log::info('Stages found', ['stages' => $stages]);

            switch ($step) {
            case 1: // Manager Approval
                $action = $request->input('action');
                
                if ($action === 'approve') {
                    $process->manager_approved_by = Auth::user()->id;
                    $process->manager_comment = $request->input('manager_comment');
                    $process->manager_status = 'approved';
                    $process->manager_approved_at = now();
                    
                    // Move to next stage (Resignation / Initiated Exit)
                    if (isset($stages[2])) {
                        $process->stage = $stages[2];
                    }
                } elseif ($action === 'reject') {
                    $process->manager_approved_by = Auth::user()->id;
                    $process->manager_comment = $request->input('manager_comment');
                    $process->manager_status = 'rejected';
                    $process->manager_approved_at = now();
                    
                    // Don't move to next stage, stay in Manager Approval
                    return response()->json(['success' => true, 'message' => __('Resignation rejected successfully.')]);
                } else {
                    return response()->json(['error' => __('Invalid action.')], 400);
                }
                break;

            case 2: // Resignation / Initiated Exit
                $action = $request->input('action');
                
                if ($action === 'approve') {
                    $process->hr_approved_by = Auth::user()->id;
                    $process->hr_comment = $request->input('hr_comment');
                    $process->last_working_day = $request->input('last_working_day');
                    $process->notice_period_days = $request->input('notice_period_days');
                    $process->hr_status = 'approved';
                    $process->hr_approved_at = now();
                    
                    // Move to next stage (Access Removal Checklist)
                    if (isset($stages[4])) {
                        $process->stage = $stages[4];
                    }
                } elseif ($action === 'reject') {
                    $process->hr_approved_by = Auth::user()->id;
                    $process->hr_comment = $request->input('hr_comment');
                    $process->hr_status = 'rejected';
                    $process->hr_approved_at = now();
                    
                    // Don't move to next stage, stay in Resignation / Initiated Exit
                    return response()->json(['success' => true, 'message' => __('Resignation rejected successfully.')]);
                } else {
                    return response()->json(['error' => __('Invalid action.')], 400);
                }
                break;

            case 3: // HR Approval

            case 4: // Access Removal Checklist
                $checklist = $request->checklist ?? [];
                // Convert to array format - handle both object and array formats
                $checklistArray = [];
                $defaultItems = ['hrm_login', 'biometric', 'email', 'crm', 'workdrive', 'whatsapp', 'other'];
                
                if (is_array($checklist)) {
                    foreach ($checklist as $key => $item) {
                        if (is_array($item)) {
                            // Ensure key is set
                            if (!isset($item['key'])) {
                                $item['key'] = $key;
                            }
                            $checklistArray[] = $item;
                        }
                    }
                }
                
                $process->access_removal_checklist = $checklistArray;
                
                // Check if all items are done - strict validation
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
                
                if ($allDone && isset($stages[5])) {
                    $process->stage = $stages[5]; // Move to next stage
                } else {
                    return response()->json(['error' => __('Please complete all checklist items before proceeding.')], 400);
                }
                break;

            case 5: // Asset Collection Checklist
                $checklist = $request->checklist ?? [];
                // Convert to array format - handle both object and array formats
                $checklistArray = [];
                $defaultItems = ['laptop', 'charger', 'mobile', 'mobile_charger', 'mouse', 'sim', 'id_card', 'other'];
                
                if (is_array($checklist)) {
                    foreach ($checklist as $key => $item) {
                        if (is_array($item)) {
                            // Ensure key is set
                            if (!isset($item['key'])) {
                                $item['key'] = $key;
                            }
                            $checklistArray[] = $item;
                        }
                    }
                }
                
                $process->asset_collection_checklist = $checklistArray;
                
                // Check if all items are collected - strict validation
                $allCollected = true;
                $collectedItems = [];
                
                foreach ($checklistArray as $item) {
                    if (isset($item['key']) && isset($item['collected']) && $item['collected']) {
                        $collectedItems[] = $item['key'];
                    }
                }
                
                // Check if all default items are collected
                foreach ($defaultItems as $defaultKey) {
                    if (!in_array($defaultKey, $collectedItems)) {
                        $allCollected = false;
                        break;
                    }
                }
                
                if ($allCollected && isset($stages[6])) {
                    $process->stage = $stages[6]; // Move to next stage
                } else {
                    return response()->json(['error' => __('Please mark all assets as collected before proceeding.')], 400);
                }
                break;

            case 6: // Full & Final Settlement
                // Validate required fields
                if (empty($request->salary_settlement)) {
                    return response()->json(['error' => __('Salary settlement is required.')], 400);
                }
                
                $process->settlement_details = [
                    'salary_settlement' => $request->salary_settlement,
                    'leave_balance' => $request->leave_balance ?? '',
                    'deductions' => $request->deductions ?? '',
                    'notes' => $request->notes ?? '',
                ];
                $process->settlement_status = 'completed';
                $process->settlement_completed_by = Auth::user()->id;
                $process->settlement_completed_at = now();
                
                if (isset($stages[7])) {
                    $process->stage = $stages[7]; // Move to next stage
                }
                break;

            case 7: // Relieving & Experience Letter
                // This step just redirects to termination page - no update needed
                // The termination controller will handle moving to next step
                return response()->json(['redirect' => route('termination.index')]);
                break;

            case 8: // HR Uploads / Downloads
                // Check if this is a confirmation request (document downloaded/sent)
                if ($request->has('confirmed') && $request->confirmed == 'yes') {
                    $process->document_status = 'uploaded';
                    $process->document_uploaded_by = Auth::user()->id;
                    $process->document_uploaded_at = now();
                    
                    // Update document details if provided
                    if ($request->has('document_type')) {
                        $existingDetails = is_array($process->document_details) ? $process->document_details : [];
                        $process->document_details = array_merge($existingDetails, [
                            'document_type' => $request->document_type,
                            'notes' => $request->notes ?? '',
                        ]);
                    }
                    
                    if (isset($stages[9])) {
                        $process->stage = $stages[9]; // Move to next stage
                    }
                } else {
                    // If not confirmed, redirect to employee show page
                    if ($process->employee) {
                        return response()->json(['redirect' => route('employee.show', \Crypt::encrypt($process->employee->id))]);
                    }
                }
                break;

            case 9: // HR Records Feedback
                // Check if this is a document confirmation from step 8
                if ($request->has('confirmed') && $request->confirmed == 'yes') {
                    $process->document_status = 'uploaded';
                    $process->document_uploaded_by = Auth::user()->id;
                    $process->document_uploaded_at = now();
                    
                    // Update document details if provided
                    if ($request->has('document_type')) {
                        $existingDetails = is_array($process->document_details) ? $process->document_details : [];
                        $process->document_details = array_merge($existingDetails, [
                            'document_type' => $request->document_type,
                            'notes' => $request->notes ?? '',
                        ]);
                    }
                    
                    // Move to HR Records Feedback stage (step 9)
                    if (isset($stages[9])) {
                        $process->stage = $stages[9];
                    }
                } else {
                    // Handle employee feedback
                    $process->employee_feedback = $request->feedback;
                    $process->feedback_recorded_by = Auth::user()->id;
                    $process->feedback_recorded_at = now();
                    
                    if (isset($stages[10])) {
                        $process->stage = $stages[10]; // Move to completed stage
                    }
                }
                break;
        }

        $process->save();

        return response()->json(['success' => true, 'message' => __('Step updated successfully.')]);
        
        } catch (\Exception $e) {
            Log::error('Error in updateStep', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id,
                'step' => $step
            ]);
            return response()->json(['error' => __('An error occurred: ') . $e->getMessage()], 500);
        }
    }

    public function createFromResignation($resignationId)
    {
        $resignation = Resignation::where('id', $resignationId)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$resignation) {
            return redirect()->back()->with('error', __('Resignation not found.'));
        }

        // Check if process already exists
        $existingProcess = OffboardingProcess::where('resignation_id', $resignationId)->first();
        if ($existingProcess) {
            return redirect()->route('offboarding.index')->with('info', __('Process already exists.'));
        }

        // Get first stage
        $firstStage = OffboardingStage::where('created_by', Auth::user()->creatorId())
            ->orderBy('order', 'asc')
            ->first();

        // Create offboarding process
        $process = OffboardingProcess::create([
            'employee_id' => $resignation->employee_id,
            'resignation_id' => $resignation->id,
            'stage' => $firstStage->id,
            'created_by' => Auth::user()->creatorId(),
        ]);

        return redirect()->route('offboarding.index')->with('success', __('Offboarding process created successfully.'));
    }

    public function createFromTermination($terminationId)
    {
        $termination = Termination::where('id', $terminationId)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$termination) {
            return redirect()->back()->with('error', __('Termination not found.'));
        }

        // Check if process already exists
        $existingProcess = OffboardingProcess::where('termination_id', $terminationId)->first();
        if ($existingProcess) {
            return redirect()->route('offboarding.index')->with('info', __('Process already exists.'));
        }

        // Get first stage
        $firstStage = OffboardingStage::where('created_by', Auth::user()->creatorId())
            ->orderBy('order', 'asc')
            ->first();

        // Create offboarding process
        $process = OffboardingProcess::create([
            'employee_id' => $termination->employee_id,
            'termination_id' => $termination->id,
            'stage' => $firstStage->id,
            'created_by' => Auth::user()->creatorId(),
        ]);

        return redirect()->route('offboarding.index')->with('success', __('Offboarding process created successfully.'));
    }
}

