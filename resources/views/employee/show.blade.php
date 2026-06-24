@extends('layouts.admin')

@push('css')
    <style>
        /* Premium Profile Dashboard UI */
        .profile-header {
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1) 0%, rgba(var(--bs-primary-rgb), 0.05) 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(var(--bs-primary-rgb), 0.1);
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--bs-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            box-shadow: 0 10px 25px rgba(var(--bs-primary-rgb), 0.3);
            margin: 0 auto 15px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px 0 rgba(0,0,0,0.05);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px 0 rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 20px 25px;
        }
        .card-header h6 {
            margin-bottom: 0;
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.1rem;
            position: relative;
            padding-left: 15px;
        }
        .card-header h6::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 18px;
            background: var(--bs-primary);
            border-radius: 4px;
        }
        .card-body p {
            margin-bottom: 15px;
            color: #4a5568;
            font-size: 0.95rem;
        }
        .card-body strong {
            color: #2d3748;
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.8;
        }
        .info-block {
            padding: 15px;
            background: #f8fafc;
            border-radius: 10px;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }
        .info-block:hover {
            background: #ffffff;
            border-color: var(--bs-primary);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .btn-sm {
            border-radius: 6px;
            font-weight: 500;
            padding: 6px 12px;
            transition: all 0.2s ease;
        }
        .btn-sm:hover {
            transform: translateY(-2px);
        }
        .doc-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .doc-card:hover {
            border-color: var(--bs-primary);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .doc-icon {
            width: 40px;
            height: 40px;
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 15px;
        }
    </style>
@endpush

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
            <button type="button" 
                    class="btn btn-sm btn-info m-2 d-inline" 
                    data-bs-toggle="modal" 
                    data-bs-target="#appointmentDateModal">
                {{ __('Appointment Letter') }}
            </button>

            <button type="button" 
                    class="btn btn-sm btn-info m-2 d-inline" 
                    onclick="window.open('{{ route('custom.experience.certificate.download.pdf', $employee->id) }}', '_blank')">
                {{ __('Experience Certificate') }}
            </button>
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
                                        <p><strong>{{ __('Reporting Manager') }}:</strong> {{ $employee->reportingManager->name ?? 'N/A' }}</p>
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
                <div class="modal-content border-top border-success border-3">
                    <div class="modal-header">
                        <h5 class="modal-title text-success" id="approveModalLabel">
                            <i class="ti ti-circle-check me-1"></i>{{ __('Approve Employee Details') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="bg-success-subtle text-success rounded-circle d-inline-flex p-3 mb-3 mt-2">
                            <i class="ti ti-check fs-2"></i>
                        </div>
                        <h6 class="mb-2">{{ __('Are you sure you want to approve this employee\'s details?') }}</h6>
                        <p class="text-muted mb-0">{{ __('Once approved, the employee will not be able to edit their information without requesting approval again.') }}</p>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 justify-content-center">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <form action="{{ route('employee.approve', $employee->id) }}" method="POST" id="approveForm" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-success" id="approveBtn">
                                <i class="ti ti-check me-1"></i>{{ __('Confirm Approval') }}
                            </button>
                        </form>
                    </div>
                    <script>
                        document.getElementById('approveForm').addEventListener('submit', function(e) {
                            const submitBtn = document.getElementById('approveBtn');
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> {{ __("Approving...") }}';
                        });
                    </script>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-top border-danger border-3">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger" id="rejectModalLabel">
                            <i class="ti ti-alert-circle me-1"></i>{{ __('Reject Employee Details') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('employee.reject', $employee->id) }}" method="POST" class="needs-validation" novalidate id="rejectForm">
                        @csrf
                        <div class="modal-body">
                            <p class="text-muted">{{ __('Please provide a clear reason for rejecting these details so the employee can correct them.') }}</p>
                            <div class="form-group mb-0">
                                <label for="rejection_reason" class="form-label fw-bold">{{ __('Rejection Reason') }} <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" required 
                                          placeholder="{{ __('E.g., Document missing, incorrect date of birth...') }}"></textarea>
                                <div class="invalid-feedback">
                                    {{ __('A rejection reason is required.') }}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-danger" id="rejectBtn">
                                <i class="ti ti-x me-1"></i>{{ __('Confirm Rejection') }}
                            </button>
                        </div>
                    </form>
                    <script>
                        document.getElementById('rejectForm').addEventListener('submit', function(e) {
                            if (!this.checkValidity()) {
                                e.preventDefault();
                                e.stopPropagation();
                                this.classList.add('was-validated');
                                return;
                            }
                            const submitBtn = document.getElementById('rejectBtn');
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> {{ __("Rejecting...") }}';
                        });
                    </script>
                </div>
            </div>
        </div>
    @endif

    {{-- Appointment Date Modal --}}
    <div class="modal fade" id="appointmentDateModal" tabindex="-1" aria-labelledby="appointmentDateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentDateModalLabel">{{ __('Select Appointment Date') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('custom.appointment.letter.download.pdf.with.date', $employee->id) }}" method="GET" id="appointmentDateForm" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex p-3 mb-2">
                                <i class="ti ti-calendar-event fs-3"></i>
                            </div>
                            <p class="text-muted mt-2">{{ __('Please select the appointment date to generate the letter.') }}</p>
                        </div>
                        <div class="form-group position-relative">
                            <label for="appointment_date" class="form-label fw-bold">{{ __('Appointment Date') }} <span class="text-danger">*</span></label>
                            <input type="date" 
                                   id="appointment_date" 
                                   name="appointment_date" 
                                   class="form-control" 
                                   required 
                                   value="{{ $employee->company_doj ? (is_string($employee->company_doj) ? $employee->company_doj : $employee->company_doj->format('Y-m-d')) : '' }}">
                            <div class="invalid-feedback">
                                {{ __('Please provide a valid appointment date.') }}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="downloadBtn">
                            <i class="ti ti-download me-1"></i> {{ __('Download PDF') }}
                        </button>
                    </div>
                </form>
                
                <script>
                document.getElementById('appointmentDateForm').addEventListener('submit', function(e) {
                    if (!this.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.classList.add('was-validated');
                        return;
                    }

                    e.preventDefault();
                    
                    const submitBtn = document.getElementById('downloadBtn');
                    const modal = document.getElementById('appointmentDateModal');
                    
                    // Disable button to prevent multiple submissions
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> {{ __("Downloading...") }}';
                    
                    // Get form data
                    const formData = new FormData(this);
                    const url = this.action + '?appointment_date=' + formData.get('appointment_date');
                    
                    // Create temporary link for download
                    const link = document.createElement('a');
                    link.href = url;
                    link.target = '_blank';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // Close modal after a short delay
                    setTimeout(() => {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        
                        // Reset button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="ti ti-download me-1"></i> {{ __('Download PDF') }}';
                        this.classList.remove('was-validated');
                    }, 1000);
                });
                </script>
            </div>
        </div>
    </div>
@endsection