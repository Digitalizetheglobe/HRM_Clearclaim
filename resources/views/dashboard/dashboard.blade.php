@extends('layouts.admin')


@section('content')
<style>
    .fc-prev-button, .fc-next-button {
        padding: 5px 8px !important;
        font-size: 14px !important;
        background-color: #007bff !important;
        border-radius: 5px !important;
        border: none !important;
        color: white !important;
    }

    .fc-prev-button:hover, .fc-next-button:hover {
        background-color: #0056b3 !important;
    }

    #calendar {
        margin-bottom: 10px;
    }

    .calendar-navigation {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
    }
    
    .fc-daygrid-day.hours-complete {
        background-color: #d4edda !important;
    }
    
    .fc-daygrid-day.hours-incomplete {
        background-color: #f8d7da !important;
    }
</style>

<div>
    <div class="row">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if (\Auth::user()->type == 'employee')
            <!-- Welcome Section -->
            <div class="col-12 mb-">
                <div class="row align-items-top">
                    <div class="col-md-8">
                        @php
                            $hour = date('H');
                            if ($hour < 12) {
                                $greeting = 'Good morning';
                            } elseif ($hour < 17) {
                                $greeting = 'Good afternoon';
                            } else {
                                $greeting = 'Good evening';
                            }
                            // Get only first name
                            $firstName = explode(' ', $emp->name)[0];
                        @endphp
                        <h2 class="mb-2" style="font-size: 25px; font-weight: bold; color:rgb(0, 190, 92);">{{ $greeting }}, {{ $firstName }}!</h2>
                        <p class="mb-0" style="font-size: 16px; color: #6b7280;">
                            You have {{ $pendingLeaveCount ?? 0 }} leave request{{ $pendingLeaveCount != 1 ? 's' : '' }} pending.
                        </p>
                    </div>
                    <div class="col-md-4 d-flex justify-content-end">
                        <div class="card" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background-color: #f8f9fa; min-width: 280px;">
                            <div class="card-body p-3">
                                    <div>
                                        <p class="mb-1" style="font-size: 14px; color: #6b7280; margin: 0;">Current time</p>
                                    <p class="mb-0" id="currentDateTime" style="font-size: 18px; font-weight: bold; color: #374151; margin: 0;">{{ \Carbon\Carbon::now()->format('M d, Y, h:i:s A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-9">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card">  
                                    <div class="card-header d-flex align-items-center">
                                        <img src="{{ asset('storage/uploads/avatar/' . ($emp->user->avatar ?? 'default-avatar.png')) }}" 
                                            alt="Profile Image" 
                                            class="rounded-circle me-4" 
                                            width="60" 
                                            height="60">
                                        <div>
                                            <h4 class="mb-0" style="color:black;">{{ $emp->name }}</h4>
                                            <small style="font-size: 12px; color:black;">{{ $emp->department->name ?? 'No Department' }} Team</small><small style="font-size:16px; color:black;"> &nbsp{{ $emp->designation->name ?? 'No Designation' }}&nbsp</small><br>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Phone Number:<br></strong> {{ $emp->phone ?? 'N/A' }}</p><br>
                                        <p><strong>Email Address:<br></strong> {{ $emp->email ?? 'N/A' }}</p><br>
                                        <p><strong>Joined On:<br></strong> {{ \Carbon\Carbon::parse($emp->company_doj)->format('d M Y') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Combined Attendance Card -->
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 style="font-size:20px;color:black; margin: 0;">{{ __('Attendance Overview') }}</h5>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="attendanceFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span id="selectedFilterText">Today</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="attendanceFilterDropdown">
                                                <li><a class="dropdown-item attendance-filter-option active" href="javascript:void(0)" data-filter="today">
                                                    <i class="fas fa-calendar-day me-2"></i>Today
                                                </a></li>
                                                <li><a class="dropdown-item attendance-filter-option" href="javascript:void(0)" data-filter="date">
                                                    <i class="fas fa-calendar me-2"></i>Select Date
                                                </a></li>
                                                <li><a class="dropdown-item attendance-filter-option" href="javascript:void(0)" data-filter="weekly">
                                                    <i class="fas fa-calendar-week me-2"></i>Weekly
                                                </a></li>
                                                <li><a class="dropdown-item attendance-filter-option" href="javascript:void(0)" data-filter="monthly">
                                                    <i class="fas fa-calendar-alt me-2"></i>Monthly
                                                </a></li>
                                            </ul>
                                            <input type="date" id="attendanceDatePicker" style="position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none;" onchange="handleDateSelect(this.value)" oninput="handleDateSelect(this.value)">
                                            <input type="month" id="attendanceMonthPicker" style="position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none;" onchange="handleMonthSelect(this.value)" oninput="handleMonthSelect(this.value)">
                                            </div>

                                            
                                    </div>
                                    <div class="card-body">
                                        <!-- Punch In/Out Section -->
                                        <div id="punchInOutSection" class="text-center mb-4 pb-3 border-bottom">
                                            <p id="attendanceStatus" class="font-bold mb-3">
                                                    @if (!isset($employeeAttendance) || !$employeeAttendance->clock_in)
                                                        <span class="text-primary"><i class="fas fa-fingerprint"></i> Not Punched In</span>
                                                    @elseif ($employeeAttendance->clock_out == '00:00:00' || !$employeeAttendance->clock_out)
                                                        @php
                                                            $companyStartTime = \Utility::getValByName('company_start_time');
                                                            $isLate = false;
                                                            if ($companyStartTime && $employeeAttendance->clock_in) {
                                                                $clockInTime = \Carbon\Carbon::parse($employeeAttendance->date . ' ' . $employeeAttendance->clock_in);
                                                                $expectedStartTime = \Carbon\Carbon::parse($employeeAttendance->date . ' ' . $companyStartTime);
                                                                $isLate = $clockInTime->gt($expectedStartTime);
                                                            }
                                                        @endphp
                                                        <span class="{{ $isLate ? 'text-danger' : 'text-success' }}"><i class="fas fa-fingerprint"></i> Punched In at {{ \Carbon\Carbon::parse($employeeAttendance->clock_in)->format('h:i A') }}</span>
                                                    @else
                                                        <span class="text-danger"><i class="fas fa-sign-out-alt"></i> Punched Out at {{ \Carbon\Carbon::parse($employeeAttendance->clock_out)->format('h:i A') }}</span>
                                                    @endif
                                                </p>

                                                {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post', 'id' => 'attendanceForm']) }}
                                                    @if (empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                                    <button type="submit" value="0" name="in" id="clock_in" class="btn btn-primary btn-lg">{{ __('Punch In') }}</button>
                                                    @else
                                                    <button type="button" value="1" name="out" id="clock_out" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#confirmClockOutModal">
                                                            {{ __('Punch Out') }}
                                                        </button>
                                                    @endif
                                                {{ Form::close() }}
                                    </div>

                                        <!-- Attendance Overview Content -->
                                        <!-- Loading state -->
                                        <div class="text-center py-4" id="attendanceLoading" style="display: none;">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                        <div id="attendanceOverviewContent">
                                            <!-- Content will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header card-body table-border-style d-flex justify-content-between align-items-center">
                                        <h5 style="font-size:20px; color:black; margin: 0;">{{ __('Notices') }}</h5>
                                    </div>
                                    <div class="card-body" style="height: 325px; overflow: auto; padding: 10px; padding-top:25px;">
                                        <div class="table-responsive" style="max-width:452px;">
                                            <table class="table table-bordered text-center">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 60%;">Title</th>
                                                        <th style="width: 40%;">Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($notices as $notice)
                                                    <tr>
                                                        <td style="word-wrap: break-word; white-space: normal;">
                                                            {{ Str::limit($notice->title, 50, '...') }}
                                                        </td>
                                                        <td>
                                                            {{ \Carbon\Carbon::parse($notice->notice_startdate)->format('d M Y') }} - 
                                                            {{ \Carbon\Carbon::parse($notice->notice_enddate)->format('d M Y') }}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header card-body table-border-style">
                                        <h5 style="font-size:20px;color:black">{{ __('TO-DO Lists') }}</h5>
                                    </div>
                                    <div class="card-body" style="height: 324px; overflow:auto;">
                                        <div class="table-responsive"> 
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                    <th>{{ __('Task Title') }}</th>
                                                    <th>{{ __('Priority') }}</th>
                                                    <th>{{ __('Due Date') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="list">
                                                    @foreach ($todos as $todo)
                                                        <tr>
                                                            <td>{{ $todo->task }}</td>
                                                            <td>
                                                                @if($todo->priority == 1)
                                                                    <span class="badge bg-danger">{{ __('High') }}</span>
                                                                @elseif($todo->priority == 2)
                                                                    <span class="badge bg-success">{{ __('Medium') }}</span>
                                                                @else
                                                                    <span class="badge bg-success">{{ __('Low') }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ \Carbon\Carbon::parse($todo->expires_at)->format('d M Y') }}</td>
                                                            <td>
                                                                @if($todo->is_completed)
                                                                    <span class="badge bg-success">{{ __('Completed') }}</span>
                                                                @else
                                                                    <span class="badge bg-danger">{{ __('Pending') }}</span>
                                                                @endif
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
                    </div>

                    <div class="col-xl-12">
                        <div class="card">
                                    <div class="card-header card-body table-border-style d-flex flex-wrap justify-content-between align-items-center">
                                        <h5 class="mb-2 mb-md-0" style="font-size:20px; color:black; margin: 0;">
                                            {{ __('Employees On Leave') }}
                                        </h5>
                                    </div>
                                    <div class="card-body" style="height: 300px; overflow: auto; padding-top:25px;">
                                        <div class="table-responsive">
                                            <table class="table table-bordered text-center" id="onLeaveTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Employee Name') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($employeesNotWorkingToday as $employee)
                                                        <tr>
                                                            <td>{{ $employee['employee_name'] }}</td>
                                                            <td>{{ $employee['status'] }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="2">All employees are working today</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header card-body table-border-style">
                                <h5 style="font-size:20px;color:black">{{ __('Meeting List') }}</h5>
                            </div>
                            <div class="card-body" style="height: 324px; overflow:auto;">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Meeting Title') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Time') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($meetings as $meeting)
                                                <tr>
                                                    <td>{{ $meeting->title }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                                    <td>{{ \Auth::user()->timeFormat($meeting->time) }}</td>
                                                </tr>
                                            @endforeach

                                            @if ($meetings->isEmpty())
                                                <tr>
                                                    <td colspan="3" class="text-center">{{ __('No meetings found') }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side Calendar -->
            <div class="col-xxl-3" style="z-index: 0;">
                <div class="d-flex flex-column gap-2 sticky-top" style="">
                    <div class="card flex-grow-1" style="height: 250px;">
                        <div class="card-header">
                            <h5 style="font-size:20px;color:black">{{ __("Upcoming Events This Month") }}</h5>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            @if(count($allEvents) > 0)
                                <div class="list-group">
                                    @foreach($allEvents as $event)
                                        @php
                                            // Extract first name from title (e.g., "Dipali Sevakram Tayade's Anniversary" -> "Dipali's Anniversary")
                                            $title = $event['title'];
                                            if (strpos($title, "'s") !== false) {
                                                $namePart = explode("'s", $title)[0];
                                                $eventType = explode("'s", $title)[1] ?? '';
                                                $firstName = explode(' ', $namePart)[0];
                                                $title = $firstName . "'s" . $eventType;
                                            }
                                        @endphp
                                        <span class="list-group-item list-group-item-action flex-column align-items-start">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">{{ $title }}</h6>
                                                <small>{{ \Carbon\Carbon::parse($event['start'])->format('D, M d') }}</small>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    @if($event['type'] == 'birthday')
                                                        <span class="badge bg-success">Birthday</span>
                                                    @elseif($event['type'] == 'anniversary')
                                                        <span class="badge bg-primary">Work Anniversary</span>
                                                    @else
                                                        <span class="badge bg-info">Event</span>
                                                    @endif
                                                </small>
                                                @if(\Carbon\Carbon::parse($event['start'])->isToday())
                                                    <span class="badge bg-success">Today</span>
                                                @endif
                                            </div>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center p-3">
                                    <p>No upcoming events this month</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card flex-grow-1">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h5>{{ __('Calendar') }}</h5>
                                    <!-- <input type="hidden" id="path_admin" value="{{ url('/') }}"> -->
                                </div>
                                
                            </div>
                        </div>
                        <div class="card-body" style="padding-top:0px;">
                            <div id='calendar' class='calendar'></div>
                        </div>
                    </div>


                </div>
            </div>
        @endif
    </div>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="confirmClockOutModal" tabindex="-1" aria-labelledby="confirmClockOutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmClockOutModalLabel">Confirm Clock Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to clock out?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmClockOutBtn">Yes, Clock Out</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
     <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>

    @if (Auth::user()->type == 'employee')
    <script type="text/javascript">
    $(document).ready(function() {
        get_data();
    });

    // Function to calculate total hours worked in decimal format
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

    function get_data() {
        var calender_type = $('#calender_type :selected').val();

        $('#calendar').removeClass('local_calender google_calender');
        if (!calender_type) {
            calender_type = 'local_calender';
        }
        $('#calendar').addClass(calender_type);

        $.ajax({
            data: {
                "_token": "{{ csrf_token() }}",
                'calender_type': calender_type
            },
            success: function(data) {
                @if(isset($emp) && isset($attendanceData))
                    var attendanceData = @json($attendanceData);
                    var employeeId = {{ $emp->id }};
                @else
                    var attendanceData = {};
                    var employeeId = null;
                @endif
                
                var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                    headerToolbar: {
                        left: 'prev',
                        center: 'title',
                        right: 'next'
                    },
                    themeSystem: 'bootstrap',
                    slotDuration: '00:10:00',
                    allDaySlot: true,
                    navLinks: false,
                    droppable: true,
                    selectable: true,
                    selectMirror: true,
                    editable: true,
                    dayMaxEvents: true,
                    handleWindowResize: true,
                    height: '360px',
                    dayCellDidMount: function(info) {
                        // Skip Sundays - don't display anything for Sundays
                        var dayOfWeek = info.date.getDay();
                        if (dayOfWeek === 0) { // Sunday
                            return;
                        }
                        
                        if (employeeId && attendanceData[employeeId] && attendanceData[employeeId].data) {
                            var dateStr = info.date.toISOString().split('T')[0];
                            var dayData = attendanceData[employeeId].data[dateStr];
                            
                            if (dayData) {
                                if (dayData.type === 'present') {
                                    var clockIn = dayData.clock_in;
                                    var clockOut = dayData.clock_out;
                                    
                                    // Check if no punch-out
                                    if (!clockOut || clockOut === '00:00:00' || clockOut === '00:00') {
                                        // Red for no punch-out
                                        info.el.classList.add('hours-incomplete');
                                    } else {
                                        // Calculate total hours worked
                                        var totalHours = calculateTotalHours(clockIn, clockOut);
                                        
                                        // Apply green or red background based on 9 hours completion
                                        if (totalHours >= 9) {
                                            info.el.classList.add('hours-complete');
                                        } else {
                                            info.el.classList.add('hours-incomplete');
                                        }
                                    }
                                } else if (dayData.type === 'absent') {
                                    // Dark red for absent
                                    info.el.classList.add('hours-absent');
                                }
                                // Leave days are not colored (default appearance)
                            }
                        }
                    }
                });
                calendar.render();
            }
        });
    }
    </script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let clockInButton = document.getElementById("clock_in");
            let clockOutButton = document.getElementById("clock_out");
            let currentTimeElement = document.getElementById("currentDateTime");
            let confirmClockOutBtn = document.getElementById("confirmClockOutBtn");
            let attendanceStatus = document.getElementById("attendanceStatus");
            let attendanceForm = document.getElementById('attendanceForm');

            // Update current time display
            function updateTimeDisplay() {
                let now = new Date();
                currentTimeElement.textContent = now.toLocaleString("en-US", {
                    hour: "2-digit", minute: "2-digit", second: "2-digit", 
                    hour12: true, day: "2-digit", month: "short", year: "numeric"
                });
            }

            // Clock In Button - AJAX submission for instant feedback
            if (clockInButton) {
                clockInButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    
                    // Disable button and show processing
                    this.disabled = true;
                    let originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    
                    // Update status immediately
                    attendanceStatus.innerHTML = '<span class="text-warning"><i class="fas fa-spinner fa-spin"></i> Processing Punch In...</span>';
                    
                    // Perform AJAX request
                    fetch(attendanceForm.action, {
                        method: 'POST',
                        body: new FormData(attendanceForm),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Get current time
                            let now = new Date();
                            let timeString = now.toLocaleString("en-US", {
                                hour: "2-digit", 
                                minute: "2-digit", 
                                hour12: true
                            });
                            
                            // Update status to show success
                            attendanceStatus.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
                            
                            // Update the UI to show punch-out button
                            setTimeout(function() {
                                attendanceStatus.innerHTML = '<span class="text-success"><i class="fas fa-fingerprint"></i> Punched In at ' + timeString + '</span>';
                                
                                // Replace button with punch out button
                                attendanceForm.innerHTML = '<button type="button" value="1" name="out" id="clock_out" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmClockOutModal">{{ __("Punch Out") }}</button>';
                                
                                // Reattach event listener for new clock out button
                                attachClockOutListener();
                            }, 1500);
                        } else {
                            // Show error
                            attendanceStatus.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</span>';
                            clockInButton.disabled = false;
                            clockInButton.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        attendanceStatus.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.</span>';
                        clockInButton.disabled = false;
                        clockInButton.innerHTML = originalText;
                    });
                });
            }

            // Function to attach clock out listener
            function attachClockOutListener() {
                let newClockOutButton = document.getElementById("clock_out");
                if (newClockOutButton) {
                    newClockOutButton.addEventListener("click", function() {
                        // Show modal (Bootstrap handles this)
                        let confirmBtn = document.getElementById("confirmClockOutBtn");
                        if (confirmBtn) {
                            confirmBtn.onclick = handleClockOut;
                        }
                    });
                }
            }

            // Clock Out Button - AJAX submission via modal confirmation
            function handleClockOut() {
                // Update UI immediately
                attendanceStatus.innerHTML = '<span class="text-warning"><i class="fas fa-spinner fa-spin"></i> Processing Punch Out...</span>';
                
                // Close the modal
                let modal = bootstrap.Modal.getInstance(document.getElementById('confirmClockOutModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Perform AJAX request
                fetch(attendanceForm.action, {
                    method: 'POST',
                    body: new FormData(attendanceForm),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Get current time
                        let now = new Date();
                        let timeString = now.toLocaleString("en-US", {
                            hour: "2-digit", 
                            minute: "2-digit", 
                            hour12: true
                        });
                        
                        // Update status to show success
                        attendanceStatus.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
                        
                        // Update the UI to show punch-in button
                        setTimeout(function() {
                            attendanceStatus.innerHTML = '<span class="text-danger"><i class="fas fa-sign-out-alt"></i> Punched Out at ' + timeString + '</span>';
                            
                            // Replace button with punch in button for next day
                            attendanceForm.innerHTML = '<button type="submit" value="0" name="in" id="clock_in" class="btn btn-primary" disabled>{{ __("Punch In") }}</button>';
                        }, 1500);
                    } else {
                        attendanceStatus.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</span>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    attendanceStatus.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.</span>';
                });
            }

            // Initial setup for existing clock out button
            if (confirmClockOutBtn) {
                confirmClockOutBtn.addEventListener("click", handleClockOut);
            }

            // Initialize time display and update every second
            updateTimeDisplay();
            setInterval(updateTimeDisplay, 1000);

            // Welcome section time display
            function updateWelcomeTimeDisplay() {
                let now = new Date();
                let welcomeTimeElement = document.getElementById("currentDateTime");
                if (welcomeTimeElement) {
                    let day = now.getDate();
                    let monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];
                    let month = monthNames[now.getMonth()];
                    let year = now.getFullYear();
                    let hours = now.getHours();
                    let minutes = now.getMinutes();
                    let seconds = now.getSeconds();
                    let ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12;
                    hours = hours ? hours : 12;
                    minutes = minutes < 10 ? '0' + minutes : minutes;
                    seconds = seconds < 10 ? '0' + seconds : seconds;
                    welcomeTimeElement.textContent = `${day} ${month} ${year}, ${hours}:${minutes}:${seconds} ${ampm}`;
                }
            }

            // Refresh time button
            let refreshTimeBtn = document.getElementById("refreshTimeBtn");
            if (refreshTimeBtn) {
                refreshTimeBtn.addEventListener("click", function() {
                    let icon = this.querySelector("i");
                    icon.classList.add("fa-spin");
                    updateWelcomeTimeDisplay();
                    setTimeout(() => {
                        icon.classList.remove("fa-spin");
                    }, 500);
                });
            }

            // Initialize welcome time display and update every second
            updateWelcomeTimeDisplay();
            setInterval(updateWelcomeTimeDisplay, 1000);

            // Attendance Overview functionality
            initializeAttendanceOverview();
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', function() {
                stopRealTimeUpdates();
            });
        });

// --- Improved Attendance Overview JS (replace previous versions) ---
/* ---------- Enhanced Attendance Overview JS (paste into your Blade) ---------- */

let attendanceWeekOffset = 0; // 0 = week of selected date or current week

function initializeAttendanceOverview() {
    // Add event listeners for filter options
    document.querySelectorAll('.attendance-filter-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const filterType = this.dataset.filter;

            if (filterType === 'date' || filterType === 'monthly') {
                const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('attendanceFilterDropdown'));
                if (dropdown) dropdown.hide();
                setTimeout(() => openPicker(filterType === 'date' ? 'date' : 'month'), 120);
                return false;
            }

            if (filterType === 'weekly') {
                const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('attendanceFilterDropdown'));
                if (dropdown) dropdown.hide();

                const datePicker = document.getElementById('attendanceDatePicker');
                // if selected date exists, use it; else use today
                const refDate = (datePicker && datePicker.value) ? datePicker.value : (new Date()).toISOString().split('T')[0];
                // reset weekOffset to 0 (start from selected reference)
                attendanceWeekOffset = 0;
                setSelectedFilterActive('weekly', 'Week of ' + formatShortDate(refDate));
                loadAttendanceData('weekly', refDate);
                return false;
            }

            // today
            setSelectedFilterActive(filterType);
            loadAttendanceData(filterType);
            return false;
        });
    });

    // pickers hooks
    const datePicker = document.getElementById('attendanceDatePicker');
    if (datePicker) {
        datePicker.addEventListener('change', function() {
            if (this.value) handleDateSelect(this.value);
        });
        datePicker.addEventListener('input', function() {
            if (this.value) handleDateSelect(this.value);
        });
    }
    const monthPicker = document.getElementById('attendanceMonthPicker');
    if (monthPicker) {
        monthPicker.addEventListener('change', function() {
            if (this.value) handleMonthSelect(this.value);
        });
        monthPicker.addEventListener('input', function() {
            if (this.value) handleMonthSelect(this.value);
        });
    }

    // Add prev/next week button listeners (buttons HTML below)
    const prevWeekBtn = document.getElementById('prevWeekBtn');
    const nextWeekBtn = document.getElementById('nextWeekBtn');
    if (prevWeekBtn) {
        prevWeekBtn.addEventListener('click', function() {
            adjustWeekOffset(-1);
        });
    }
    if (nextWeekBtn) {
        nextWeekBtn.addEventListener('click', function() {
            adjustWeekOffset(1);
        });
    }

    loadAttendanceData('today'); // default
}

function setSelectedFilterActive(filterType, labelText = null) {
    document.querySelectorAll('.attendance-filter-option').forEach(o => o.classList.remove('active'));
    const option = document.querySelector(`[data-filter="${filterType}"]`);
    if (option) option.classList.add('active');
    document.getElementById('selectedFilterText').textContent = labelText || (option ? option.textContent.trim() : filterType);
}

function formatShortDate(isoDate /* YYYY-MM-DD */) {
    try {
        const d = new Date(isoDate + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } catch (e) {
        return isoDate;
    }
}

function adjustWeekOffset(delta) {
    attendanceWeekOffset += delta; // negative -> previous weeks
    // compute reference date from datePicker value or today
    const datePicker = document.getElementById('attendanceDatePicker');
    let base = (datePicker && datePicker.value) ? new Date(datePicker.value + 'T00:00:00') : new Date();
    // shift by 7 * offset days
    const ref = new Date(base);
    ref.setDate(base.getDate() + (attendanceWeekOffset * 7));
    const isoRef = ref.toISOString().split('T')[0];
    setSelectedFilterActive('weekly', 'Week of ' + formatShortDate(isoRef));
    loadAttendanceData('weekly', isoRef);
}

function openPicker(type) {
    const datePicker = document.getElementById('attendanceDatePicker');
    const monthPicker = document.getElementById('attendanceMonthPicker');
    if (type === 'date' && datePicker) {
        datePicker.style.position = 'fixed'; datePicker.style.opacity = '0'; datePicker.style.pointerEvents = 'auto'; datePicker.style.zIndex = '9999';
        if (datePicker.showPicker && typeof datePicker.showPicker === 'function') {
            const pickerPromise = datePicker.showPicker();
            if (pickerPromise && typeof pickerPromise.catch === 'function') {
                pickerPromise.catch(() => datePicker.click()).finally(() => {
                    datePicker.style.position = 'absolute';
                    datePicker.style.opacity = '0';
                    datePicker.style.pointerEvents = 'none';
                    datePicker.style.zIndex = 'auto';
                });
            } else {
                datePicker.click();
                setTimeout(() => {
                    datePicker.style.position = 'absolute';
                    datePicker.style.opacity = '0';
                    datePicker.style.pointerEvents = 'none';
                    datePicker.style.zIndex = 'auto';
                }, 200);
            }
        } else {
            datePicker.click();
            setTimeout(() => {
                datePicker.style.position = 'absolute';
                datePicker.style.opacity = '0';
                datePicker.style.pointerEvents = 'none';
                datePicker.style.zIndex = 'auto';
            }, 200);
        }
    } else if (type === 'month' && monthPicker) {
        monthPicker.style.position = 'fixed'; monthPicker.style.opacity = '0'; monthPicker.style.pointerEvents = 'auto'; monthPicker.style.zIndex = '9999';
        if (monthPicker.showPicker && typeof monthPicker.showPicker === 'function') {
            const pickerPromise = monthPicker.showPicker();
            if (pickerPromise && typeof pickerPromise.catch === 'function') {
                pickerPromise.catch(() => monthPicker.click()).finally(() => {
                    monthPicker.style.position = 'absolute';
                    monthPicker.style.opacity = '0';
                    monthPicker.style.pointerEvents = 'none';
                    monthPicker.style.zIndex = 'auto';
                });
            } else {
                monthPicker.click();
                setTimeout(() => {
                    monthPicker.style.position = 'absolute';
                    monthPicker.style.opacity = '0';
                    monthPicker.style.pointerEvents = 'none';
                    monthPicker.style.zIndex = 'auto';
                }, 200);
            }
        } else {
            monthPicker.click();
            setTimeout(() => {
                monthPicker.style.position = 'absolute';
                monthPicker.style.opacity = '0';
                monthPicker.style.pointerEvents = 'none';
                monthPicker.style.zIndex = 'auto';
            }, 200);
        }
    }
}

function handleDateSelect(dateValue) {
    if (!dateValue) return;
    // when user picks a date we treat it as date filter (single day)
    setSelectedFilterActive('date', formatShortDate(dateValue));
    // reset week offset so prev/next operate from new base
    attendanceWeekOffset = 0;
    loadAttendanceData('date', dateValue);
}

function handleMonthSelect(monthValue) {
    if (!monthValue) return;
    setSelectedFilterActive('monthly', new Date(monthValue + '-01').toLocaleDateString('en-US',{ month:'long', year:'numeric' }));
    attendanceWeekOffset = 0;
    loadAttendanceData('monthly', monthValue);
}

function loadAttendanceData(filterType, dateValue = null) {
    // Stop real-time updates when loading new data
    stopRealTimeUpdates();
    
    const contentDiv = document.getElementById('attendanceOverviewContent');
    const loadingDiv = document.getElementById('attendanceLoading');
    const punchInOutSection = document.getElementById('punchInOutSection');
    
    // Check if elements exist before accessing
    if (!contentDiv) {
        console.error('[Attendance] attendanceOverviewContent element not found');
        return;
    }
    
    // Hide/show punch in/out section based on filter type
    // Check if selected date is today for date filter
    const datePicker = document.getElementById('attendanceDatePicker');
    const selectedDate = datePicker ? datePicker.value : null;
    const today = new Date().toISOString().split('T')[0];
    const isSelectedDateToday = selectedDate === today;
    
    if (punchInOutSection) {
        if (filterType === 'today' || (filterType === 'date' && isSelectedDateToday)) {
            punchInOutSection.style.display = 'block';
        } else {
            punchInOutSection.style.display = 'none';
        }
    }
    
    // Show loading indicator if it exists
    if (loadingDiv) {
        loadingDiv.style.display = 'block';
    }
    
    // Clear content
    contentDiv.innerHTML = '';

    const url = '{{ route("attendance.overview") }}';
    const payload = {
        _token: '{{ csrf_token() }}',
        filter_type: filterType,
        employee_id: {{ $emp->id ?? 0 }}
    };

    if (filterType === 'weekly') {
        if (dateValue) {
            payload.date = dateValue;
            currentWeekDate = dateValue;
        } else {
            const datePicker = document.getElementById('attendanceDatePicker');
            payload.date = (datePicker && datePicker.value) ? datePicker.value : (new Date()).toISOString().split('T')[0];
            currentWeekDate = payload.date;
        }
    } else if (filterType === 'date' && dateValue) {
        payload.date = dateValue;
    } else if (filterType === 'monthly' && dateValue) {
        payload.month = dateValue; // YYYY-MM
        currentMonth = dateValue;
    }

    console.log('[Attendance] Request payload:', payload);

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    })
    .then(r => {
        // Hide loading indicator if it exists
        if (loadingDiv) {
            loadingDiv.style.display = 'none';
        }
        if (!r.ok) throw new Error('Network error');
        return r.json();
    })
    .then(json => {
        console.log('[Attendance] Response:', json);
        if (!json.success) {
            contentDiv.innerHTML = '<div class="alert alert-warning">' + (json.message || 'No data') + '</div>';
            return;
        }

        // If weekly, update label with week range returned by server if available:
        if (filterType === 'weekly' && json.data && json.data.week_start && json.data.week_end) {
            const selectedFilterText = document.getElementById('selectedFilterText');
            if (selectedFilterText) {
                selectedFilterText.textContent = json.data.week_start + ' - ' + json.data.week_end;
            }
        }
        if (filterType === 'monthly' && json.data && json.data.month_name) {
            const selectedFilterText = document.getElementById('selectedFilterText');
            if (selectedFilterText) {
                selectedFilterText.textContent = json.data.month_name;
            }
        }

        renderAttendanceOverview(json.data, filterType);
        
        // Update attendance status text if late punch-in (for today view)
        if (filterType === 'today' && json.data.clock_in && json.data.is_late) {
            const attendanceStatus = document.getElementById('attendanceStatus');
            if (attendanceStatus) {
                const timeString = json.data.clock_in;
                attendanceStatus.innerHTML = '<span class="text-danger"><i class="fas fa-fingerprint"></i> Punched In at ' + timeString + '</span>';
            }
        }
    })
    .catch(err => {
        // Hide loading indicator if it exists
        if (loadingDiv) {
            loadingDiv.style.display = 'none';
        }
        if (contentDiv) {
            contentDiv.innerHTML = '<div class="alert alert-danger">Error loading attendance data.</div>';
        }
        console.error('[Attendance] fetch error:', err);
    });
}

/* Call initializeAttendanceOverview() after DOM ready (you already do that) */

// Global variables for real-time updates
let attendanceUpdateInterval = null;
let currentAttendanceData = null;
let currentFilterType = null;

// Function to stop real-time updates
function stopRealTimeUpdates() {
    if (attendanceUpdateInterval) {
        clearInterval(attendanceUpdateInterval);
        attendanceUpdateInterval = null;
    }
}

// Function to check if viewing current period
function isCurrentPeriod(filterType, data) {
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    
    if (filterType === 'today') {
        return true;
    }
    
    if (filterType === 'date') {
        const datePicker = document.getElementById('attendanceDatePicker');
        const selectedDate = datePicker ? datePicker.value : null;
        return selectedDate === todayStr;
    }
    
    if (filterType === 'weekly') {
        // Check if current week
        const weekStart = data.week_start ? new Date(data.week_start) : null;
        const weekEnd = data.week_end ? new Date(data.week_end) : null;
        if (weekStart && weekEnd) {
            return today >= weekStart && today <= weekEnd;
        }
    }
    
    if (filterType === 'monthly') {
        // Check if current month
        const monthName = data.month_name || '';
        const currentMonthName = today.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        return monthName === currentMonthName;
    }
    
    return false;
}

// Function to calculate current hours for today
function calculateCurrentHours(data) {
    if (!data.clock_in || data.clock_in === 'N/A') {
        return data.hours_completed || 0;
    }
    
    // If already clocked out, return the completed hours
    if (data.clock_out && data.clock_out !== 'N/A' && data.clock_out !== '00:00:00') {
        return data.hours_completed || 0;
    }
    
    // Use server-calculated hours if available (more accurate)
    // Only recalculate if we need real-time updates and the server value seems stale
    if (data.hours_completed !== undefined && data.hours_completed !== null) {
        // For real-time calculation, add the difference from last server update
        // But cap it at a reasonable maximum (e.g., 24 hours for a single day)
        const maxHoursForDay = 24;
        const serverHours = data.hours_completed || 0;
        
        // If server hours are already calculated and reasonable, use them
        // Only do real-time calculation if it's today and we want live updates
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        
        // Calculate from clock_in to now, using the actual attendance date
        try {
            let clockInTime;
            let clockInDateStr = todayStr; // Default to today
            
            // Use the attendance date from server if available
            if (data.date) {
                clockInDateStr = data.date;
            }
            
            // If we have raw clock_in time (24-hour format), use it for more accurate calculation
            if (data.clock_in_raw) {
                // Use raw time in format "HH:MM:SS" or "HH:MM"
                const timeStr = data.clock_in_raw.length >= 5 ? data.clock_in_raw.substring(0, 5) : data.clock_in_raw;
                clockInTime = new Date(clockInDateStr + 'T' + timeStr + ':00');
            } else if (data.clock_in.includes('AM') || data.clock_in.includes('PM')) {
                // Handle 12-hour format conversion (e.g., "09:30 AM" or "12:19 AM")
                const timeParts = data.clock_in.match(/(\d+):(\d+)\s*(AM|PM)/i);
                if (timeParts) {
                    let hours = parseInt(timeParts[1]);
                    const minutes = parseInt(timeParts[2]);
                    const ampm = timeParts[3].toUpperCase();
                    
                    if (ampm === 'PM' && hours !== 12) hours += 12;
                    if (ampm === 'AM' && hours === 12) hours = 0;
                    
                    clockInTime = new Date(clockInDateStr + 'T' + 
                        String(hours).padStart(2, '0') + ':' + 
                        String(minutes).padStart(2, '0') + ':00');
                } else {
                    return Math.min(serverHours, maxHoursForDay);
                }
            } else {
                // Handle 24-hour format (e.g., "09:30:00" or "09:30")
                clockInTime = new Date(clockInDateStr + 'T' + data.clock_in);
            }
            
            const now = new Date();
            const diffMs = now - clockInTime;
            const diffHours = diffMs / (1000 * 60 * 60);
            
            // Cap at maximum reasonable hours for a single day (24 hours)
            // Also, if the calculated hours are way more than server hours, 
            // it might be a date mismatch - use server hours instead
            const calculatedHours = Math.max(0, diffHours);
            
            // If calculated hours are more than 24, it's likely a date issue
            // Use server hours instead, or cap at 24
            if (calculatedHours > maxHoursForDay) {
                // Likely clocked in yesterday - use server hours or cap
                return Math.min(serverHours, maxHoursForDay);
            }
            
            // Use the calculated hours, but don't let it exceed server hours by too much
            // (server hours are more accurate as they use the correct attendance date)
            if (calculatedHours > serverHours + 0.5) {
                // If calculated is significantly more than server, there's a date mismatch
                // Use server hours
                return Math.min(serverHours, maxHoursForDay);
            }
            
            return Math.min(calculatedHours, maxHoursForDay);
        } catch (e) {
            console.error('Error calculating current hours:', e);
            return Math.min(serverHours || 0, maxHoursForDay);
        }
    }
    
    // Fallback: return server hours or 0
    return Math.min(data.hours_completed || 0, 24);
}

// Store the date/month used for weekly/monthly to refresh data
let currentWeekDate = null;
let currentMonth = null;

// Function to update progress bar in real-time (smooth, no interruptions)
function updateProgressBarRealTime() {
    if (!currentAttendanceData || !currentFilterType) return;
    
    const contentDiv = document.getElementById('attendanceOverviewContent');
    if (!contentDiv) return;
    
    let hoursCompleted = 0;
    let totalHours = 0;
    let percentage = 0;
    
    if (currentFilterType === 'today' || currentFilterType === 'date') {
        // For today/date, calculate real-time from clock_in
        hoursCompleted = calculateCurrentHours(currentAttendanceData);
        totalHours = 9;
        percentage = totalHours > 0 ? (hoursCompleted / totalHours * 100) : 0;
    } else if (currentFilterType === 'weekly') {
        // For weekly, use stored data and add today's real-time hours if applicable
        const baseHours = currentAttendanceData.hours_completed || 0;
        totalHours = currentAttendanceData.total_hours || 0;
        
        // If today is in the week and user is clocked in, add real-time hours
        if (currentAttendanceData.clock_in && 
            (!currentAttendanceData.clock_out || currentAttendanceData.clock_out === 'N/A')) {
            // User is clocked in today, calculate real-time hours
            const todayHours = calculateCurrentHours(currentAttendanceData);
            const storedTodayHours = currentAttendanceData.today_hours || 0;
            // Replace today's hours with real-time calculation
            hoursCompleted = baseHours - storedTodayHours + todayHours;
        } else {
            hoursCompleted = baseHours;
        }
        
        percentage = totalHours > 0 ? (hoursCompleted / totalHours * 100) : 0;
    } else if (currentFilterType === 'monthly') {
        // For monthly, similar to weekly
        const baseHours = currentAttendanceData.hours_completed || 0;
        totalHours = currentAttendanceData.total_hours || 0;
        
        // If user is clocked in today, add real-time hours
        if (currentAttendanceData.clock_in && 
            (!currentAttendanceData.clock_out || currentAttendanceData.clock_out === 'N/A')) {
            // User is clocked in today, calculate real-time hours
            const todayHours = calculateCurrentHours(currentAttendanceData);
            const storedTodayHours = currentAttendanceData.today_hours || 0;
            // Replace today's hours with real-time calculation
            hoursCompleted = baseHours - storedTodayHours + todayHours;
        } else {
            hoursCompleted = baseHours;
        }
        
        percentage = totalHours > 0 ? (hoursCompleted / totalHours * 100) : 0;
    }
    
    // Update the progress bar elements smoothly without any loading indicators
    const hoursText = contentDiv.querySelector('.h5.mb-0');
    const badge = contentDiv.querySelector('.badge');
    const progressBar = contentDiv.querySelector('.progress-bar');
    const hoursCompletedLabel = contentDiv.querySelector('h6.text-muted.mb-2');
    
    // Check if late punch-in (from currentAttendanceData)
    const isLate = currentAttendanceData && currentAttendanceData.is_late;
    
    if (hoursText) {
        if (currentFilterType === 'today' || currentFilterType === 'date') {
            hoursText.textContent = `${hoursCompleted.toFixed(2)}/${totalHours} hours`;
        } else {
            hoursText.textContent = `${Math.round(hoursCompleted)}/${totalHours} hours`;
        }
    }
    
    if (badge) {
        badge.textContent = `${percentage.toFixed(1)}%`;
        // Update badge color if late
        if (isLate) {
            badge.className = 'badge bg-danger';
        }
    }
    
    if (progressBar) {
        // Smooth transition for progress bar width
        progressBar.style.transition = 'width 0.5s ease';
        progressBar.style.width = `${Math.min(percentage, 100)}%`;
        progressBar.setAttribute('aria-valuenow', hoursCompleted);
        progressBar.textContent = `${percentage.toFixed(1)}%`;
        // Update progress bar color if late
        if (isLate) {
            progressBar.className = 'progress-bar bg-danger';
        }
    }
    
    // Update hours completed label to show "(Late Punch-In)" if late
    if (hoursCompletedLabel && isLate && !hoursCompletedLabel.innerHTML.includes('Late Punch-In')) {
        hoursCompletedLabel.innerHTML = hoursCompletedLabel.innerHTML.replace('Hours Completed', 'Hours Completed <span class="text-danger">(Late Punch-In)</span>');
    }
}

        function renderAttendanceOverview(data, filterType) {
            const contentDiv = document.getElementById('attendanceOverviewContent');
            const punchInOutSection = document.getElementById('punchInOutSection');
            let html = '';

            // Check if selected date is today
            const datePicker = document.getElementById('attendanceDatePicker');
            const selectedDate = datePicker ? datePicker.value : null;
            const today = new Date().toISOString().split('T')[0];
            const isSelectedDateToday = selectedDate === today;

            // Hide punch in/out section for:
            // - date filter when selected date is NOT today
            // - weekly view
            // - monthly view
            if (filterType === 'weekly' || filterType === 'monthly' || 
                (filterType === 'date' && !isSelectedDateToday)) {
                if (punchInOutSection) {
                    punchInOutSection.style.display = 'none';
                }
            } else {
                // Show punch in/out section for today view or when selected date is today
                if (punchInOutSection) {
                    punchInOutSection.style.display = 'block';
                }
            }

            if (filterType === 'today' || filterType === 'date') {
                // Check if late punch-in
                const isLate = data.is_late || false;
                const progressBarClass = isLate ? 'bg-danger' : (data.hours_completed >= 9 ? 'bg-success' : 'bg-success');
                const badgeClass = isLate ? 'bg-danger' : (data.hours_completed >= 9 ? 'bg-success' : 'bg-success');
                const lateText = isLate ? ' <span class="text-danger">(Late Punch-In)</span>' : '';
                
                if (filterType === 'date' && !isSelectedDateToday) {
                    // For selected date (not today), show punch in and punch out times with progress bar
                    const hoursCompleted = data.hours_completed || 0;
                    const totalHours = 9; // Expected hours per day
                    const percentage = totalHours > 0 ? (hoursCompleted / totalHours * 100) : 0;
                    
                    html = `
                        <div class="attendance-detail">
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Hours Completed${lateText}</h6>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="h5 mb-0">${hoursCompleted.toFixed(2)}/${totalHours} hours</span>
                                    <span class="badge ${badgeClass}">${percentage.toFixed(1)}%</span>
                                </div>
                                <div class="progress" style="height: 30px;">
                                    <div class="progress-bar ${progressBarClass}" 
                                         role="progressbar" 
                                         style="width: ${Math.min(percentage, 100)}%" 
                                         aria-valuenow="${hoursCompleted}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="${totalHours}">
                                        ${percentage.toFixed(1)}%
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6 mb-3">
                                    <small class="text-muted d-block">Punch In Time</small>
                                    <p class="small mb-0 ${isLate ? 'text-danger' : ''}" style="font-size: 0.875rem;">${data.clock_in || 'N/A'}</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <small class="text-muted d-block">Punch Out Time</small>
                                    <p class="small mb-0" style="font-size: 0.875rem;">${data.clock_out || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    // Today view - show hours completed with progress (buttons shown above)
                    html = `
                        <div class="attendance-detail">
                            <div class="mb-2">
                                <h6 class="text-muted mb-2">Hours Completed${lateText}</h6>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="h5 mb-0">${data.hours_completed || '0'}/9 hours</span>
                                    <span class="badge ${badgeClass}">${((data.hours_completed || 0) / 9 * 100).toFixed(1)}%</span>
                                </div>
                                <div class="progress" style="height: 30px;">
                                    <div class="progress-bar ${progressBarClass}" 
                                         role="progressbar" 
                                         style="width: ${Math.min(((data.hours_completed || 0) / 9 * 100), 100)}%" 
                                         aria-valuenow="${data.hours_completed || 0}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="9">
                                        ${Math.min(((data.hours_completed || 0) / 9 * 100), 100).toFixed(1)}%
                                    </div>
                                </div>
                            </div>
                            ${data.clock_out ? `<div class="mb-2"><h6 class="text-muted mb-2">Punch Out Time</h6><p class="h6 mb-0">${data.clock_out}</p></div>` : ''}
                        </div>
                    `;
                }
            } else if (filterType === 'weekly') {
                // Weekly view - show hours completed with progress bar, Days Worked and Week Period
                const hoursCompleted = Math.round(data.hours_completed || 0);
                const totalHours = Math.round(data.total_hours || 0);
                const percentage = data.percentage || 0;
                
                html = `
                    <div class="attendance-detail">
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Hours Completed</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="h5 mb-0">${hoursCompleted}/${totalHours} hours</span>
                                <span class="badge ${hoursCompleted >= totalHours ? 'bg-success' : 'bg-success'}">${percentage.toFixed(1)}%</span>
                            </div>
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar ${hoursCompleted >= totalHours ? 'bg-success' : 'bg-success'}" 
                                     role="progressbar" 
                                     style="width: ${Math.min(percentage, 100)}%" 
                                     aria-valuenow="${hoursCompleted}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="${totalHours}">
                                    ${percentage.toFixed(1)}%
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">Days Worked</small>
                                <p class="small mb-0" style="font-size: 0.875rem;">${data.days_worked || 0} days</p>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">Week Period</small>
                                <p class="small mb-0" style="font-size: 0.875rem;">${data.week_start || ''} - ${data.week_end || ''}</p>
                            </div>
                        </div>
                        <!-- Week Navigation -->
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="prevWeekBtn">
                                <i class="fas fa-chevron-left"></i> Previous Week
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="nextWeekBtn">
                                Next Week <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                // Re-attach week navigation listeners after rendering
                setTimeout(() => {
                    const prevWeekBtn = document.getElementById('prevWeekBtn');
                    const nextWeekBtn = document.getElementById('nextWeekBtn');
                    if (prevWeekBtn) {
                        prevWeekBtn.addEventListener('click', function() {
                            adjustWeekOffset(-1);
                        });
                    }
                    if (nextWeekBtn) {
                        nextWeekBtn.addEventListener('click', function() {
                            adjustWeekOffset(1);
                        });
                    }
                }, 100);
            } else if (filterType === 'monthly') {
                // Monthly view - show hours completed with progress bar, Days Worked and Month Name
                const hoursCompleted = Math.round(data.hours_completed || 0);
                const totalHours = Math.round(data.total_hours || 0);
                const percentage = data.percentage || 0;
                
                html = `
                    <div class="attendance-detail">
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Hours Completed</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="h5 mb-0">${hoursCompleted}/${totalHours} hours</span>
                                <span class="badge ${hoursCompleted >= totalHours ? 'bg-success' : 'bg-success'}">${percentage.toFixed(1)}%</span>
                            </div>
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar ${hoursCompleted >= totalHours ? 'bg-success' : 'bg-success'}" 
                                     role="progressbar" 
                                     style="width: ${Math.min(percentage, 100)}%" 
                                     aria-valuenow="${hoursCompleted}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="${totalHours}">
                                    ${percentage.toFixed(1)}%
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">Days Worked</small>
                                <p class="small mb-0" style="font-size: 0.875rem;">${data.days_worked || 0} days</p>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">Month Name</small>
                                <p class="small mb-0" style="font-size: 0.875rem;">${data.month_name || ''}</p>
                            </div>
                        </div>
                    </div>
                `;
            }

            contentDiv.innerHTML = html;
            
            // Store current data for real-time updates
            currentAttendanceData = data;
            currentFilterType = filterType;
            
            // Stop any existing interval
            stopRealTimeUpdates();
            
            // Start real-time updates if viewing current period
            if (isCurrentPeriod(filterType, data)) {
                // Update immediately
                updateProgressBarRealTime();
                
                // Update every 5 seconds for smooth continuous progress
                // This ensures the progress bar updates continuously without interruptions
                attendanceUpdateInterval = setInterval(() => {
                    // Always update the progress bar smoothly without reloading
                    // This runs every 5 seconds for smooth continuous progress
                    updateProgressBarRealTime();
                }, 5000); // Update every 5 seconds for smooth continuous progress
                
                // For weekly/monthly, also refresh data from server every 2 minutes in background
                // but don't show loading - just update the stored data silently
                if (filterType === 'weekly' || filterType === 'monthly') {
                    setInterval(() => {
                        const url = '{{ route("attendance.overview") }}';
                        const payload = {
                            _token: '{{ csrf_token() }}',
                            filter_type: filterType,
                            employee_id: {{ $emp->id ?? 0 }}
                        };
                        
                        if (filterType === 'weekly' && currentWeekDate) {
                            payload.date = currentWeekDate;
                        } else if (filterType === 'monthly' && currentMonth) {
                            payload.month = currentMonth;
                        }
                        
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(r => r.json())
                        .then(json => {
                            if (json.success && json.data) {
                                // Update stored data silently without reloading UI
                                currentAttendanceData = json.data;
                                // Progress bar will update on next interval automatically
                            }
                        })
                        .catch(err => {
                            console.error('Background refresh error:', err);
                        });
                    }, 120000); // Refresh every 2 minutes in background
                }
            }
        }
    </script>
@endpush

<style>
#confirmClockOutModal {
    display: none;
}

.attendance-filter-option.active {
    background-color: #007bff;
    color: white;
}

.attendance-filter-option:hover {
    background-color: #f8f9fa;
}

.attendance-filter-option.active:hover {
    background-color: #0056b3;
    color: white;
}

.attendance-detail {
    padding: 10px 0;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

#attendanceLoading {
    display: none;
}
</style>