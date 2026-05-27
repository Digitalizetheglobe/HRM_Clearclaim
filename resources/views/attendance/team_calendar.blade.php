@extends('layouts.admin')

@section('page-title')
    {{ __('Team Calendar') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Team Calendar') }}</li>
@endsection

@php
    $months = [
        '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
        '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
        '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
    ];
    $years = range(date('Y') - 5, date('Y') + 5);

    // Helper function to convert 24-hour time to 12-hour format with AM/PM
    if (!function_exists('formatTime12Hour')) {
        function formatTime12Hour($timeStr) {
            if (!$timeStr || $timeStr === '00:00:00' || $timeStr === '00:00') {
                return '0:00';
            }
            
            $parts = explode(':', $timeStr);
            $hours = (int)$parts[0];
            $minutes = isset($parts[1]) ? (int)$parts[1] : 0;
            
            $period = $hours >= 12 ? 'PM' : 'AM';
            $hours = $hours % 12;
            $hours = $hours ? $hours : 12; // 0 should be 12
            
            $minutesStr = $minutes < 10 ? '0' . $minutes : $minutes;
            
            return $hours . ':' . $minutesStr . ' ' . $period;
        }
    }

    // Helper function to calculate total time between clock_in and clock_out
    if (!function_exists('calculateTotalTime')) {
        function calculateTotalTime($clockIn, $clockOut) {
            if (!$clockOut || $clockOut === '00:00:00' || $clockOut === '00:00' || !$clockIn || $clockIn === '00:00:00' || $clockIn === '00:00') {
                return 'No Punch Out';
            }
            
            $inParts = explode(':', $clockIn);
            $outParts = explode(':', $clockOut);
            
            $inTotalMinutes = ((int)$inParts[0] * 60) + (int)$inParts[1];
            $outTotalMinutes = ((int)$outParts[0] * 60) + (int)$outParts[1];
            
            $diffMinutes = $outTotalMinutes - $inTotalMinutes;
            if ($diffMinutes < 0) {
                $diffMinutes = (24 * 60) - $inTotalMinutes + $outTotalMinutes;
            }
            
            $hours = floor($diffMinutes / 60);
            $minutes = $diffMinutes % 60;
            
            return $hours . 'h ' . $minutes . 'm';
        }
    }

    // Helper function to calculate total hours worked in decimal format
    if (!function_exists('calculateTotalHours')) {
        function calculateTotalHours($clockIn, $clockOut) {
            if (!$clockOut || $clockOut === '00:00:00' || $clockOut === '00:00' || !$clockIn || $clockIn === '00:00:00' || $clockIn === '00:00') {
                return 0;
            }
            
            $inParts = explode(':', $clockIn);
            $outParts = explode(':', $clockOut);
            
            $inTotalMinutes = ((int)$inParts[0] * 60) + (int)$inParts[1];
            $outTotalMinutes = ((int)$outParts[0] * 60) + (int)$outParts[1];
            
            $diffMinutes = $outTotalMinutes - $inTotalMinutes;
            if ($diffMinutes < 0) {
                $diffMinutes = (24 * 60) - $inTotalMinutes + $outTotalMinutes;
            }
            
            return $diffMinutes / 60;
        }
    }
@endphp

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['attendance.team.calendar'], 'method' => 'get', 'id' => 'attendance_calendar_filter']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                        <select name="employee_id" class="form-control select2" id="employee_id">
                                            <option value="">{{ __('Select Employee') }}</option>
                                            @foreach($allEmployees as $employee)
                                                <option value="{{ $employee->id }}" {{ ($selectedEmployee && $selectedEmployee->id == $employee->id) ? 'selected' : '' }}>
                                                    {{ $employee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                        <select name="month" class="form-control select" id="month">
                                            @foreach($months as $key => $name)
                                                <option value="{{ $key }}" {{ $currentMonth == (int)$key ? 'selected' : '' }}>{{ __($name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('year', __('Year'), ['class' => 'form-label']) }}
                                        <select name="year" class="form-control select" id="year">
                                            @foreach($years as $year)
                                                <option value="{{ $year }}" {{ $currentYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-auto mt-4">
                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('attendance_calendar_filter').submit(); return false;">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('attendance.team.calendar') }}" class="btn btn-sm btn-danger">
                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

        @if($selectedEmployee)
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6 text-md-start text-center mb-2 mb-md-0">
                                <h5 class="mb-0">{{ $selectedEmployee->name }} - {{ __($months[sprintf('%02d', $currentMonth)]) }} {{ $currentYear }}</h5>
                            </div>
                            <div class="col-md-6 text-md-end text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('attendance.team.calendar', ['employee_id' => $selectedEmployee->id, 'month' => $previousMonth, 'year' => $previousYear]) }}" class="btn btn-primary d-inline-flex align-items-center">
                                        <i class="ti ti-chevron-left me-1"></i> {{ __('Previous') }}
                                    </a>
                                    <a href="{{ route('attendance.team.calendar', ['employee_id' => $selectedEmployee->id, 'month' => $nextMonth, 'year' => $nextYear]) }}" class="btn btn-primary d-inline-flex align-items-center">
                                        {{ __('Next') }} <i class="ti ti-chevron-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12 d-flex flex-wrap gap-2 justify-content-start align-items-center" style="font-size: 0.85em;">
                                <div class="d-flex align-items-center"><span class="badge bg-success me-2">&nbsp;</span> {{ __('Present (9+ Hours)') }}</div>
                                <div class="d-flex align-items-center"><span class="badge bg-warning text-dark me-2">&nbsp;</span> {{ __('Present (< 9 Hours)') }}</div>
                                <div class="d-flex align-items-center"><span class="badge bg-danger me-2">&nbsp;</span> {{ __('Absent') }}</div>
                                <div class="d-flex align-items-center"><span class="badge bg-warning me-2">&nbsp;</span> {{ __('Leave') }}</div>
                                <div class="d-flex align-items-center"><span class="badge bg-secondary me-2">&nbsp;</span> {{ __('WO (Week Off)') }}</div>
                                <div class="d-flex align-items-center"><span class="badge bg-info me-2">&nbsp;</span> {{ __('H (Holiday)') }}</div>
                                <div class="d-flex align-items-center"><span class="badge bg-primary me-2">&nbsp;</span> {{ __('Half Day') }}</div>
                                <div class="d-flex align-items-center"><span class="badge bg-danger me-2" style="background-color: #dc3545 !important;">&nbsp;</span> {{ __('Half Day (Punch Miss)') }}</div>
                                <div class="d-flex align-items-center"><span class="badge bg-warning me-2" style="background-color: #fd7e14 !important;">&nbsp;</span> {{ __('Half Day (Late)') }}</div>
                                <div class="d-flex align-items-center"><span class="badge me-2" style="border: 2px solid #e3342f; background: #ffebee;">&nbsp;</span> {{ __('LATE') }}</div>
                            </div>
                        </div>

                        <div class="calendar-grid">
                            @php
                                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
                                $firstDay = date('N', strtotime("$currentYear-$currentMonth-01"));
                                $attendance = $attendanceData[$selectedEmployee->id]['data'] ?? [];
                            @endphp

                            <div class="calendar-header-row">
                                <div class="calendar-day-head">{{ __('Mon') }}</div>
                                <div class="calendar-day-head">{{ __('Tue') }}</div>
                                <div class="calendar-day-head">{{ __('Wed') }}</div>
                                <div class="calendar-day-head">{{ __('Thu') }}</div>
                                <div class="calendar-day-head">{{ __('Fri') }}</div>
                                <div class="calendar-day-head">{{ __('Sat') }}</div>
                                <div class="calendar-day-head">{{ __('Sun') }}</div>
                            </div>

                            <div class="calendar-days-row">
                                @for($i = 1; $i < $firstDay; $i++)
                                    <div class="calendar-day empty"></div>
                                @endfor

                                @for($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $dateString = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
                                        $dayOfWeek = date('N', strtotime($dateString)); // 1 (Mon) - 7 (Sun)
                                        $dayData = $attendance[$dateString] ?? null;
                                        
                                        $class = '';
                                        $title = '';
                                        $badges = [];
                                        $timeInfo = '';
                                        
                                        // Check if Sunday (Week Off)
                                        if ($dayOfWeek == 7) {
                                            $class = 'sunday-background';
                                            $badges[] = '<span class="badge" style="background-color: #6c757d; color: white;">WO</span>';
                                            $title = __('Week Off');
                                        }
                                        
                                        if ($dayData) {
                                            $status = $dayData['type'];
                                            
                                            if ($status === 'present') {
                                                $clockIn = $dayData['clock_in'];
                                                $clockOut = $dayData['clock_out'];
                                                $isLate = $dayData['is_late'] ?? false;
                                                
                                                $formattedIn = formatTime12Hour($clockIn);
                                                $formattedOut = formatTime12Hour($clockOut);
                                                $totalTime = calculateTotalTime($clockIn, $clockOut);
                                                $totalHours = calculateTotalHours($clockIn, $clockOut);
                                                
                                                if ($isLate) {
                                                    $class .= ' late-mark';
                                                    $badges[] = '<span class="badge bg-danger">LATE</span>';
                                                }
                                                
                                                if ($totalHours >= 9) {
                                                    $class .= ' hours-complete';
                                                    $badges[] = '<span class="badge bg-success">Present</span>';
                                                } else {
                                                    $class .= ' hours-incomplete';
                                                    $badges[] = '<span class="badge bg-warning text-dark">Present</span>';
                                                }
                                                
                                                $timeInfo = "In: {$formattedIn}<br>Out: {$formattedOut}<br>Total: {$totalTime}";
                                                $title = __('Clock In: ') . $clockIn . "\n" . __('Clock Out: ') . $clockOut;
                                            } elseif ($status === 'absent') {
                                                $class .= ' bg-danger-light';
                                                $badges[] = '<span class="badge bg-danger">Absent</span>';
                                                $title = __('Absent');
                                            } elseif ($status === 'half_day') {
                                                $clockIn = $dayData['clock_in'];
                                                $clockOut = $dayData['clock_out'];
                                                $actualStatus = $dayData['status'] ?? 'Half Day';
                                                $isLate = $dayData['is_late'] ?? false;
                                                
                                                $formattedIn = formatTime12Hour($clockIn);
                                                $formattedOut = formatTime12Hour($clockOut);
                                                
                                                if ($isLate) {
                                                    $class .= ' late-mark';
                                                    $badges[] = '<span class="badge bg-danger">LATE</span>';
                                                }
                                                
                                                $class .= ' half-day-background';
                                                
                                                // Determine badge color and text based on actual status
                                                $badgeColor = '#f59e0b';
                                                $badgeText = 'Half Day';
                                                
                                                if ($actualStatus === 'Half Day (Punch Miss)') {
                                                    $badgeColor = '#dc3545';
                                                    $badgeText = 'Half Day (Punch Miss)';
                                                } elseif ($actualStatus === 'Half Day (Late)') {
                                                    $badgeColor = '#fd7e14';
                                                    $badgeText = 'Half Day (Late)';
                                                }
                                                
                                                $badges[] = '<span class="badge" style="background-color: ' . $badgeColor . '; color: white;">' . $badgeText . '</span>';
                                                
                                                // Calculate display punch-out time (4.5 hours after clock in)
                                                $displayClockOut = $formattedOut;
                                                $actualClockOutDisplay = '';
                                                if ($clockIn && $clockIn !== '00:00:00' && $clockIn !== '00:00') {
                                                    $clockInParts = explode(':', $clockIn);
                                                    $inHours = (int)$clockInParts[0];
                                                    $inMinutes = (int)$clockInParts[1];
                                                    
                                                    $outTotalMinutes = ($inHours * 60 + $inMinutes) + 270; // 4.5 hours
                                                    $outHours = floor($outTotalMinutes / 60) % 24;
                                                    $outMinutes = $outTotalMinutes % 60;
                                                    
                                                    $calculatedClockOut = sprintf('%02d:%02d', $outHours, $outMinutes);
                                                    $displayClockOut = formatTime12Hour($calculatedClockOut);
                                                    
                                                    if ($clockOut && $clockOut !== '00:00:00' && $clockOut !== '00:00' && $clockOut !== $calculatedClockOut) {
                                                        $formattedActualClockOut = formatTime12Hour($clockOut);
                                                        $actualClockOutDisplay = '<br><span style="color: #666; font-size: 85%; font-weight: normal;">(Actual: ' . $formattedActualClockOut . ')</span>';
                                                    }
                                                }
                                                
                                                $timeInfo = "In: {$formattedIn}<br>Out: {$displayClockOut}{$actualClockOutDisplay}<br>Total: 4h 30m";
                                            } elseif ($status === 'leave') {
                                                $class .= ' bg-warning-light';
                                                $badges[] = '<span class="badge bg-warning text-dark">Leave</span>';
                                                $title = __('Leave: ') . ($dayData['reason'] ?? '');
                                            } elseif ($status === 'lop') {
                                                $class .= ' bg-danger-light';
                                                $badges[] = '<span class="badge bg-danger">LOP</span>';
                                                $title = __('LOP: ') . ($dayData['reason'] ?? '');
                                            } elseif ($status === 'holiday') {
                                                $class .= ' bg-info-light';
                                                $occasion = $dayData['reason'] ?? 'Holiday';
                                                $badges[] = '<span class="badge" style="background-color: #17a2b8; color: white;">H</span>';
                                                $timeInfo = '<div style="font-size: 0.9em; margin-top: 4px; font-weight: 600; color: #17a2b8; word-break: break-word;">' . e($occasion) . '</div>';
                                                $title = __('Holiday: ') . $occasion;
                                            }
                                        }
                                        
                                        $isToday = $dateString == date('Y-m-d');
                                    @endphp
                                    
                                    <div class="calendar-day {{ $class }} {{ $isToday ? 'today' : '' }}" title="{{ $title }}">
                                        <div class="day-number">{{ $day }}</div>
                                        
                                        @if($dayData && !empty($dayData['earned_comp_off']))
                                            <div class="comp-off-badge" style="position: absolute; top: 5px; right: 5px; background: #ffc107; color: #000; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 0.6rem; font-weight: bold; border: 1px solid #d39e00;" title="Earned Comp Off">
                                                C
                                            </div>
                                        @endif

                                        @if(count($badges) > 0 || $timeInfo)
                                            <div class="day-info mt-2">
                                                <div class="d-flex flex-wrap gap-1 mb-1">
                                                    {!! implode('', $badges) !!}
                                                </div>
                                                @if($timeInfo)
                                                    <div class="time-info" style="font-size: 0.78rem; line-height: 1.4; font-weight: 500;">
                                                        {!! $timeInfo !!}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-calendar-off text-muted mb-3" style="font-size: 3rem; display: block;"></i>
                        <h5>
                            {{ __('Please select a team member to view their attendance calendar.') }}
                        </h5>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .calendar-grid {
            display: flex;
            flex-direction: column;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }
        .calendar-header-row {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: #fdfdfd;
            border-bottom: 2px solid #eef2f6;
        }
        .calendar-day-head {
            padding: 12px 10px;
            text-align: center;
            font-weight: 700;
            color: #4a5568;
            font-size: 0.9rem;
            border-right: 1px solid #eef2f6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .calendar-day-head:last-child { border-right: none; }
        
        .calendar-days-row {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }
        .calendar-day {
            min-height: 120px;
            padding: 10px;
            border-right: 1px solid #eef2f6;
            border-bottom: 1px solid #eef2f6;
            position: relative;
            transition: all 0.2s ease-in-out;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }
        .calendar-day:nth-child(7n) { border-right: none; }
        .calendar-day:hover {
            background-color: #f7fafc;
            transform: scale(1.01);
            z-index: 2;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .calendar-day.empty { 
            background: #fafbfc; 
            pointer-events: none;
        }
        .day-number {
            font-weight: 700;
            font-size: 1.15rem;
            color: #2d3748;
            margin-bottom: 4px;
            line-height: 1;
        }
        
        .calendar-day.today {
            border: 2.5px solid #5c59e8 !important;
            z-index: 3;
            box-shadow: 0 0 10px rgba(92, 89, 232, 0.15);
            border-radius: 4px;
        }
        .calendar-day.today .day-number {
            color: #5c59e8;
            font-weight: 800;
        }
        
        /* Status Backgrounds & Highlights */
        .calendar-day.hours-complete {
            background-color: rgba(40, 167, 69, 0.08) !important;
            color: #155724;
        }
        .calendar-day.hours-incomplete {
            background-color: rgba(255, 193, 7, 0.1) !important;
            color: #856404;
        }
        .calendar-day.bg-danger-light {
            background-color: rgba(220, 53, 69, 0.08) !important;
            color: #721c24;
        }
        .calendar-day.bg-warning-light {
            background-color: rgba(255, 193, 7, 0.1) !important;
            color: #856404;
        }
        .calendar-day.bg-info-light {
            background-color: rgba(23, 162, 184, 0.08) !important;
            color: #0c5460;
        }
        .calendar-day.sunday-background {
            background-color: rgba(108, 117, 125, 0.05) !important;
            color: #383d41;
        }
        .calendar-day.half-day-background {
            background-color: rgba(245, 158, 11, 0.08) !important;
            color: #78350f;
        }
        .calendar-day.late-mark {
            border: 2px solid #e3342f !important;
            border-radius: 4px;
        }
        
        .day-info {
            font-size: 0.8rem;
            margin-top: auto;
        }
        
        .time-info {
            font-size: 0.78rem;
            line-height: 1.4;
            color: #4a5568;
            background: rgba(255, 255, 255, 0.6);
            padding: 4px;
            border-radius: 4px;
            border: 1px solid rgba(0,0,0,0.03);
        }

        .day-info .badge {
            font-size: 0.7rem;
            padding: 3px 6px;
            font-weight: 700;
            border-radius: 4px;
            text-transform: uppercase;
        }
        
        @media (max-width: 992px) {
            .calendar-day {
                min-height: 90px;
                padding: 6px;
            }
            .day-number { font-size: 1rem; }
            .time-info { font-size: 0.7rem; padding: 2px; }
            .day-info .badge { font-size: 0.65rem; padding: 2px 4px; }
        }

        @media (max-width: 768px) {
            .calendar-grid {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .calendar-header-row, .calendar-days-row {
                min-width: 700px;
            }
            .calendar-day {
                min-height: 100px;
            }
        }
    </style>
@endsection