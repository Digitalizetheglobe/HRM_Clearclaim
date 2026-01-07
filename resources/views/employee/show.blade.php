@extends('layouts.admin')

@section('page-title')
    {{ __('Employee') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Manage Employee') }}</li>
@endsection

@section('action-button')
    <div class="float-end">
        {{-- Edit button - hidden for employees when approved, always visible for company/HR --}}
        @if(\Auth::user()->type === 'employee')
            {{-- Employee can only edit if not approved --}}
            @if($employee->approval_status !== 'approved')
                <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                    data-bs-toggle="tooltip" title="{{ __('Edit') }}" class="btn btn-sm btn-primary">
                    <i class="ti ti-pencil"></i>
                </a>
            @endif
        @else
            {{-- Company/HR can always edit --}}
            @can('edit employee')
                <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                    data-bs-toggle="tooltip" title="{{ __('Edit') }}" class="btn btn-sm btn-primary">
                    <i class="ti ti-pencil"></i>
                </a>
            @endcan
        @endif
        
        {{-- Show approval buttons for company/HR users when status is pending or null --}}
        @if(\Auth::user()->type !== 'employee' && ($employee->approval_status === 'pending' || empty($employee->approval_status)))
            {{-- Show buttons for company/HR users without permission check --}}
            @if(\Auth::user()->type === 'company' || \Auth::user()->type === 'hr' || \Auth::user()->can('approve employee'))
                <button type="button" class="btn btn-sm btn-success" 
                    data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="ti ti-check"></i> {{ __('Approve') }}
                </button>
                
                <button type="button" class="btn btn-sm btn-danger" 
                    data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="ti ti-x"></i> {{ __('Reject') }}
                </button>
            @endif
        @endif
        
        {{-- Show request approval button for employees when rejected --}}
        @if(\Auth::user()->type === 'employee' && $employee->approval_status === 'rejected')
            <form action="{{ route('employee.request-approval', $employee->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-warning">
                    <i class="ti ti-refresh"></i> {{ __('Request Approval Again') }}
                </button>
            </form>
        @endif
        
        {{-- Offer Letter and Experience Certificate dropdowns --}}
        <div class="d-inline">
            <ul class="list-unstyled mb-0 m-2 d-inline">
                <li class="dropdown dash-h-item drp-language d-inline">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0 btn btn-sm btn-info" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text hide-mob text-white"> {{ __('Appointment Letter') }}
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="{{ route('custom.appointment.letter.download.pdf', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('PDF') }}</a>

                        <a href="{{ route('custom.appointment.letter.download.doc', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('DOC') }}</a>
                    </div>
                </li>
            </ul>

            <ul class="list-unstyled mb-0 m-2 d-inline">
                <li class="dropdown dash-h-item drp-language d-inline">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0 btn btn-sm btn-info" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text hide-mob text-white"> {{ __('Experience Certificate') }}
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="{{ route('exp.download.pdf', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('PDF') }}</a>

                        <a href="{{ route('exp.download.doc', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('DOC') }}</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
    {{-- Approval Status Alert --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="alert alert-@if($employee->approval_status === 'approved')success
                                @elseif($employee->approval_status === 'rejected')danger
                                @elsewarning @endif">
                <strong>{{ __('Approval Status') }}:</strong> 
                {{ ucfirst($employee->approval_status ?? 'pending') }}
                
                @if($employee->approval_status === 'approved' && $employee->approved_at)
                    <br><small>{{ __('Approved on') }}: {{ \Auth::user()->dateFormat($employee->approved_at) }} 
                    @if($employee->approvedBy) by {{ $employee->approvedBy->name }} @endif</small>
                @endif
                
                @if($employee->approval_status === 'rejected' && $employee->rejection_reason)
                    <br><small>{{ __('Reason') }}: {{ $employee->rejection_reason }}</small>
                @endif

                @if(!$employee->approval_status || $employee->approval_status === 'pending')
                    <br><small>{{ __('Waiting for approval from HR/Company.') }}</small>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                            <div class="card-header">
                                <h6>{{ __('Personal Details') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Employee ID') }}:</strong> {{ $employeesId }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Name') }}:</strong> {{ $employee->name }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Email') }}:</strong> {{ $employee->email }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Phone') }}:</strong> {{ $employee->phone }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Office Phone 1') }}:</strong> {{ $employee->office_phone_one ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Office Phone 2') }}:</strong> {{ $employee->office_phone_two ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Emergency Number') }}:</strong> {{ $employee->emergency_number ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Date of Birth') }}:</strong> 
                                            {{ $employee->dob ? \Auth::user()->dateFormat($employee->dob) : __('Not Set') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Blood Group') }}:</strong> {{ $employee->blood_group ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Gender') }}:</strong> {{ $employee->gender }}</p>
                                    </div>
                                    <div class="col-md-6">
                                    </div>
                                    <div class="col-12">
                                        <p><strong>{{ __('Address') }}:</strong> {{ $employee->address ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="col-sm-12 col-md-6">
                   <div class="card">
                            <div class="card-header">
                                <h6>{{ __('Company Details') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Branch') }}:</strong> {{ $employee->branch->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Department') }}:</strong> {{ $employee->department->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Designation') }}:</strong> {{ $employee->designation->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Reporting Manager') }}:</strong> 
                                            @if($employee->reporting_manager)
                                                @php
                                                    $reportingManager = \App\Models\Employee::find($employee->reporting_manager);
                                                @endphp
                                                {{ $reportingManager->name ?? 'N/A' }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>{{ __('Date of Joining') }}:</strong> 
                                            {{ $employee->company_doj ? \Auth::user()->dateFormat($employee->company_doj) : __('Not Set') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="card ">
                        <div class="card-body employee-detail-body fulls-card emp-card">
                            <h5>{{ __('Document Detail') }}</h5>
                            <hr>
                            <div class="row">
                                @php
                                    $employeedoc = $employee->documents()->pluck('document_value', 'document_id');
                                @endphp
                                @if (!$documents->isEmpty())
                                    @foreach ($documents as $key => $document)
                                        <div class="col-md-6">
                                            <div class="info text-sm">
                                                <strong class="font-bold">{{ $document->name }} : </strong>
                                                <span>
                                                    @if(!empty($employeedoc[$document->id]))
                                                        <a href="{{ asset($employeedoc[$document->id]) }}" 
                                                        target="_blank" 
                                                        class="btn btn-sm btn-primary">
                                                            <i class="ti ti-download"></i> View
                                                        </a>
                                                    @else
                                                        No document uploaded
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center">
                                        No Document Type Added!
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-body employee-detail-body fulls-card emp-card">
                            <h5>{{ __('Experience Detail') }}</h5>
                            <hr>
                            <div class="row">
                                @if(!empty($experienceDetails))
                                    @foreach($experienceDetails as $exp)
                                        <div class="col-md-12 mb-3">
                                            <strong>Company Name:</strong> {{ $exp['previous_company_name'] ?? '-' }}<br>
                                            <strong>Designation:</strong> {{ $exp['previous_designation'] ?? '-' }}<br>
                                            <strong>Start Date:</strong> {{ $exp['start_date'] ?? '-' }}<br>
                                            <strong>End Date:</strong> {{ $exp['end_date'] ?? '-' }}<br>
                                            <strong>Previous Salary:</strong> {{ $exp['previous_salary'] ?? '-' }}
                                            <hr>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-md-12">
                                        <p>No experience detail available.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
           <div class="card">
                <div class="card-header">
                    <h6>{{ __('Education Details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(!empty($educationDetails))
                            @foreach($educationDetails as $edu)
                                <div class="col-md-12 mb-3">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Degree:</strong><br>
                                            {{ $edu['degree'] ?? '-' }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>College Name:</strong><br>
                                            {{ $edu['college_name'] ?? '-' }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Passing Year:</strong><br>
                                            {{ $edu['passing_year'] ?? '-' }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Grade:</strong><br>
                                            {{ $edu['grade'] ?? '-' }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Document:</strong><br>
                                            @if(isset($edu['document_path']))
                                                <a href="{{ asset($edu['document_path']) }}" 
                                                target="_blank" 
                                                class="btn btn-sm btn-primary">
                                                    <i class="ti ti-download"></i> View
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                            @endforeach
                        @else
                            <div class="col-md-12">
                                <p>No education details available.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
           <div class="card">
                <div class="card-header">
                    <h6>{{ __('Bank Account Detail') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <p><strong>{{ __('Account Holder Name') }}:</strong> {{ $employee->account_holder_name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <p><strong>{{ __('Bank Name') }}:</strong> {{ $employee->bank_name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <p><strong>{{ __('Branch Location') }}:</strong> {{ $employee->branch_location ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <p><strong>{{ __('Account Number') }}:</strong> {{ $employee->account_number ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <p><strong>{{ __('Bank Identifier Code') }}:</strong> {{ $employee->bank_identifier_code ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <p><strong>{{ __('Tax Payer Id') }}:</strong> {{ $employee->tax_payer_id ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval Modals --}}
    @if(\Auth::user()->type !== 'employee')
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">{{ __('Approve Employee Details') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('Are you sure you want to approve this employee\'s details?') }}</p>
                        <p>{{ __('Once approved, the employee will not be able to edit their information.') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <form action="{{ route('employee.approve', $employee->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">{{ __('Approve') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">{{ __('Reject Employee Details') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('employee.reject', $employee->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>{{ __('Please provide a reason for rejecting this employee\'s details:') }}</p>
                            <div class="form-group">
                                <textarea name="rejection_reason" class="form-control" rows="3" required 
                                          placeholder="{{ __('Enter rejection reason...') }}"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-danger">{{ __('Reject') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection