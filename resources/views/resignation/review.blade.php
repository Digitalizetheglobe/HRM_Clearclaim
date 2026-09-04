@extends('layouts.admin')

@section('page-title')
    {{ __('Review Resignation') }}
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Resignation Details') }}</h5>
            </div>
            <div class="card-body">
                @php
                    $user = \Auth::user();
                    $isManagerOrCompany = $user->hasCompanyAccess() || 
                                        ($user->type == 'employee' && $user->employee && $user->employee->designation && strcasecmp($user->employee->designation->name, 'Manager') == 0);
                    $isHRUser = $user->hasCompanyAccess() || 
                               ($user->type == 'employee' && $user->employee && $user->employee->department && strcasecmp($user->employee->department->name, 'Human Resources') == 0);
                @endphp
                
                @if($resignation->status == 'pending' && $isManagerOrCompany)
                    {{ Form::open(['route' => ['resignation.manager-approve', $resignation->id], 'method' => 'post']) }}
                @elseif($resignation->status == 'manager_approved' && $isHRUser)
                    {{ Form::open(['route' => ['resignation.approve', $resignation->id], 'method' => 'post']) }}
                @else
                    <div class="row">
                @endif
                
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>{{ __('Employee') }}</label>
                        <input type="text" class="form-control" value="{{ $resignation->employee->name }}" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label>{{ __('Current Status') }}</label>
                        <input type="text" class="form-control" value="@if($resignation->status == 'pending'){{ __('Pending') }}@elseif($resignation->status == 'manager_approved'){{ __('Manager Approved') }}@else{{ __('Approved') }}@endif" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        {{ Form::label('notice_date', __('Resignation Date'), ['class' => 'form-label']) }}
                        {{ Form::date('notice_date', $resignation->notice_date, ['class' => 'form-control', 'required' => 'required']) }}
                    </div>
                    <div class="form-group col-md-6">
                        {{ Form::label('resignation_date', __('Last Working Day'), ['class' => 'form-label']) }}
                        {{ Form::date('resignation_date', $resignation->resignation_date, ['class' => 'form-control', 'required' => 'required']) }}
                    </div>
                    <div class="form-group col-md-12">
                        <label>{{ __('Reason') }}</label>
                        <textarea class="form-control" readonly>{{ $resignation->description }}</textarea>
                    </div>
                    
                    @if($resignation->status == 'pending' && $isManagerOrCompany)
                        <div class="col-md-12 text-end">
                            <a href="{{ route('employee-resignations.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Manager Approve') }}</button>
                        </div>
                    @elseif($resignation->status == 'manager_approved' && $isHRUser)
                        <div class="col-md-12 text-end">
                            <a href="{{ route('resignation.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('HR Approve') }}</button>
                        </div>
                    @else
                        <div class="col-md-12 text-end">
                            <a href="{{ $isManagerOrCompany ? route('employee-resignations.index') : route('resignation.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
                        </div>
                    @endif
                </div>
                
                @if(($resignation->status == 'pending' && $isManagerOrCompany) || ($resignation->status == 'manager_approved' && $isHRUser))
                    {{ Form::close() }}
                @else
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection