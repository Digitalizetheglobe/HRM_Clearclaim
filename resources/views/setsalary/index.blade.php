@extends('layouts.admin')

@section('page-title')
   {{ __('Manage Employee Salary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Salary') }}</li>
@endsection


@section('content')
<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12 col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee Id') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Salary') }}</th>
                                <th>{{ __('Net Salary') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <tr>
                                    <td>
                                        <a href="{{ route('setsalary.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                            class="btn btn-outline-primary">
                                            {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}
                                        </a>
                                    </td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ \Auth::user()->priceFormat($employee->salary) }}</td>
                                    <td>{{ !empty($employee->get_net_salary()) ? \Auth::user()->priceFormat($employee->get_net_salary()) : '-' }}
                                    </td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="#" data-url="{{ route('employee.basic.salary', $employee->id) }}" data-ajax-popup="true"
                                                    data-title="{{ __('Add Salary') }}"
                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                    title="" data-bs-original-title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            
                                            @can('Edit Set Salary')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" data-size="lg" data-url="{{ route('salary-increment.form', $employee->id) }}" data-ajax-popup="true"
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                        title="" data-bs-original-title="{{ __('Salary Increment') }}">
                                                        <i class="ti ti-arrow-up text-white"></i>
                                                    </a>
                                                    
                                                </div>
                                            @endcan
                                            
                                            @if($employee->salaryIncrements && $employee->salaryIncrements->count() > 0)
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="{{ route('salary-increment.pdf', $employee->salaryIncrements->first()->id) }}"
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                        title="" data-bs-original-title="{{ __('Download Increment Letter') }}">
                                                        <i class="ti ti-download text-white"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection