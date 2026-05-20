@extends('layouts.admin')

@section('page-title')
    {{ __('Leave Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('leave.index') }}">{{ __('Leave') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave Details') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <h5>{{ __('Leave Details') }}</h5>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form method="GET" action="{{ route('leave-details.index') }}">
                    <div class="row mb-4 align-items-end">
                        <div class="col-md-2">
                            <label for="month" class="form-label">{{ __('Month') }}</label>
                            <select name="month" id="month" class="form-select">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="year" class="form-label">{{ __('Year') }}</label>
                            <select name="year" id="year" class="form-select">
                                @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ $selectedYear == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="department" class="form-label">{{ __('Department') }}</label>
                            <select name="department" id="department" class="form-select">
                                <option value="">{{ __('All Departments') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ $selectedDepartment == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="employee" class="form-label">{{ __('Employee') }}</label>
                            <select name="employee" id="employee" class="form-select">
                                <option value="">{{ __('All Employees') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ $selectedEmployee == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex align-items-center gap-2">
                                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" title="{{ __('Filter') }}">
                                    <i class="ti ti-search"></i>
                                </button>
                                <a href="{{ route('leave-details.index') }}" class="btn btn-danger" data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                    <i class="ti ti-trash-off"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Leave Details Table -->
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee Name') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th>{{ __('Yearly Leaves') }}</th>
                                <th>{{ __('Monthly Leaves') }}</th>
                                <th>{{ __('Leaves Taken') }}</th>
                                <th>{{ __('Pending Leaves') }}</th>
                                <th>{{ __('Remaining Monthly') }}</th>
                                <th>{{ __('Remaining Yearly') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaveDetails as $detail)
                                <tr>
                                    <td>{{ $detail['employee']->name }}</td>
                                    <td>{{ $detail['employee']->department->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ number_format($detail['yearly_leaves'], 2) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ __('Pro-rata entitlement') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ number_format($detail['monthly_leaves'], 2) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ __('Monthly limit (paid)') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">
                                            {{ number_format($detail['leaves_taken'], 2) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ __('Used: :used / :total | Remaining: :remaining', [
                                                'used' => number_format($detail['leaves_taken'], 2),
                                                'total' => number_format($detail['monthly_leaves'], 2),
                                                'remaining' => number_format($detail['remaining_monthly'], 2)
                                            ]) }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ number_format($detail['pending_leaves'], 2) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ __('Pending approval') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $detail['remaining_monthly'] > 0 ? 'success' : 'danger' }}">
                                            {{ number_format($detail['remaining_monthly'], 2) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ __('Available balance') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $detail['remaining_yearly'] > 0 ? 'success' : 'danger' }}">
                                            {{ number_format($detail['remaining_yearly'], 2) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ __('Available balance') }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        {{ __('No leave details found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load employees when department changes
    $('#department').on('change', function() {
        var departmentId = $(this).val();
        if (departmentId) {
            $.get('{{ route("get.employees.by.department") }}', {department_id: departmentId}, function(data) {
                $('#employee').empty().append('<option value="">{{ __("All Employees") }}</option>');
                $.each(data, function(key, value) {
                    $('#employee').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            });
        } else {
            $('#employee').empty().append('<option value="">{{ __("All Employees") }}</option>');
        }
    });
});
</script>
@endsection
