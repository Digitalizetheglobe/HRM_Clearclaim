{{-- Add this at the top of your edit.blade.php file --}}
@php
    // Determine edit permissions based on user role and department
    $canEditCompanyDetails = false;
    $emailReadonly = false; // Email is now editable
    
    if (\Auth::user()->type === 'company') {
        // Company role can edit everything except email
        $canEditCompanyDetails = true;
    } elseif (\Auth::user()->type === 'employee') {
        // Check if employee is from Human Resources department
        $currentUserEmployee = \Auth::user()->employee;
        if ($currentUserEmployee && $currentUserEmployee->department) {
            $hrDepartment = \App\Models\Department::where('name', 'LIKE', '%Human Resource%')
                ->orWhere('name', 'LIKE', '%HR%')
                ->where('created_by', \Auth::user()->creatorId())
                ->first();
            
            if ($hrDepartment && $currentUserEmployee->department_id == $hrDepartment->id) {
                // HR employees can edit everything except email
                $canEditCompanyDetails = true;
            } else {
                // Regular employees can only edit personal details, documents, education, bank, and experience
                $canEditCompanyDetails = false;
            }
        } else {
            $canEditCompanyDetails = false;
        }
    } else {
        // Other roles (admin, etc.) can edit everything except email
        $canEditCompanyDetails = true;
    }
    
    // Determine if form should be readonly (for approved employees)
    $readonly = false;
    if($employee->approval_status === 'approved' && \Auth::user()->type === 'employee') {
        $readonly = true;
    }
@endphp

@if($readonly)
    <div class="alert alert-warning">
        <strong>{{ __('Notice') }}:</strong> 
        {{ __('Your details have been approved and can no longer be edited.') }}
        {{ __('If you need to make changes, please contact your administrator.') }}
    </div>
    
    <div class="float-end">
        <a href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
           class="btn btn-primary">{{ __('Back to View') }}</a>
    </div>
@endif

@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Employee') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Employee') }}</li>
@endsection

@push('css')
    <style>
        .cursor-pointer {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="">
            <div class="">
                {{ Form::model($employee, ['route' => ['employee.update', $employee->id], 'method' => 'put', 'enctype' => 'multipart/form-data']) }}

                <!-- Add this error display section at the top of your form -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="row">
                    <!-- Personal Details Section -->
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Personal Detail') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('name', old('name', $employee->name), [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'placeholder' => 'Enter employee name',
                                            'readonly' => $readonly, // Add this line

                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('phone', __('Phone'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('phone', old('phone', $employee->phone), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter employee phone',
                                            'oninput' => 'validateNumbers()',
                                        ]) !!}
                                        <span id="phone-error" class="text-danger"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {!! Form::label('office_phone_one', __('Office Phone 1'), ['class' => 'form-label']) !!}
                                        {!! Form::text('office_phone_one', old('office_phone_one', $employee->office_phone_one), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter office phone 1',
                                            'oninput' => 'validateNumbers()',
                                        ]) !!}
                                        <span id="office_phone_one-error" class="text-danger"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {!! Form::label('office_phone_two', __('Office Phone 2'), ['class' => 'form-label']) !!}
                                        {!! Form::text('office_phone_two', old('office_phone_two', $employee->office_phone_two), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter office phone 2',
                                            'oninput' => 'validateNumbers()',
                                        ]) !!}
                                        <span id="office_phone_two-error" class="text-danger"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {!! Form::label('emergency_number', __('Emergency Number'), ['class' => 'form-label']) !!}
                                        {!! Form::text('emergency_number', old('emergency_number', $employee->emergency_number), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter emergency number',
                                            'oninput' => 'validateNumbers()',
                                        ]) !!}
                                        <span id="emergency_number-error" class="text-danger"></span>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}
                                            {!! Form::date('dob', null, [
                                                'class' => 'form-control',
                                                'autocomplete' => 'off',
                                                'placeholder' => 'dd-mm-yyyy'
                                            ]) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('blood_group', __('Blood-Group'), ['class' => 'form-label']) !!}<span class="text-danger pl-1"></span>
                                            {!! Form::text('blood_group', old('Blood-Group'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter employee Blood-Group',
                                            ]) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                            <div class="d-flex radio-check">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="g_male" value="Male" name="gender"
                                                        class="form-check-input" {{ $employee->gender == 'Male' ? 'checked' : '' }}>
                                                    <label class="form-check-label "
                                                        for="g_male">{{ __('Male') }}</label>
                                                </div>
                                                <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                    <input type="radio" id="g_female" value="Female" name="gender"
                                                        class="form-check-input" {{ $employee->gender == 'Female' ? 'checked' : '' }}>
                                                    <label class="form-check-label "
                                                        for="g_female">{{ __('Female') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::email('email', old('email', $employee->email), [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'placeholder' => 'Enter employee email',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('password', __('Password'), ['class' => 'form-label']) !!}
                                        {!! Form::password('password', [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter new password (leave blank to keep current)',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::textarea('address', old('address', $employee->address), [
                                        'class' => 'form-control',
                                        'rows' => 3,
                                        'placeholder' => 'Enter employee address',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Details Section -->
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Company Detail') }}</h5>
                            </div>
                            <div class="card-body employee-detail-create-body">
                                <div class="row">
                                    @csrf
                                    <div class="form-group">
                                        {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}
                                        {!! Form::text('employee_id', \Auth::user()->employeeIdFormat($employee->employee_id), ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                                    </div>

                                    <div class="form-group col-md-6">
                                        {{ Form::label('branch_id', __('Select Branch*'), ['class' => 'form-label']) }}
                                        <div class="form-icon-user">
                                            {{ Form::select('branch_id', $branches, $employee->branch_id, [
                                                'class' => 'form-control branch_id', 
                                                'id' => 'branch_id', 
                                                'required' => 'required',
                                                'disabled' => !$canEditCompanyDetails ? 'disabled' : null
                                            ]) }}
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <div class="form-icon-user" id="department_id">
                                            {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                            <select class="form-control select department_id" name="department_id"
                                                id="department_id" placeholder="Select Department" required {{ !$canEditCompanyDetails ? 'disabled' : '' }}>
                                                @foreach($departments as $id => $department)
                                                    <option value="{{ $id }}" {{ $employee->department_id == $id ? 'selected' : '' }}>{{ $department }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {{ Form::label('designation_id', __('Select Designation'), ['class' => 'form-label']) }}
                                        <div class="form-icon-user designation_div">
                                            <select class="form-control designation_id" name="designation_id" id="designation_id" required {{ !$canEditCompanyDetails ? 'disabled' : '' }}>
                                                @if($employee->designation_id)
                                                    <option value="{{ $employee->designation_id }}" selected>
                                                        {{ $designations[$employee->designation_id] ?? 'N/A' }}
                                                    </option>
                                                @else
                                                    <option value="" selected disabled>{{ __('Select Designation') }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group col-md-6">
                                        {{ Form::label('reporting_manager', __('Reporting Manager'), ['class' => 'form-label']) }}
                                        <div class="form-icon-user reporting_manager_div">
                                            <select class="form-control reporting_manager_id" name="reporting_manager" id="reporting_manager_id" placeholder="Select Reporting Manager" {{ !$canEditCompanyDetails ? 'disabled' : '' }}>
                                                <option value="">{{ __('Select Reporting Manager') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        {!! Form::label('company_doj', __('Company Date Of Joining'), ['class' => 'form-label']) !!}
                                        {!! Form::date('company_doj', null, [
                                            'class' => 'form-control',
                                            'autocomplete' => 'off',
                                            'placeholder' => 'dd-mm-yyyy',
                                            'readonly' => !$canEditCompanyDetails ? 'readonly' : null
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                   
                        <!-- Experience Section -->
                        <div class="col-md-12">
                            <div class="card md-12">
                                <div class="card-header">
                                    <h5>{{ __('Total Experience') }}</h5>
                                    <button type="button" class="btn btn-primary btn-sm add-experience-row">
                                        <i class="fa fa-plus"></i> Add Experience
                                    </button>
                                </div>
                                <div class="card-body employee-detail-create-body">
                                    <div id="experience-details-container">
                                        @if(!empty($employee->experience))
                                            @foreach($employee->experience as $key => $experience)
                                                <div class="row experience-detail-row mb-3">
                                                    <div class="form-group col-md-6">
                                                        {!! Form::label("experience[$key][previous_company_name]", __('Previous Company Name'), ['class' => 'form-label']) !!}
                                                        {!! Form::text("experience[$key][previous_company_name]", $experience['previous_company_name'] ?? null, [
                                                            'class' => 'form-control',
                                                            'placeholder' => 'Enter previous company name',
                                                        ]) !!}
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        {!! Form::label("experience[$key][previous_designation]", __('Designation'), ['class' => 'form-label']) !!}
                                                        {!! Form::text("experience[$key][previous_designation]", $experience['previous_designation'] ?? null, [
                                                            'class' => 'form-control',
                                                            'placeholder' => 'Enter designation',
                                                        ]) !!}
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        {!! Form::label("experience[$key][start_date]", __('Start Date'), ['class' => 'form-label']) !!}
                                                        {!! Form::date("experience[$key][start_date]", null, [
                                                            'class' => 'form-control',
                                                            'placeholder' => 'dd-mm-yyyy'
                                                        ]) !!}
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        {!! Form::label("experience[$key][end_date]", __('End Date'), ['class' => 'form-label']) !!}
                                                        {!! Form::date("experience[$key][end_date]", null, [
                                                            'class' => 'form-control',
                                                            'placeholder' => 'dd-mm-yyyy'
                                                        ]) !!}
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        {!! Form::label("experience[$key][previous_salary]", __('Previous Salary'), ['class' => 'form-label']) !!}
                                                        {!! Form::number("experience[$key][previous_salary]", $experience['previous_salary'] ?? null, [
                                                            'class' => 'form-control',
                                                            'placeholder' => 'Enter previous salary',
                                                        ]) !!}
                                                    </div>
                                                    <div class="form-group col-md-12 text-end">
                                                        <button type="button" class="btn btn-danger remove-experience-row">
                                                            <i class="fa fa-trash"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="row experience-detail-row mb-3">
                                                <div class="form-group col-md-6">
                                                    {!! Form::label('experience[0][previous_company_name]', __('Previous Company Name'), ['class' => 'form-label']) !!}
                                                    {!! Form::text('experience[0][previous_company_name]', null, [
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Enter previous company name',
                                                    ]) !!}
                                                </div>
                                                <div class="form-group col-md-6">
                                                    {!! Form::label('experience[0][previous_designation]', __('Designation'), ['class' => 'form-label']) !!}
                                                    {!! Form::text('experience[0][previous_designation]', null, [
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Enter designation',
                                                    ]) !!}
                                                </div>
                                                <div class="form-group col-md-6">
                                                    {!! Form::label('experience[0][start_date]', __('Start Date'), ['class' => 'form-label']) !!}
                                                    {!! Form::date('experience[0][start_date]', null, [
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Select start date',
                                                    ]) !!}
                                                </div>
                                                <div class="form-group col-md-6">
                                                    {!! Form::label('experience[0][end_date]', __('End Date'), ['class' => 'form-label']) !!}
                                                    {!! Form::date('experience[0][end_date]', null, [
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Select end date',
                                                    ]) !!}
                                                </div>
                                                <div class="form-group col-md-12">
                                                    {!! Form::label('experience[0][previous_salary]', __('Previous Salary'), ['class' => 'form-label']) !!}
                                                    {!! Form::number('experience[0][previous_salary]', null, [
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Enter previous salary',
                                                    ]) !!}
                                                </div>
                                                <div class="form-group col-md-12 text-end">
                                                    <button type="button" class="btn btn-danger remove-experience-row">
                                                        <i class="fa fa-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents and Education Section -->
                <div class="row">
                    <!-- Documents Section -->
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Document') }}</h5>
                            </div>
                            <div class="card-body employee-detail-create-body">
                                @foreach ($documents as $key => $document)
                                    <div class="row">
                                        <div class="form-group col-12 d-flex">
                                            <div class="float-left col-4">
                                                <label for="document" class="float-left pt-1 form-label">
                                                    {{ $document->name }} 
                                                    @if ($document->is_required == 1)
                                                        @php
                                                            $employeeDoc = $employee->documents()->where('document_id', $document->id)->first();
                                                        @endphp
                                                        @if(!$employeeDoc || !$employeeDoc->document_value)
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    @endif
                                                </label>
                                            </div>
                                            <div class="float-right col-8">
                                                <input type="hidden" name="emp_doc_id[{{ $document->id }}]" value="{{ $document->id }}">
                                                <div class="choose-files">
                                                    <label for="document[{{ $document->id }}]">
                                                        <div class="bg-primary document cursor-pointer">
                                                            <i class="ti ti-upload"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" 
                                                            class="form-control file @error('document') is-invalid @enderror"
                                                            @if ($document->is_required == 1 && (!$employeeDoc || !$employeeDoc->document_value)) required @endif
                                                            name="document[{{ $document->id }}]"
                                                            id="document[{{ $document->id }}]"
                                                            data-filename="{{ $document->id . '_filename' }}"
                                                            onchange="document.getElementById('{{ 'blah' . $key }}').src = window.URL.createObjectURL(this.files[0])">
                                                    </label>
                                                    @php
                                                        $employeeDoc = $employee->documents()->where('document_id', $document->id)->first();
                                                    @endphp
                                                    @if($employeeDoc && $employeeDoc->document_value)
                                                        <div class="mt-2">
                                                            <a href="{{ asset($employeeDoc->document_value) }}"
                                                            target="_blank" 
                                                            class="btn btn-sm btn-primary">
                                                                <i class="ti ti-download"></i> View Document
                                                            </a>
                                                        </div>
                                                    @else
                                                        <img id="{{ 'blah' . $key }}" src="" width="50%" />
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
              
                    <!-- Education Section -->
                    <!-- Education Section -->
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Education Details') }}</h5>
                            </div>
                            <div class="card-body employee-detail-create-body">
                                <div id="education-details-container">
                                    @if(!empty($educations))
                                        @foreach($educations as $key => $education)
                                            <div class="row education-detail-row mb-3">
                                                <div class="form-group col-md-6">
                                                    {!! Form::label("education[$key][degree]", __('Degree'), ['class' => 'form-label']) !!}
                                                    <select name="education[{{ $key }}][degree]" class="form-control degree">
                                                        <option value="10th" {{ (isset($education['degree']) && $education['degree'] == '10th') ? 'selected' : '' }}>{{ __('10th') }}</option>
                                                        <option value="12th" {{ (isset($education['degree']) && $education['degree'] == '12th') ? 'selected' : '' }}>{{ __('12th') }}</option>
                                                        <option value="Bachelor" {{ (isset($education['degree']) && $education['degree'] == 'Bachelor') ? 'selected' : '' }}>{{ __('Bachelor') }}</option>
                                                        <option value="Master" {{ (isset($education['degree']) && $education['degree'] == 'Master') ? 'selected' : '' }}>{{ __('Master') }}</option>
                                                        <option value="PhD" {{ (isset($education['degree']) && $education['degree'] == 'PhD') ? 'selected' : '' }}>{{ __('PhD') }}</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    {!! Form::label("education[$key][college_name]", __('College Name'), ['class' => 'form-label']) !!}
                                                    {!! Form::text("education[$key][college_name]", $education['college_name'] ?? null, [
                                                        'class' => 'form-control college-name',
                                                        'placeholder' => 'Enter college name',
                                                    ]) !!}
                                                </div>
                                                <div class="form-group col-md-6">
                                                    {!! Form::label("education[$key][passing_year]", __('Passing Year'), ['class' => 'form-label']) !!}
                                                    <select name="education[{{ $key }}][passing_year]" class="form-control passing-year">
                                                        <option value="" disabled selected>{{ __('Select Year') }}</option>
                                                        @for ($year = 1997; $year <= 2040; $year++)
                                                            <option value="{{ $year }}" {{ (isset($education['passing_year']) && $education['passing_year'] == $year) ? 'selected' : '' }}>{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    {!! Form::label("education[$key][grade]", __('Grade'), ['class' => 'form-label']) !!}
                                                    {!! Form::number("education[$key][grade]", $education['grade'] ?? null, [
                                                        'class' => 'form-control grade',
                                                        'placeholder' => 'Enter grade (e.g., 4.0)',
                                                        'step' => '0.1',
                                                        'min' => '0',
                                                        'max' => '10',
                                                    ]) !!}
                                                </div>
                                                @if(isset($education['document_path']))
                                                    <div class="form-group col-md-12">
                                                        {!! Form::label("education[$key][document]", __('Education Document'), ['class' => 'form-label']) !!}
                                                        <div class="choose-files">
                                                            <label for="education[{{ $key }}][document]">
                                                                <div class="bg-primary document cursor-pointer">
                                                                    <i class="ti ti-upload"></i>{{ __('Choose file here') }}
                                                                </div>
                                                                <input type="file" 
                                                                    name="education[{{ $key }}][document]" 
                                                                    id="education[{{ $key }}][document]" 
                                                                    class="form-control file education-document"
                                                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                            </label>
                                                                <div class="mt-2">
                                                                    <a href="{{ asset($education['document_path']) }}" 
                                                                    target="_blank" 
                                                                    class="btn btn-sm btn-primary">
                                                                        <i class="ti ti-download"></i> View Document
                                                                    </a>
                                                                    <input type="hidden" name="education[{{ $key }}][existing_document]" value="{{ $education['document_path'] }}">
                                                                </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="form-group col-md-12 text-end">
                                                    <button type="button" class="btn btn-danger remove-education-row">
                                                        <i class="fa fa-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="row education-detail-row">
                                            <div class="form-group col-md-6">
                                                {!! Form::label('education[0][degree]', __('Degree'), ['class' => 'form-label']) !!}
                                                <select name="education[0][degree]" class="form-control degree">
                                                    <option value="10th">{{ __('10th') }}</option>
                                                    <option value="12th">{{ __('12th') }}</option>
                                                    <option value="Bachelor">{{ __('Bachelor') }}</option>
                                                    <option value="Master">{{ __('Master') }}</option>
                                                    <option value="PhD">{{ __('PhD') }}</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('education[0][college_name]', __('College Name'), ['class' => 'form-label']) !!}
                                                {!! Form::text('education[0][college_name]', null, [
                                                    'class' => 'form-control college-name',
                                                    'placeholder' => 'Enter college name',
                                                ]) !!}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('education[0][passing_year]', __('Passing Year'), ['class' => 'form-label']) !!}
                                                <select name="education[0][passing_year]" class="form-control passing-year">
                                                    <option value="" disabled selected>{{ __('Select Year') }}</option>
                                                    @for ($year = 1997; $year <= 2040; $year++)
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('education[0][grade]', __('Grade'), ['class' => 'form-label']) !!}
                                                {!! Form::number('education[0][grade]', null, [
                                                    'class' => 'form-control grade',
                                                    'placeholder' => 'Enter grade (e.g., 4.0)',
                                                    'step' => '0.1',
                                                    'min' => '0',
                                                    'max' => '10',
                                                ]) !!}
                                            </div>
                                            <div class="form-group col-md-12">
                                                {!! Form::label("education[0][document]", __('Education Document'), ['class' => 'form-label']) !!}
                                                <div class="choose-files">
                                                    <label for="education[0][document]">
                                                        <div class="bg-primary document cursor-pointer">
                                                            <i class="ti ti-upload"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" 
                                                            name="education[0][document]" 
                                                            id="education[0][document]" 
                                                            class="form-control file education-document"
                                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-12 text-end">
                                                <button type="button" class="btn btn-danger remove-education-row">
                                                    <i class="fa fa-trash"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group mt-3">
                                    <button type="button" class="btn btn-primary add-education-row">
                                        <i class="fa fa-plus"></i> Add Education
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Account Detail Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Bank Account Detail') }}</h5>
                            </div>
                            <div class="card-body employee-detail-create-body">
                                <div class="row">
                                    <!-- Left Column -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                                            {!! Form::text('account_holder_name', old('account_holder_name', $employee->account_holder_name), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter account holder name',
                                            ]) !!}
                                        </div>
                                        <div class="form-group">
                                            {!! Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) !!}
                                            {!! Form::text('bank_name', old('bank_name', $employee->bank_name), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter bank name',
                                            ]) !!}
                                        </div>
                                        <div class="form-group">
                                            {!! Form::label('branch_location', __('Branch Location'), ['class' => 'form-label']) !!}
                                            {!! Form::text('branch_location', old('branch_location', $employee->branch_location), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter branch location',
                                            ]) !!}
                                        </div>
                                    </div>
                                    <!-- Right Column -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('account_number', __('Account Number'), ['class' => 'form-label']) !!}
                                            {!! Form::text('account_number', old('account_number', $employee->account_number), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter account number',
                                            ]) !!}
                                        </div>
                                        <div class="form-group">
                                            {!! Form::label('bank_identifier_code', __('Bank Identifier Code'), ['class' => 'form-label']) !!}
                                            {!! Form::text('bank_identifier_code', old('bank_identifier_code', $employee->bank_identifier_code), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter bank identifier code',
                                            ]) !!}
                                        </div>
                                        <div class="form-group">
                                            {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => 'form-label']) !!}
                                            {!! Form::text('tax_payer_id', old('tax_payer_id', $employee->tax_payer_id), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter tax payer id',
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="float-end">
                    <button type="submit" class="btn btn-primary">{{ 'Update' }}</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $('input[type="file"]').change(function(e) {
            var file = e.target.files[0].name;
            var file_name = $(this).attr('data-filename');
            $('.' + file_name).append(file);
        });
    </script>
    <script>
        $(document).ready(function() {
            var b_id = $('#branch_id').val();
            // getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch_id]', function() {
            var branch_id = $(this).val();

            getDepartment(branch_id);
        });

        function getDepartment(bid) {
            $.ajax({
                url: '{{ route('monthly.getdepartment') }}',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('.department_id').empty();
                    var emp_selct = `<select class="form-control department_id" name="department_id" id="choices-multiple"
                                            placeholder="Select Department" required>
                                            </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value=""> {{ __('Select Department') }} </option>');
                    $.each(data, function(key, value) {
                        $('.department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        }

        $(document).ready(function() {
            var branch_id = $('#branch_id').val();
            var department_id = $('.department_id').val();
            
            // Fetch designations based on the current department
            if (department_id) {
                getDesignation(department_id).then(() => {
                    // After loading designations, set the selected designation
                    if ({{ $employee->designation_id ?? 'null' }}) {
                        $('.designation_id').val({{ $employee->designation_id }});
                    }
                });
                
                // Load reporting managers for the current department
                getEmployeesByDepartment(department_id).then(() => {
                    // After loading employees, set the selected reporting manager
                    if ({{ $employee->reporting_manager ?? 'null' }}) {
                        $('.reporting_manager_id').val({{ $employee->reporting_manager }});
                    }
                });
            }
        });

        // Make getDesignation return a Promise
        function getDesignation(did) {
            return new Promise((resolve) => {
                $.ajax({
                    url: '{{ route("employee.json") }}',
                    type: 'POST',
                    data: {
                        "department_id": did,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        $('.designation_id').empty();
                        $('.designation_id').append('<option value="">{{ __("Select Designation") }}</option>');
                        
                        $.each(data, function(key, value) {
                            $('.designation_id').append('<option value="' + key + '">' + value + '</option>');
                        });
                        
                        resolve(); // Resolve the promise when done
                    }
                });
            });
        }

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
            getEmployeesByDepartment(department_id);
        });

        function getEmployeesByDepartment(department_id) {
            return new Promise((resolve) => {
                if (!department_id) {
                    $('.reporting_manager_id').empty();
                    $('.reporting_manager_id').append('<option value="">{{ __('Select Reporting Manager') }}</option>');
                    resolve();
                    return;
                }
                
                $.ajax({
                    url: '{{ route('employee.getemployees') }}',
                    type: 'POST',
                    data: {
                        "department_id": department_id,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        var currentValue = $('.reporting_manager_id').val();
                        $('.reporting_manager_id').empty();
                        $('.reporting_manager_id').append('<option value="">{{ __('Select Reporting Manager') }}</option>');
                        $.each(data, function(key, value) {
                            $('.reporting_manager_id').append('<option value="' + key + '">' + value + '</option>');
                        });
                        // Restore the selected value if it still exists
                        if (currentValue) {
                            $('.reporting_manager_id').val(currentValue);
                        }
                        resolve();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching employees:', error);
                        $('.reporting_manager_id').empty();
                        $('.reporting_manager_id').append('<option value="">{{ __('Select Reporting Manager') }}</option>');
                        resolve();
                    }
                });
            });
        }
    </script>



    <script>
        // Education Details Dynamic Rows
        $(document).ready(function() {
            let educationRowCount = {{ !empty($employee->education) ? count($employee->education) : 1 }};
            
            // Add new education row
            $('.add-education-row').click(function() {
                const newRow = `
                    <div class="row education-detail-row mb-3">
                        <div class="form-group col-md-6">
                            <label class="form-label">Degree</label>
                            <select name="education[${educationRowCount}][degree]" class="form-control degree">
                                <option value="10th">10th</option>
                                <option value="12th">12th</option>
                                <option value="Bachelor">Bachelor</option>
                                <option value="Master">Master</option>
                                <option value="PhD">PhD</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">College Name</label>
                            <input type="text" name="education[${educationRowCount}][college_name]" 
                                   class="form-control college-name" placeholder="Enter college name">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Passing Year</label>
                            <select name="education[${educationRowCount}][passing_year]" class="form-control passing-year">
                                <option value="" disabled selected>Select Year</option>
                                @for ($year = 1997; $year <= 2040; $year++)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Grade</label>
                            <input type="number" name="education[${educationRowCount}][grade]" 
                                   class="form-control grade" placeholder="Enter grade" step="0.1" min="0" max="10">
                        </div>
                        <div class="form-group col-md-12">
                            <label class="form-label">Education Document</label>
                            <div class="choose-files">
                                <label for="education[${educationRowCount}][document]">
                                    <div class="bg-primary document cursor-pointer">
                                        <i class="ti ti-upload"></i> Choose file here
                                    </div>
                                    <input type="file" name="education[${educationRowCount}][document]"
                                           id="education[${educationRowCount}][document]"
                                           class="form-control file education-document"
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                </label>
                            </div>
                        </div>
                        <div class="form-group col-md-12 text-end">
                            <button type="button" class="btn btn-danger remove-education-row">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                
                $('#education-details-container').append(newRow);
                educationRowCount++;
            });
            
            // Remove education row
            $(document).on('click', '.remove-education-row', function() {
                $(this).closest('.education-detail-row').remove();
            });

            // Experience Details Dynamic Rows
            let experienceRowCount = {{ !empty($employee->experience) ? count($employee->experience) : 1 }};

            // Add new experience row
            $(document).on('click', '.add-experience-row', function() {
                const newRow = `
                    <div class="row experience-detail-row mb-3">
                        <div class="form-group col-md-6">
                            <label class="form-label">Previous Company Name</label>
                            <input type="text" name="experience[${experienceRowCount}][previous_company_name]" 
                                class="form-control" placeholder="Enter previous company name">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Designation</label>
                            <input type="text" name="experience[${experienceRowCount}][previous_designation]" 
                                class="form-control" placeholder="Enter designation">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="experience[${experienceRowCount}][start_date]" 
                                class="form-control" placeholder="Select start date">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="experience[${experienceRowCount}][end_date]" 
                                class="form-control" placeholder="Select end date">
                        </div>
                        <div class="form-group col-md-12">
                            <label class="form-label">Previous Salary</label>
                            <input type="number" name="experience[${experienceRowCount}][previous_salary]" 
                                class="form-control" placeholder="Enter previous salary">
                        </div>
                        <div class="form-group col-md-12 text-end">
                            <button type="button" class="btn btn-danger remove-experience-row">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                
                $('#experience-details-container').append(newRow);
                experienceRowCount++;
            });

            // Remove experience row
            $(document).on('click', '.remove-experience-row', function() {
                $(this).closest('.experience-detail-row').remove();
            });
        });

        // Phone number validation
        function validateNumbers() {
            const phone = document.getElementsByName('phone')[0].value;
            const officePhoneOne = document.getElementsByName('office_phone_one')[0].value;
            const officePhoneTwo = document.getElementsByName('office_phone_two')[0].value;
            const emergencyNumber = document.getElementsByName('emergency_number')[0].value;

            const numbers = [phone, officePhoneOne, officePhoneTwo, emergencyNumber];
            const errorIds = ['phone-error', 'office_phone_one-error', 'office_phone_two-error', 'emergency_number-error'];
            
            // Clear previous errors
            errorIds.forEach(id => document.getElementById(id).innerText = '');
            
            // Check for duplicates
            for (let i = 0; i < numbers.length; i++) {
                if (numbers[i]) {
                    for (let j = 0; j < numbers.length; j++) {
                        if (i !== j && numbers[i] && numbers[i] === numbers[j]) {
                            document.getElementById(errorIds[i]).innerText = 'Do not use the same number in multiple fields.';
                            document.getElementById(errorIds[j]).innerText = 'Do not use the same number in multiple fields.';
                        }
                    }
                }
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            // Handle education document preview
            $(document).on('change', '.education-document', function() {
                const input = this;
                const row = $(this).closest('.education-detail-row');
                
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        // Remove any existing preview
                        row.find('.document-preview').remove();
                        
                        // Add preview for image files
                        if (input.files[0].type.match('image.*')) {
                            const preview = $('<img class="document-preview mt-2" style="max-width: 200px; max-height: 200px;">');
                            preview.attr('src', e.target.result);
                            row.find('.choose-files').append(preview);
                        }
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            });
            
            // Handle disabled form fields before submission
            $('form').on('submit', function() {
                // Enable disabled fields temporarily before form submission
                // This ensures the values are included in the form data
                $(this).find(':disabled').prop('disabled', false);
            });
        });
    </script>
@endpush