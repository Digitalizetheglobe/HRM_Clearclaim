@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Employee') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee') }}</li>
@endsection



@section('action-button')
    <a href="{{ route('employee.export') }}" data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-original-title="{{ __('Export') }}" class="btn btn-sm btn-primary">
        <i class="ti ti-file-export"></i>
    </a>


    @can('Create Assets')
            <a href="{{ route('employee.create') }}" 
               data-title="{{ __('Create New Employee') }}" 
               class="btn btn-sm btn-primary ">
                <i class="ti ti-plus"></i>
            </a>
    @endcan
@endsection



@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <ul class="nav nav-tabs" id="employeeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab" aria-controls="active" aria-selected="true">
                                {{ __('Active Employees') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="left-tab" data-bs-toggle="tab" data-bs-target="#left" type="button" role="tab" aria-controls="left" aria-selected="false">
                                {{ __('Left Employees') }}
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3" id="employeeTabsContent">
                        <!-- Active Employees Tab -->
                        <div class="tab-pane fade show active" id="active" role="tabpanel" aria-labelledby="active-tab">
                            <div class="table-responsive">
                                <table class="table" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee ID') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Department') }}</th>
                                            <th>{{ __('Designation') }}</th>
                                            <th>{{ __('Date Of Joining') }}</th>
                                            @if (Auth::user()->type != 'hr' && (Gate::check('Edit Employee') || Gate::check('Delete Employee')))
                                                <th width="100px">{{ __('Action') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($activeEmployees as $employee)
                                            <tr>
                                                <td>
                                                    @can('Show Employee')
                                                        <a class="btn btn-outline-primary btn-sm"
                                                            href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">
                                                            {{ $employee->formatted_id }}
                                                        </a>
                                                    @else
                                                        <span class="badge bg-primary">
                                                            {{ $employee->formatted_id }}
                                                        </span>
                                                    @endcan
                                                </td>
                                                <td>{{ $employee->name ?? '-' }}</td>
                                                <td>{{ $employee->email ?? '-' }}</td>  

                                                <td>
                                                    <span class="">
                                                        {{ $employee->department?->name ?? '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="">
                                                        {{ $employee->designation?->name ?? '-' }}
                                                    </span>
                                                </td>
                                                <td>{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                                                
                                                @if (Auth::user()->type != 'hr' && (Gate::check('Edit Employee') || Gate::check('Delete Employee')))
                                                    <td class="Action" style="white-space: nowrap;">
                                                        @if (($employee->user?->is_active ?? 0) == 1 && ($employee->user?->is_disable ?? 0) == 1)
                                                            @can('Edit Employee')
                                                                <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}" 
                                                                   class="btn btn-sm btn-icon-only bg-info ms-2" 
                                                                   data-bs-toggle="tooltip" 
                                                                   title="{{ __('Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            @endcan

                                                            @can('Delete Employee')
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['employee.destroy', $employee->id],
                                                                    'style' => 'display:inline'
                                                                ]) !!}
                                                                <a href="#"
                                                                   class="btn btn-sm btn-icon-only bg-danger ms-2 bs-pass-para"
                                                                   data-bs-toggle="tooltip" 
                                                                   title="{{ __('Delete') }}">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            @endcan
                                                        @else
                                                            <i class="ti ti-lock"></i>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Left Employees Tab -->
                        <div class="tab-pane fade" id="left" role="tabpanel" aria-labelledby="left-tab">
                            <div class="table-responsive">
                                <table class="table mt-5" id="pc-dt-simple2">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee ID') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Branch') }}</th>
                                            <th>{{ __('Department') }}</th>
                                            <th>{{ __('Designation') }}</th>
                                            <th>{{ __('Date Of Joining') }}</th>
                                            <th>{{ __('Termination Date') }}</th>
                                            @if (Auth::user()->type != 'hr' && Gate::check('Show Employee'))
                                                <th width="80px">{{ __('Action') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($leftEmployees as $employee)
                                            @php
                                                $termination = \App\Models\Termination::where('employee_id', $employee->id)->first();
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="">
                                                        <a class="btn btn-outline-primary btn-sm"
                                                            href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">
                                                            {{ $employee->formatted_id }}
                                                        </a>
                                                    </span>
                                                </td>
                                                <td>{{ $employee->name ?? '-' }}</td>
                                                <td>{{ $employee->email ?? '-' }}</td>  
                                                <td>
                                                    <span class="">
                                                        {{ $employee->branch?->name ?? '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="">
                                                        {{ $employee->department?->name ?? '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="">
                                                        {{ $employee->designation?->name ?? '-' }}
                                                    </span>
                                                </td>
                                                <td>{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                                                <td>
                                                    @if($termination)
                                                        {{ \Auth::user()->dateFormat($termination->termination_date) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                @if (Auth::user()->type != 'hr' && Gate::check('Show Employee'))
                                                    <td class="Action">
                                                        @can('Show Employee')
                                                            <a href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                                               class="btn btn-sm btn-icon-only bg-info"
                                                               data-bs-toggle="tooltip" 
                                                               title="{{ __('View') }}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        @endcan
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize both tables with the same style
            $('#pc-dt-simple').DataTable();
            $('#pc-dt-simple2').DataTable();
            
            // Delete functionality with confirmation
            $(document).on('click', '.bs-pass-para', function(e) {
                e.preventDefault();
                const button = $(this);
                const form = button.closest('form');
                
                if (!confirm('Are you sure you want to delete this employee?')) {
                    return;
                }

                // Show loading state
                button.prop('disabled', true);
                button.html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST', // Laravel needs POST for DELETE method
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Remove the row with animation
                            button.closest('tr').fadeOut(400, function() {
                                $(this).remove();
                                
                                // Show success message
                                showToast('success', response.message);
                                
                                // Handle empty table state
                                if ($('#pc-dt-simple tbody tr').length === 0) {
                                    $('#pc-dt-simple tbody').append(
                                        '<tr><td colspan="8" class="text-center">No employees found</td></tr>'
                                    );
                                }
                            });
                        } else {
                            showToast('error', response.message);
                            button.prop('disabled', false).html('<i class="ti ti-trash"></i>');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Server error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.status === 403) {
                            errorMsg = 'Unauthorized action';
                        }
                        
                        showToast('error', errorMsg);
                        button.prop('disabled', false).html('<i class="ti ti-trash"></i>');
                    }
                });
            });

            // Toast notification function
            function showToast(type, message) {
                const toast = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
                
                $('.toast-container').html(toast);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }
        });
    </script>
@endpush