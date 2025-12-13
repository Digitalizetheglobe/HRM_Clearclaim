@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Attendance List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Attendance List') }}</li>
@endsection


@push('script-page')
    <script>
        $('input[name="type"]:radio').on('change', function(e) {
            var type = $(this).val();

            if (type == 'monthly') {
                $('.month').addClass('d-block');
                $('.month').removeClass('d-none');  
                $('.date').addClass('d-none');
                $('.date').removeClass('d-block');
            } else {
                $('.date').addClass('d-block');
                $('.date').removeClass('d-none');
                $('.month').addClass('d-none');
                $('.month').removeClass('d-block');
            }
        });

        $('input[name="type"]:radio:checked').trigger('change');
    </script>

    <script>
        $(document).ready(function() {
            var b_id = $('#branch_id').val();
            // getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch]', function() {
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
                                            placeholder="Select Department" >
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
    </script>

    <script>
        // Function to process missing punch outs
        function processMissingPunchOuts() {
            if (!confirm('This will process all missing punch-outs for past dates. First missing punch-out in each month will be skipped. Continue?')) {
                return false;
            }
            
            var btn = document.getElementById('processMissingPunchOutsBtn');
            if (!btn) {
                alert('Button not found!');
                return false;
            }
            
            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            var routeUrl = '{{ route("attendance.processMissingPunchOuts") }}';
            var token = '{{ csrf_token() }}';
            
            // Use fetch API as fallback if jQuery fails
            fetch(routeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Missing punch-outs processed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error occurred'));
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing missing punch-outs. Please check the console for details.');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
            
            return false;
        }
        
        $(document).ready(function() {
            console.log('Document ready - Setting up Process Missing Punch Outs button');
            
            // Check if button exists
            var btn = $('#processMissingPunchOutsBtn');
            if (btn.length === 0) {
                console.warn('Process Missing Punch Outs button not found!');
            } else {
                console.log('Process Missing Punch Outs button found!');
            }
            
            // Process Missing Punch Outs Button - Use event delegation
            $(document).on('click', '#processMissingPunchOutsBtn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Button clicked via jQuery!');
                processMissingPunchOuts();
                return false;
            });
        });
    </script>
@endpush
@section('action-button')
@endsection
@section('content')
    @if (session('status'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {!! session('   ') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['attendanceemployee.index'], 'method' => 'get', 'id' => 'attendanceemployee_filter']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">

                                    <div class="col-3">
                                        <label class="form-label">{{ __('Type') }}</label> <br>

                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="monthly" value="monthly" name="type"
                                                class="form-check-input"
                                                {{ isset($_GET['type']) && $_GET['type'] == 'monthly' ? 'checked' : 'checked' }}>
                                            <label class="form-check-label" for="monthly">{{ __('Monthly') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="daily" value="daily" name="type"
                                                class="form-check-input"
                                                {{ isset($_GET['type']) && $_GET['type'] == 'daily' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="daily">{{ __('Daily') }}</label>
                                        </div>

                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                        <div class="btn-box">
                                            {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                            {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'month-btn form-control month-btn']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                        <div class="btn-box">
                                            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : '', ['class' => 'form-control month-btn']) }}
                                        </div>
                                    </div>
                                    @if (\Auth::user()->type != 'employee')
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                                                {{ Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control select branch_id', 'id' => 'branch_id']) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{ Form::label('department', __('department'), ['class' => 'form-label']) }}
                                                {{ Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control select department_id', 'id' => 'department_id']) }}
                                            </div>

                                            {{-- <div class="form-icon-user" id="department_div">
                                                {{ Form::label('department', __('Department'), ['class' => 'form-label']) }}
                                                <select class="form-control select department_id" name="department_id"
                                                    id="department_id" placeholder="Select Department">
                                                </select>
                                            </div> --}}
                                            

                                        </div>
                                        
                                    @endif

                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">

                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('attendanceemployee_filter').submit(); return false;"
                                            data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>

                                        <a href="{{ route('attendanceemployee.index') }}" class="btn btn-sm btn-danger "
                                            data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                            data-original-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off "></i></span>
                                        </a>

                                        <a href="#" data-url="{{ route('attendance.file.import') }}"
                                            data-ajax-popup="true" data-title="{{ __('Import  Attendance CSV File') }}"
                                            data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                            data-bs-original-title="{{ __('Import') }}">
                                            <i class="ti ti-file"></i>
                                        </a>

                                        <a href="{{ route('attendance.export', request()->query()) }}" class="btn btn-sm btn-primary" 
                                            data-bs-toggle="tooltip" title="{{ __('Export') }}" 
                                            data-bs-original-title="{{ __('Export') }}">
                                            <i class="ti ti-download"></i>
                                        </a>

                                        @if (\Auth::user()->type != 'employee')
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                id="processMissingPunchOutsBtn"
                                                onclick="return processMissingPunchOuts();"
                                                data-bs-toggle="tooltip" 
                                                title="{{ __('Process Missing Punch Outs') }}" 
                                                data-bs-original-title="{{ __('Process Missing Punch Outs') }}">
                                                <i class="ti ti-clock"></i> {{ __('Process Missing Punch Outs') }}
                                            </button>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <th>{{ __('Employee') }}</th>
                                    @endif
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Clock-In Time') }}</th>
                                    <th>{{ __('Clock-Out Time') }}</th>
                                    <th>{{ __('Total Hours') }}</th>
                                    <th>{{ __('Difference') }}</th>
                                    @if (Gate::check('Edit Attendance') || Gate::check('Delete Attendance'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendanceEmployee as $attendance)
                                    @php
                                        // Calculate total hours
                                        $totalHours = null;
                                        $totalMinutes = null;
                                        $diffMinutes = null;
                                        
                                        if ($attendance->clock_in != '00:00:00' && $attendance->clock_out != '00:00:00' && $attendance->clock_in && $attendance->clock_out) {
                                            try {
                                                // Combine date with time for proper calculation
                                                $date = $attendance->date;
                                                $inTime = \Carbon\Carbon::parse($date . ' ' . $attendance->clock_in);
                                                $outTime = \Carbon\Carbon::parse($date . ' ' . $attendance->clock_out);
                                                
                                                // Handle case where clock out might be next day (e.g., clock out at 7:00 PM when clock in was 10:00 AM)
                                                if ($outTime->lt($inTime)) {
                                                    $outTime->addDay();
                                                }
                                                
                                                $totalMinutes = $outTime->diffInMinutes($inTime);
                                                
                                                // Calculate difference from standard (9 hours = 540 minutes)
                                                $standardMinutes = 540;
                                                $diffMinutes = $totalMinutes - $standardMinutes;
                                            } catch (\Exception $e) {
                                                $totalMinutes = null;
                                                $diffMinutes = null;
                                            }
                                        }
                                        
                                        // Format total hours
                                        $totalHoursFormatted = '-';
                                        if ($totalMinutes !== null) {
                                            $hours = floor($totalMinutes / 60);
                                            $minutes = $totalMinutes % 60;
                                            $totalHoursFormatted = $hours . 'h ' . $minutes . 'm';
                                        }
                                        
                                        // Format difference
                                        $differenceFormatted = '-';
                                        if ($diffMinutes !== null) {
                                            if ($diffMinutes == 0) {
                                                $differenceFormatted = '0m';
                                            } else {
                                                $sign = $diffMinutes > 0 ? '+' : '-';
                                                $absMinutes = abs($diffMinutes);
                                                
                                                if ($absMinutes >= 60) {
                                                    $diffHours = floor($absMinutes / 60);
                                                    $diffMins = $absMinutes % 60;
                                                    if ($diffMins > 0) {
                                                        $differenceFormatted = $sign . $diffHours . 'h ' . $diffMins . 'm';
                                                    } else {
                                                        $differenceFormatted = $sign . $diffHours . 'h';
                                                    }
                                                } else {
                                                    $differenceFormatted = $sign . $absMinutes . 'm';
                                                }
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        @if (\Auth::user()->type != 'employee')
                                            <td>{{ !empty($attendance->employee) ? $attendance->employee->name : '' }}</td>
                                        @endif
                                        <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                        <td>{{ $attendance->status }}</td>
                                        <td>{{ $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00' }}
                                        </td>
                                        <td>{{ $attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00' }}
                                        </td>
                                        <td>{{ $totalHoursFormatted }}</td>
                                        <td>{{ $differenceFormatted }}</td>
                                        @if (Gate::check('Edit Attendance') || Gate::check('Delete Attendance'))
                                            <td class="Action">
                                                <div class="d-flex align-items-center justify-content-start">
                                                    @can('Edit Attendance')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="btn btn-sm d-flex align-items-center justify-content-center"
                                                                data-size="lg"
                                                                data-url="{{ URL::to('attendanceemployee/' . $attendance->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Attendance') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan


                                                    @can('Delete Attendance')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['attendanceemployee.destroy', $attendance->id],
                                                                'id' => 'delete-form-' . $attendance->id,
                                                                'class' => 'd-inline-block',
                                                                'style' => 'margin: 0;'
                                                            ]) !!}
                                                            <a href="#"
                                                                class="btn btn-sm d-flex align-items-center justify-content-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </div>
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
@endsection