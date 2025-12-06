@extends('layouts.admin')

@section('page-title')
    {{ __('Attendance Calendar') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Attendance Calendar') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('Attendance Calendar') }}</h5>
                        </div>
                        @if (\Auth::user()->type != 'employee')
                            <div class="col-md-6">
                                <form method="GET" action="{{ route('attendance.calendar') }}" class="d-flex">
                                    <select name="employee_id" class="form-select me-2" onchange="this.form.submit()">
                                        <option value="">{{ __('Select Employee') }}</option>
                                        @foreach($allEmployees as $employee)
                                            <option value="{{ $employee->id }}" {{ $selectedEmployee && $selectedEmployee->id == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        @endif
                    </div>
                    @if($selectedEmployee)
                        <div class="d-flex mt-2">
                            <div class="me-3">
                                <span class="badge bg-success me-2">Present</span>
                                <span class="badge bg-danger me-2">Absent</span>
                                <span class="badge bg-warning me-2">Leave</span>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($selectedEmployee)
                        <div id='calendar'></div>
                    @else
                        <div class="alert alert-info">
                            @if(\Auth::user()->type == 'employee')
                                {{ __('No employee record found for your account.') }}
                            @else
                                {{ __('Please select an employee to view attendance calendar') }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        /* Your existing styles remain the same */
    </style>
@endpush

@push('script-page')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            
            @if($selectedEmployee)
                var attendanceData = @json($attendanceData);
                
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,dayGridWeek,dayGridDay'
                    },
                    timeZone: 'UTC',
                    dayCellDidMount: function(info) {
                        var dateStr = info.date.toISOString().split('T')[0];
                        
                        // Check if we have data for the selected employee
                        if (attendanceData['{{ $selectedEmployee->id }}'] && 
                            attendanceData['{{ $selectedEmployee->id }}'].data[dateStr]) {
                            
                            var status = attendanceData['{{ $selectedEmployee->id }}'].data[dateStr].type;
                            var statusClass = status.replace('_', '-');
                            
                            info.el.classList.add(statusClass);
                            
                            var detailsEl = document.createElement('div');
                            detailsEl.className = 'status-details';
                            
                            if (status === 'present') {
                                detailsEl.innerHTML = `<span style="background-color: #3490dc; color: white; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                                    Present</span><br>` + 
                                    (attendanceData['{{ $selectedEmployee->id }}'].data[dateStr].clock_in ? 'In: ' + attendanceData['{{ $selectedEmployee->id }}'].data[dateStr].clock_in : '') + 
                                    (attendanceData['{{ $selectedEmployee->id }}'].data[dateStr].clock_out ? '<br>Out: ' + attendanceData['{{ $selectedEmployee->id }}'].data[dateStr].clock_out : '');
                            } else if (status === 'absent') {
                                detailsEl.innerHTML = `<span style="background-color: #e3342f; color: white; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                                    Absent</span>`;
                            } else if (status === 'leave') {
                                detailsEl.innerHTML = `<span style="background-color: #f6993f; color: white; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                                    Leave</span><br>` + 
                                    (attendanceData['{{ $selectedEmployee->id }}'].data[dateStr].reason ? attendanceData['{{ $selectedEmployee->id }}'].data[dateStr].reason : '');
                            }
                            
                            info.el.querySelector('.fc-daygrid-day-frame').appendChild(detailsEl);
                        }
                    }
                });
                
                calendar.render();
            @endif
        });
    </script>
@endpush