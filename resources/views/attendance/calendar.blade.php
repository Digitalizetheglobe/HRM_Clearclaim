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
        .status-details {
            padding: 4px;
            font-size: 11px;
            line-height: 1.4;
        }
        
        .status-details .badge {
            margin-bottom: 4px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .status-details .time-info {
            margin-top: 2px;
            color: #333;
        }
        
        .fc-daygrid-day-frame {
            min-height: 100px;
        }
        
        .fc-daygrid-day {
            overflow: visible;
        }
        
        .fc-daygrid-day-top {
            position: relative;
        }
        
        .fc-daygrid-day.hours-complete {
            background-color: #d4edda !important;
        }
        
        .fc-daygrid-day.hours-incomplete {
            background-color: #f8d7da !important;
        }
    </style>
@endpush

@push('script-page')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            
            // Function to convert 24-hour time to 12-hour format with AM/PM
            // Returns "0:00" if time is missing or invalid
            function formatTime12Hour(timeStr) {
                if (!timeStr || timeStr === '00:00:00' || timeStr === '00:00') {
                    return '0:00';
                }
                
                // Handle both HH:MM:SS and HH:MM formats
                var parts = timeStr.split(':');
                var hours = parseInt(parts[0]);
                var minutes = parseInt(parts[1]);
                
                if (isNaN(hours) || isNaN(minutes)) {
                    return '0:00';
                }
                
                var period = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; // 0 should be 12
                
                var minutesStr = minutes < 10 ? '0' + minutes : minutes;
                
                return hours + ':' + minutesStr + ' ' + period;
            }
            
            // Function to calculate total time between clock_in and clock_out
            // Returns "No Punch Out" if clock_out is missing
            function calculateTotalTime(clockIn, clockOut) {
                if (!clockOut || clockOut === '00:00:00' || clockOut === '00:00') {
                    return 'No Punch Out';
                }
                
                if (!clockIn || clockIn === '00:00:00' || clockIn === '00:00') {
                    return 'No Punch Out';
                }
                
                // Parse times
                var inParts = clockIn.split(':');
                var outParts = clockOut.split(':');
                
                var inHours = parseInt(inParts[0]);
                var inMinutes = parseInt(inParts[1]);
                var outHours = parseInt(outParts[0]);
                var outMinutes = parseInt(outParts[1]);
                
                if (isNaN(inHours) || isNaN(inMinutes) || isNaN(outHours) || isNaN(outMinutes)) {
                    return 'No Punch Out';
                }
                
                // Convert to minutes for easier calculation
                var inTotalMinutes = inHours * 60 + inMinutes;
                var outTotalMinutes = outHours * 60 + outMinutes;
                
                // Calculate difference
                var diffMinutes = outTotalMinutes - inTotalMinutes;
                
                if (diffMinutes < 0) {
                    // Handle overnight shift (clock out next day)
                    diffMinutes = (24 * 60) - inTotalMinutes + outTotalMinutes;
                }
                
                var hours = Math.floor(diffMinutes / 60);
                var minutes = diffMinutes % 60;
                
                return hours + 'h ' + minutes + 'm';
            }
            
            // Function to calculate total hours worked in decimal format
            // Returns 0 if clock_out is missing or invalid
            function calculateTotalHours(clockIn, clockOut) {
                if (!clockOut || clockOut === '00:00:00' || clockOut === '00:00') {
                    return 0;
                }
                
                if (!clockIn || clockIn === '00:00:00' || clockIn === '00:00') {
                    return 0;
                }
                
                // Parse times
                var inParts = clockIn.split(':');
                var outParts = clockOut.split(':');
                
                var inHours = parseInt(inParts[0]);
                var inMinutes = parseInt(inParts[1]);
                var outHours = parseInt(outParts[0]);
                var outMinutes = parseInt(outParts[1]);
                
                if (isNaN(inHours) || isNaN(inMinutes) || isNaN(outHours) || isNaN(outMinutes)) {
                    return 0;
                }
                
                // Convert to minutes for easier calculation
                var inTotalMinutes = inHours * 60 + inMinutes;
                var outTotalMinutes = outHours * 60 + outMinutes;
                
                // Calculate difference
                var diffMinutes = outTotalMinutes - inTotalMinutes;
                
                if (diffMinutes < 0) {
                    // Handle overnight shift (clock out next day)
                    diffMinutes = (24 * 60) - inTotalMinutes + outTotalMinutes;
                }
                
                // Convert to hours (decimal format)
                var totalHours = diffMinutes / 60;
                
                return totalHours;
            }
            
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
                                var clockIn = attendanceData['{{ $selectedEmployee->id }}'].data[dateStr].clock_in;
                                var clockOut = attendanceData['{{ $selectedEmployee->id }}'].data[dateStr].clock_out;
                                
                                var formattedIn = formatTime12Hour(clockIn);
                                var formattedOut = formatTime12Hour(clockOut);
                                var totalTime = calculateTotalTime(clockIn, clockOut);
                                
                                // Calculate total hours worked
                                var totalHours = calculateTotalHours(clockIn, clockOut);
                                
                                // Apply green or red background based on 9 hours completion
                                if (totalHours >= 9) {
                                    info.el.classList.add('hours-complete');
                                } else {
                                    info.el.classList.add('hours-incomplete');
                                }
                                
                                var html = `<span class="badge" style="background-color: #3490dc; color: white; padding: 4px 8px; border-radius: 4px; display: inline-block; font-size: 15px; font-weight: 600;">
                                    Present</span>
                                    <div class="time-info ">
                                        In: ${formattedIn}<br>
                                        Out: ${formattedOut}<br>
                                        Total: ${totalTime}
                                    </div>`;
                                
                                detailsEl.innerHTML = html;
                            } else if (status === 'absent') {
                                detailsEl.innerHTML = `<span class="badge" style="background-color: #e3342f; color: white; padding: 4px 8px; border-radius: 4px; display: inline-block; font-size: 15px; font-weight: 600;">
                                    Absent</span>`;
                            } else if (status === 'leave') {
                                detailsEl.innerHTML = `<span class="badge" style="background-color: #f6993f; color: white; padding: 4px 8px; border-radius: 4px; display: inline-block; font-size: 15px; font-weight: 600;">
                                    Leave</span>`;
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