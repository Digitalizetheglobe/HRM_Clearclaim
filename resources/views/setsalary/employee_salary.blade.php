@extends('layouts.admin')

@section('page-title')
    {{ __('Employee Set Salary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('setsalary') }}">{{ __('Set Salary') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Set Salary') }}</li>
@endsection

@section('content')
    <div class="row justify-content-left">
        <div class="col-xl-5 col-lg-6 col-md-8 col-sm-11 mt-4">
            <div class="card border-0 shadow-lg" style="border-radius: 16px; overflow: hidden; background: linear-gradient(145deg, #ffffff, #f8f9fa);">
                <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="m-0 text-dark fw-bold" style="font-family: 'Inter', sans-serif;">{{ __('Employee Salary Details') }}</h5>
                        @can('Create Set Salary')
                            <a data-url="{{ route('employee.basic.salary', $employee->id) }}" data-ajax-popup="true"
                                data-title="{{ __('Set Basic Salary') }}" data-bs-toggle="tooltip" title=""
                                class="btn btn-sm btn-primary d-inline-flex align-items-center justify-content-center" 
                                style="border-radius: 8px; width: 32px; height: 32px;"
                                data-bs-original-title="{{ __('Set Salary') }}">
                                <i class="ti ti-plus text-white" style="font-size: 1.1rem;"></i>
                            </a>
                        @endcan
                    </div>
                </div>
                
                <div class="card-body px-4 py-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="avatar-wrapper me-3 bg-light-primary text-primary d-flex align-items-center justify-content-center" 
                             style="width: 54px; height: 54px; border-radius: 12px; background-color: rgba(9, 144, 246, 0.1);">
                            <i class="ti ti-user" style="font-size: 1.8rem;"></i>
                        </div>
                        <div>
                            <h6 class="m-0 text-dark fw-semibold" style="font-size: 1.05rem;">{{ $employee->name }}</h6>
                            <small class="text-muted">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</small>
                        </div>
                    </div>

                    <div class="p-3 mb-2" style="border-radius: 12px; background: linear-gradient(135deg, rgba(9, 144, 246, 0.05), rgba(9, 144, 246, 0.02)); border: 1px solid rgba(9, 144, 246, 0.1);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase tracking-wider fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ __('Basic Salary') }}</span>
                                <h3 class="m-0 mt-1 text-primary fw-bold" style="font-family: 'Outfit', 'Inter', sans-serif;">
                                    {{ \Auth::user()->priceFormat($employee->salary) }}
                                </h3>
                            </div>
                            <div class="bg-primary text-white d-flex align-items-center justify-content-center" 
                                 style="width: 44px; height: 44px; border-radius: 10px; box-shadow: 0 4px 10px rgba(9, 144, 246, 0.3);">
                                <i class="ti ti-wallet" style="font-size: 1.4rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
