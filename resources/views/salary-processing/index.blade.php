@extends('layouts.admin')

@section('page-title')
    {{ __('Salary Processing') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Salary Processing') }}</li>
@endsection

@section('content')
    <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12 mt-4">
        <div class="card">
            <div class="card-body">
                {{ Form::open(['route' => ['salary-processing.index'], 'method' => 'GET', 'id' => 'salary_filter_form']) }}
                <div class="d-flex align-items-center justify-content-end">
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mx-2">
                        <div class="btn-box">
                            {{ Form::label('month', __('Select Month'), ['class' => 'form-label']) }}
                            {{ Form::select('month', $monthOptions, $month, ['class' => 'form-control select', 'id' => 'month']) }}
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mx-2">
                        <div class="btn-box">
                            {{ Form::label('year', __('Select Year'), ['class' => 'form-label']) }}
                            {{ Form::select('year', $yearOptions, $year, ['class' => 'form-control select', 'id' => 'year']) }}
                        </div>
                    </div>
                    <div class="col-auto float-end ms-2 mt-4">
                        <a href="#" class="btn btn-primary"
                            onclick="document.getElementById('salary_filter_form').submit(); return false;"
                            data-bs-toggle="tooltip" title="{{ __('Filter') }}"
                            data-original-title="{{ __('Filter') }}">
                            {{ __('Filter') }}
                        </a>
                    </div>
                    <div class="col-auto float-end ms-2 mt-4">
                        <a href="{{ route('salary-processing.export', ['month' => $month, 'year' => $year]) }}" class="btn btn-success"
                            data-bs-toggle="tooltip" title="{{ __('Export') }}"
                            data-original-title="{{ __('Export') }}">
                            <i class="ti ti-file-export"></i>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-12">
                        <h5>{{ __('Salary Processing') }} - {{ $monthOptions[$month] }} {{ $year }}</h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="salary-processing-table">
                        <thead>
                            <tr>
                                <th>{{ __('Employee Name') }}</th>
                                <th>{{ __('Total Monthly Days') }}</th>
                                <th>{{ __('Total Late Marks') }}</th>
                                <th>{{ __('Late Mark Deduction') }}</th>
                                <th>{{ __('LOP Days') }}</th>
                                <th>{{ __('Payable Days') }}</th>
                                <th>{{ __('Actual Payable Days') }}</th>
                                <th>{{ __('Actual Salary') }}</th>
                                <th>{{ __('Daily Salary') }}</th>
                                <th>{{ __('Salary Arrears') }}</th>
                                <th>{{ __('Monthly Salary') }}</th>
                                <th>{{ __('Final Payable Salary') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($salaryData) > 0)
                                @foreach($salaryData as $data)
                                    <tr>
                                        <td>{{ $data['employee_name'] }}</td>
                                        <td>{{ $data['total_monthly_days'] }}</td>
                                        <td>{{ $data['total_late_marks'] }}</td>
                                        <td>{{ $data['late_mark_deduction_amount'] ?? 0 }}</td>
                                        <td>{{ $data['lop_days'] + 0 }}</td>
                                        <td>{{ $data['payable_days'] + 0 }}</td>
                                        <td>{{ $data['actual_payable_days'] + 0 }}</td>
                                        <td>{{ \Auth::user()->priceFormat($data['actual_salary']) }}</td>
                                        <td>{{ \Auth::user()->priceFormat($data['daily_salary']) }}</td>
                                        <td>{{ \Auth::user()->priceFormat($data['salary_arrears']) }}</td>
                                        <td>{{ \Auth::user()->priceFormat($data['monthly_salary']) }}</td>
                                        <td><strong>{{ \Auth::user()->priceFormat($data['final_payable_salary']) }}</strong></td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-url="{{ route('late-mark-deductions.create', ['employee_id' => $data['employee_id'], 'month' => $year.'-'.$month]) }}"
                                                        data-ajax-popup="true" data-title="{{ __('Add Late Mark Deductions') }}"
                                                        data-bs-toggle="tooltip" title="{{ __('Add Late Mark Deductions') }}">
                                                        <i class="ti ti-plus text-white"></i>
                                                    </a>
                                                </div>
                                            </span>
                                            <span>
                                                @php
                                                    $hasArrears = $data['salary_arrears'] > 0;
                                                    $arrearsBtnClass = $hasArrears ? 'bg-success' : 'bg-info';
                                                    $arrearsIcon = $hasArrears ? 'ti ti-pencil' : 'ti ti-plus';
                                                    $arrearsTitle = $hasArrears ? __('Edit Salary Arrears') : __('Add Salary Arrears');
                                                @endphp
                                                <div class="action-btn {{ $arrearsBtnClass }} ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-url="{{ route('salary-arrears-popup.create', ['employee_id' => $data['employee_id'], 'month' => $year.'-'.$month]) }}"
                                                        data-ajax-popup="true" data-title="{{ $arrearsTitle }}"
                                                        data-bs-toggle="tooltip" title="{{ $arrearsTitle }}">
                                                        <i class="{{ $arrearsIcon }} text-white"></i>
                                                    </a>
                                                </div>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="14" class="text-center">{{ __('No employees with salary found.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                        @if(count($salaryData) > 0)
                        <tfoot>
                            <tr>
                                <th>{{ collect($salaryData)->sum('late_mark_deduction_amount') }}</th>
                                <th colspan="4" class="text-right">{{ __('Total:') }}</th>
                                <th>{{ \Auth::user()->priceFormat(collect($salaryData)->sum('actual_salary')) }}</th>
                                <th>{{ \Auth::user()->priceFormat(collect($salaryData)->sum('daily_salary')) }}</th>
                                <th>{{ \Auth::user()->priceFormat(collect($salaryData)->sum('salary_arrears')) }}</th>
                                <th>{{ \Auth::user()->priceFormat(collect($salaryData)->sum('monthly_salary')) }}</th>
                                <th><strong>{{ \Auth::user()->priceFormat(collect($salaryData)->sum('final_payable_salary')) }}</strong></th>
                                <th></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            var table = new simpleDatatables.DataTable("#salary-processing-table", {
                searchable: true,
                sortable: true,
            });
        });
    </script>
@endpush

