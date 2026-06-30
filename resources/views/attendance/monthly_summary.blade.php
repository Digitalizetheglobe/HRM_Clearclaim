@extends('layouts.admin')

@section('page-title')
    {{ __('Monthly Working Hours Summary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Monthly Working Hours Summary') }}</li>
@endsection

@push('css-page')
<style>
    .table thead th {
        background-color: #004696;
        color: #ffffff;
    }
    .text-success-custom {
        color: #28a745 !important;
        font-weight: 500;
    }
    .text-danger-custom {
        color: #dc3545 !important;
        font-weight: 500;
    }
    .info-alert {
        background-color: #e7f1ff;
        color: #0c5460;
        border: 1px solid #b8daff;
        border-radius: 8px;
        padding: 10px 15px;
        margin-bottom: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
    }
    .info-alert i {
        margin-right: 10px;
        font-size: 18px;
        color: #0056b3;
    }
</style>
@endpush

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['monthly.working.hours.index'], 'method' => 'get', 'id' => 'monthly_summary_filter']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                {{ Form::select('month', ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'], isset($_GET['month']) ? $_GET['month'] : date('m'), ['class' => 'form-control select', 'id' => 'month']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('year', __('Year'), ['class' => 'form-label']) }}
                                @php
                                    $currentYear = date('Y');
                                    $years = [];
                                    for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
                                        $years[$i] = $i;
                                    }
                                @endphp
                                {{ Form::select('year', $years, isset($_GET['year']) ? $_GET['year'] : date('Y'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('department', __('Department'), ['class' => 'form-label']) }}
                                {{ Form::select('department', $departments, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                                {{ Form::select('employee', $employeeFilter, isset($_GET['employee']) ? $_GET['employee'] : '', ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('search', __('Search Name'), ['class' => 'form-label']) }}
                                {{ Form::text('search', isset($_GET['search']) ? $_GET['search'] : '', ['class' => 'form-control', 'placeholder' => 'Search Employee...']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn btn-sm btn-primary"
                               onclick="document.getElementById('monthly_summary_filter').submit(); return false;"
                               data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{ route('monthly.working.hours.index') }}" class="btn btn-sm btn-danger"
                               data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                            </a>
                            <a href="#" class="btn btn-sm btn-success"
                               onclick="exportExcel(); return false;"
                               data-bs-toggle="tooltip" title="{{ __('Export') }}">
                                <span class="btn-inner--icon"><i class="ti ti-file-export text-white-off "></i></span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    
    <div class="info-alert">
        <i class="ti ti-info-circle"></i>
        <span><b>Expected Hours = Total Attendance Days × 9 Hours</b> (Holidays & Weekly Offs are excluded. Approved Leave Days are considered as completed working days.)</span>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th>Total Attendance Days</th>
                                    <th>Expected Hours (9 Hrs/Day)</th>
                                    <th>Actual Hours Worked</th>
                                    <th>Overtime (+)</th>
                                    <th>Shortfall (-)</th>
                                    <th>Net Hours (Actual - Expected)</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $srNo = 1;
                                @endphp
                                @foreach ($summaryData['employees'] as $employee)
                                    <tr>
                                        <td>{{ $srNo++ }}</td>
                                        <td><a href="#" class="btn btn-outline-primary">{{ $employee['employee_id'] }}</a></td>
                                        <td>{{ $employee['name'] }}</td>
                                        <td>{{ $employee['department'] }}</td>
                                        <td>{{ $employee['working_days'] }}</td>
                                        <td>{{ $employee['expected_hours'] }}</td>
                                        <td>{{ $employee['actual_hours'] }}</td>
                                        <td class="text-success-custom">{{ $employee['overtime'] }}</td>
                                        <td class="text-danger-custom">{{ $employee['shortfall'] }}</td>
                                        <td class="{{ strpos($employee['net_hours'], '+') !== false ? 'text-success-custom' : (strpos($employee['net_hours'], '-') !== false && $employee['net_hours'] !== '-00:00' ? 'text-danger-custom' : '') }}">{{ $employee['net_hours'] }}</td>
                                        <td>-</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background-color: #f1f6f9; font-weight: bold;">
                                <tr>
                                    <td colspan="4" class="text-end">TOTAL</td>
                                    <td>{{ $summaryData['totals']['working_days'] }}</td>
                                    <td>
                                        @php
                                            $totExpH = floor($summaryData['totals']['expected'] / 3600);
                                            $totExpM = floor(($summaryData['totals']['expected'] % 3600) / 60);
                                            echo sprintf('%02d:%02d', $totExpH, $totExpM);
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $totActH = floor($summaryData['totals']['actual'] / 3600);
                                            $totActM = floor(($summaryData['totals']['actual'] % 3600) / 60);
                                            echo sprintf('%02d:%02d', $totActH, $totActM);
                                        @endphp
                                    </td>
                                    <td class="text-success-custom">
                                        @php
                                            $totOvH = floor($summaryData['totals']['overtime'] / 3600);
                                            $totOvM = floor(($summaryData['totals']['overtime'] % 3600) / 60);
                                            echo sprintf('%02d:%02d', $totOvH, $totOvM);
                                        @endphp
                                    </td>
                                    <td class="text-danger-custom">
                                        @php
                                            $totShH = floor($summaryData['totals']['shortfall'] / 3600);
                                            $totShM = floor(($summaryData['totals']['shortfall'] % 3600) / 60);
                                            echo sprintf('%02d:%02d', $totShH, $totShM);
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $netTotSec = $summaryData['totals']['overtime'] - $summaryData['totals']['shortfall'];
                                            $netTotPrefix = $netTotSec >= 0 ? '+' : '-';
                                            $absNet = abs($netTotSec);
                                            $netTotH = floor($absNet / 3600);
                                            $netTotM = floor(($absNet % 3600) / 60);
                                            echo $netTotPrefix . sprintf('%02d:%02d', $netTotH, $netTotM);
                                        @endphp
                                    </td>
                                    <td>-</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Department Wise Summary') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Department Name</th>
                                    <th>Total Employees</th>
                                    <th>Expected Working Hours</th>
                                    <th>Actual Hours Worked</th>
                                    <th>Total Overtime</th>
                                    <th>Total Shortfall</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summaryData['departments'] as $deptName => $deptData)
                                    <tr>
                                        <td>{{ $deptName }}</td>
                                        <td>{{ $deptData['count'] }}</td>
                                        <td>
                                            @php
                                                $h = floor($deptData['expected'] / 3600);
                                                $m = floor(($deptData['expected'] % 3600) / 60);
                                                echo sprintf('%02d:%02d', $h, $m);
                                            @endphp
                                        </td>
                                        <td>
                                            @php
                                                $h = floor($deptData['actual'] / 3600);
                                                $m = floor(($deptData['actual'] % 3600) / 60);
                                                echo sprintf('%02d:%02d', $h, $m);
                                            @endphp
                                        </td>
                                        <td class="text-success-custom">
                                            @php
                                                $h = floor($deptData['overtime'] / 3600);
                                                $m = floor(($deptData['overtime'] % 3600) / 60);
                                                echo sprintf('%02d:%02d', $h, $m);
                                            @endphp
                                        </td>
                                        <td class="text-danger-custom">
                                            @php
                                                $h = floor($deptData['shortfall'] / 3600);
                                                $m = floor(($deptData['shortfall'] % 3600) / 60);
                                                echo sprintf('%02d:%02d', $h, $m);
                                            @endphp
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

@push('script-page')
<script>
    function exportExcel() {
        var form = document.getElementById('monthly_summary_filter');
        var originalAction = form.action;
        form.action = '{{ route("monthly.working.hours.export") }}';
        form.submit();
        form.action = originalAction;
    }
</script>
@endpush
