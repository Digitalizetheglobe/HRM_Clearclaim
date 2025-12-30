<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Mail\ResignationApproved;
use App\Models\Resignation;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendResignationApprovalEmail;


class ResignationController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('Manage Resignation')) {
            if(Auth::user()->type == 'employee') {
                $emp = Employee::where('user_id', \Auth::user()->id)->first();
                $resignations = Resignation::where('created_by', \Auth::user()->creatorId())
                    ->where('employee_id', $emp->id)
                    ->get();
            } else {
                $resignations = Resignation::where('created_by', \Auth::user()->creatorId())
                    ->with(['employee', 'approvedBy'])
                    ->get();
            }

            return view('resignation.index', compact('resignations'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function create()
    {
        if(\Auth::user()->can('Create Resignation'))
        {
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('resignation.create', compact('employees'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Resignation'))
        {

            $validator = \Validator::make(
                $request->all(), [

                                   'notice_date' => 'required',
                                   'resignation_date' => 'required|after_or_equal:notice_date',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $resignation = new Resignation();
            $user        = \Auth::user();
            if($user->type == 'employee')
            {
                $employee                 = Employee::where('user_id', $user->id)->first();
                $resignation->employee_id = $employee->id;
            }
            else
            {
                $resignation->employee_id = $request->employee_id;
            }
            $resignation->notice_date      = $request->notice_date;
            $resignation->resignation_date = $request->resignation_date;
            $resignation->description      = $request->description ;
            $resignation->created_by       = \Auth::user()->creatorId();

            $resignation->save();

            // Automatically create offboarding process
            try {
                // Check if process already exists
                $existingProcess = \App\Models\OffboardingProcess::where('resignation_id', $resignation->id)
                    ->where('created_by', \Auth::user()->creatorId())
                    ->first();
                
                if (!$existingProcess) {
                    $firstStage = \App\Models\OffboardingStage::where('created_by', \Auth::user()->creatorId())
                        ->orderBy('order', 'asc')
                        ->first();
                    
                    if ($firstStage) {
                        \App\Models\OffboardingProcess::create([
                            'employee_id' => $resignation->employee_id,
                            'resignation_id' => $resignation->id,
                            'stage' => $firstStage->id,
                            'created_by' => \Auth::user()->creatorId(),
                        ]);
                    } else {
                        \Log::warning('Offboarding stage not found for user: ' . \Auth::user()->creatorId());
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error creating offboarding process: ' . $e->getMessage());
                // Don't fail the resignation creation, but log the error
            }

            $setings = Utility::settings();
            if($setings['employee_resignation'] == 1)
            {
                try {
                    $employee           = Employee::find($resignation->employee_id);
                    $uArr = [
                        'assign_user'=>$employee->name,
                        'resignation_date'  =>$request->notice_date,
                        'notice_date' =>$request->resignation_date,
                    ];

                    $resp = Utility::sendEmailTemplate('employee_resignation', [$employee->email], $uArr);
                    
                    $user           = User::find($employee->created_by);
                    $uArr = [
                        'assign_user'=>$user->name,
                        'resignation_date'  =>$request->notice_date,
                        'notice_date' =>$request->resignation_date,
                    ];

                    $resp = Utility::sendEmailTemplate('employee_resignation', [$user->email], $uArr);
                } catch (\Exception $e) {
                    // Log email error but don't fail the resignation creation
                    \Log::warning('Failed to send resignation email: ' . $e->getMessage());
                }
            }

            return redirect()->route('resignation.index')->with('success', __('Resignation  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Resignation $resignation)
    {
        return redirect()->route('resignation.index');
    }

    public function edit(Resignation $resignation)
    {
        if(\Auth::user()->can('Edit Resignation'))
        {
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            if($resignation->created_by == \Auth::user()->creatorId())
            {

                return view('resignation.edit', compact('resignation', 'employees'));
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

    public function update(Request $request, Resignation $resignation)
    {
        if(\Auth::user()->can('Edit Resignation'))
        {
            if($resignation->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [

                                       'notice_date' => 'required',
                                       'resignation_date' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                if(\Auth::user()->type != 'employee')
                {
                    $resignation->employee_id = $request->employee_id;
                }


                $resignation->notice_date      = $request->notice_date;
                $resignation->resignation_date = $request->resignation_date;
                $resignation->description      = $request->description;

                $resignation->save();

                return redirect()->route('resignation.index')->with('success', __('Resignation successfully updated.'));
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

    public function destroy(Resignation $resignation)
    {
        if(\Auth::user()->can('Delete Resignation'))
        {
            if($resignation->created_by == \Auth::user()->creatorId())
            {
                $resignation->delete();

                return redirect()->route('resignation.index')->with('success', __('Resignation successfully deleted.'));
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

    public function review($id)
    {
        if(\Auth::user()->can('Manage Resignation')) {
            $resignation = Resignation::with(['employee'])->findOrFail($id);
            return view('resignation.review', compact('resignation'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function approve(Request $request, $id)
    {
        if(\Auth::user()->can('Manage Resignation')) {
            $resignation = Resignation::findOrFail($id);
            
            $validator = \Validator::make($request->all(), [
                'notice_date' => 'required',
                'resignation_date' => 'required|after_or_equal:notice_date',
            ]);

            if($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Update dates if changed
            $resignation->update([
                'notice_date' => $request->notice_date,
                'resignation_date' => $request->resignation_date,
                'status' => 'approved',
                'approved_by' => \Auth::id(),
                'approved_at' => now(),
            ]);

            // Update offboarding process to move directly to Access Removal Checklist (step 2, order 2)
            try {
                $offboardingProcess = \App\Models\OffboardingProcess::where('resignation_id', $resignation->id)
                    ->where('created_by', \Auth::user()->creatorId())
                    ->first();
                
                if ($offboardingProcess) {
                    // Get Access Removal Checklist stage (order 2)
                    $accessRemovalStage = \App\Models\OffboardingStage::where('created_by', \Auth::user()->creatorId())
                        ->where('order', 2)
                        ->first();
                    
                    if ($accessRemovalStage) {
                        $offboardingProcess->stage = $accessRemovalStage->id;
                        $offboardingProcess->save();
                    }
                } else {
                    // If process doesn't exist, create it and move to step 2
                    $accessRemovalStage = \App\Models\OffboardingStage::where('created_by', \Auth::user()->creatorId())
                        ->where('order', 2)
                        ->first();
                    
                    if ($accessRemovalStage) {
                        \App\Models\OffboardingProcess::create([
                            'employee_id' => $resignation->employee_id,
                            'resignation_id' => $resignation->id,
                            'stage' => $accessRemovalStage->id,
                            'created_by' => \Auth::user()->creatorId(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Silently fail if offboarding stages don't exist yet
            }

             // Send approval email (non-blocking - don't fail if email fails)
             try {
                 SendResignationApprovalEmail::dispatch($resignation, $resignation->employee->email)
                     ->onQueue('emails');
             } catch (\Exception $e) {
                 // Log email error but don't fail the approval
                 \Log::warning('Failed to queue resignation approval email: ' . $e->getMessage());
             }
             
             return redirect()->route('resignation.index')
                 ->with('success', __('Resignation approved successfully.'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}
