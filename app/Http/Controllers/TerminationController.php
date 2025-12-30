<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Mail\TerminationSend;
use App\Models\Termination;
use App\Models\TerminationType;
use App\Models\Resignation;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TerminationController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('Manage Termination'))
        {
            if(Auth::user()->type == 'employee')
            {
                $emp          = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $terminations = Termination::where('created_by', '=', \Auth::user()->creatorId())->where('employee_id', '=', $emp->id)->get();
            }
            else
            {
                $terminations = Termination::where('created_by', '=', \Auth::user()->creatorId())->with(['employee', 'terminationType'])->get();
            }

            return view('termination.index', compact('terminations'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('Create Termination'))
        {
            // Get employees who have resignations (from resignations table)
            $resignationEmployeeIds = Resignation::where('created_by', \Auth::user()->creatorId())
                ->pluck('employee_id')
                ->unique()
                ->toArray();
            
            // Also get employees who are in offboarding step 1 (Resignation / Initiated Exit)
            $firstStage = \App\Models\OffboardingStage::where('created_by', \Auth::user()->creatorId())
                ->where('order', 1)
                ->first();
            
            $offboardingEmployeeIds = [];
            if ($firstStage) {
                $offboardingEmployeeIds = \App\Models\OffboardingProcess::where('created_by', \Auth::user()->creatorId())
                    ->where('stage', $firstStage->id)
                    ->whereNotNull('resignation_id')
                    ->pluck('employee_id')
                    ->unique()
                    ->toArray();
            }
            
            // Combine both lists (employees with resignations OR in step 1)
            $employeeIds = array_unique(array_merge($resignationEmployeeIds, $offboardingEmployeeIds));
            
            // Exclude employees who are already terminated
            $terminatedEmployeeIds = Termination::where('created_by', \Auth::user()->creatorId())
                ->pluck('employee_id')
                ->toArray();
            
            $employeeIds = array_diff($employeeIds, $terminatedEmployeeIds);
            
            // Get employees list
            if (!empty($employeeIds)) {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())
                    ->whereIn('id', $employeeIds)
                    ->get()
                    ->pluck('name', 'id');
            } else {
                $employees = collect([]);
            }
            
            $terminationtypes = TerminationType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('termination.create', compact('employees', 'terminationtypes'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Termination'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'termination_type' => 'required',
                                   'termination_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $termination                   = new Termination();
            $termination->employee_id      = $request->employee_id;
            $termination->termination_type = $request->termination_type;
            // Get notice_date from resignation if exists
            $resignation = Resignation::where('employee_id', $request->employee_id)
                ->where('created_by', \Auth::user()->creatorId())
                ->orderBy('created_at', 'desc')
                ->first();
            $termination->notice_date      = $resignation ? $resignation->notice_date : $request->termination_date;
            $termination->termination_date = $request->termination_date;
            $termination->description      = $request->description;
            $termination->created_by       = \Auth::user()->creatorId();
            $termination->save();

            // Update existing offboarding process instead of creating new one
            try {
                $offboardingProcess = \App\Models\OffboardingProcess::where('employee_id', $termination->employee_id)
                    ->where('created_by', \Auth::user()->creatorId())
                    ->whereNotNull('resignation_id')
                    ->first();
                
                if ($offboardingProcess) {
                    // Update the process with termination_id
                    $offboardingProcess->termination_id = $termination->id;
                    
                    // Move to HR Uploads/Downloads step (order 6)
                    $hrUploadsStage = \App\Models\OffboardingStage::where('created_by', \Auth::user()->creatorId())
                        ->where('order', 6)
                        ->first();
                    
                    if ($hrUploadsStage) {
                        $offboardingProcess->stage = $hrUploadsStage->id;
                        $offboardingProcess->termination_completed_by = \Auth::user()->id;
                        $offboardingProcess->termination_completed_at = now();
                    }
                    
                    $offboardingProcess->save();
                } else {
                    // If no process exists, create one (shouldn't happen, but handle it)
                    $hrUploadsStage = \App\Models\OffboardingStage::where('created_by', \Auth::user()->creatorId())
                        ->where('order', 6)
                        ->first();
                    
                    if ($hrUploadsStage) {
                        \App\Models\OffboardingProcess::create([
                            'employee_id' => $termination->employee_id,
                            'termination_id' => $termination->id,
                            'stage' => $hrUploadsStage->id,
                            'created_by' => \Auth::user()->creatorId(),
                            'termination_completed_by' => \Auth::user()->id,
                            'termination_completed_at' => now(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error updating offboarding process: ' . $e->getMessage());
            }

            $employee = Employee::find($request->employee_id);
            if ($employee && $employee->user) {
                // You might want to immediately disable the user or wait until termination date
                // Option 1: Disable immediately
                // $employee->user->is_active = 0;
                // $employee->user->save();
                
                // Option 2: Let the middleware handle it based on termination date
            }


            $setings = Utility::settings();
            if($setings['employee_termination'] == 1)
            {
                $employee           = Employee::find($termination->employee_id);

            $uArr = [
                'employee_termination_name'=>$employee->name, 
                'notice_date'=>$request->notice_date,
                'termination_date'=>$request->termination_date, 
                'termination_type'=>$request->termination_type, 
             ];
          $resp = Utility::sendEmailTemplate('employee_termination', [$employee->email], $uArr);
           return redirect()->route('termination.index')->with('success', __('Termination  successfully created.'). ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->route('termination.index')->with('success', __('Termination  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Termination $termination)
    {
        return redirect()->route('termination.index');
    }

    public function edit(Termination $termination)
    {
        if(\Auth::user()->can('Edit Termination'))
        {
            // Get employees who have resignations in offboarding step 1
            $firstStage = \App\Models\OffboardingStage::where('created_by', \Auth::user()->creatorId())
                ->where('order', 1)
                ->first();
            
            $employeeIds = [];
            if ($firstStage) {
                $processes = \App\Models\OffboardingProcess::where('created_by', \Auth::user()->creatorId())
                    ->where('stage', $firstStage->id)
                    ->whereNotNull('resignation_id')
                    ->pluck('employee_id')
                    ->toArray();
                
                $employeeIds = array_unique($processes);
            }
            
            // Include the current termination's employee
            $employeeIds[] = $termination->employee_id;
            $employeeIds = array_unique($employeeIds);
            
            // Get employees list
            if (!empty($employeeIds)) {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())
                    ->whereIn('id', $employeeIds)
                    ->get()
                    ->pluck('name', 'id');
            } else {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())
                    ->where('id', $termination->employee_id)
                    ->get()
                    ->pluck('name', 'id');
            }
            
            $terminationtypes = TerminationType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            if($termination->created_by == \Auth::user()->creatorId())
            {

                return view('termination.edit', compact('termination', 'employees', 'terminationtypes'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Termination $termination)
    {
        if(\Auth::user()->can('Edit Termination'))
        {
            if($termination->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'employee_id' => 'required',
                                       'termination_type' => 'required',
                                       'termination_date' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }


                $termination->employee_id      = $request->employee_id;
                $termination->termination_type = $request->termination_type;
                // Get notice_date from resignation if exists, otherwise keep existing
                if (!$termination->notice_date) {
                    $resignation = Resignation::where('employee_id', $termination->employee_id)
                        ->where('created_by', \Auth::user()->creatorId())
                        ->orderBy('created_at', 'desc')
                        ->first();
                    $termination->notice_date = $resignation ? $resignation->notice_date : $request->termination_date;
                }
                $termination->termination_date = $request->termination_date;
                $termination->description      = $request->description;
                $termination->save();
                
                // Update offboarding process if exists (don't create new one)
                try {
                    $offboardingProcess = \App\Models\OffboardingProcess::where('employee_id', $termination->employee_id)
                        ->where('created_by', \Auth::user()->creatorId())
                        ->whereNotNull('resignation_id')
                        ->first();
                    
                    if ($offboardingProcess) {
                        // Update the process with termination_id
                        $offboardingProcess->termination_id = $termination->id;
                        
                        // Move to HR Uploads/Downloads step (order 6) if not already there
                        $hrUploadsStage = \App\Models\OffboardingStage::where('created_by', \Auth::user()->creatorId())
                            ->where('order', 6)
                            ->first();
                        
                        if ($hrUploadsStage && $offboardingProcess->stage != $hrUploadsStage->id) {
                            $offboardingProcess->stage = $hrUploadsStage->id;
                            $offboardingProcess->termination_completed_by = \Auth::user()->id;
                            $offboardingProcess->termination_completed_at = now();
                        }
                        
                        $offboardingProcess->save();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error updating offboarding process: ' . $e->getMessage());
                }

                $employee = Employee::find($request->employee_id);
                if ($employee && $employee->user) {
                    // Same logic as above
                }

                return redirect()->route('termination.index')->with('success', __('Termination successfully updated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Termination $termination)
    {
        if(\Auth::user()->can('Delete Termination'))
        {
            if($termination->created_by == \Auth::user()->creatorId())
            {
                $termination->delete();

                return redirect()->route('termination.index')->with('success', __('Termination successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function description($id)
    {
        $termination = Termination::find($id);

        return view('termination.description', compact('termination'));
    }
}
