

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Attendance Calendar')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Attendance Calendar')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-6 mb-2 mb-md-0">
                            <h5 class="mb-0"><?php echo e(__('Attendance Calendar')); ?></h5>
                        </div>
                        <?php if(\Auth::user()->type != 'employee'): ?>
                            <div class="col-12 col-md-6">
                                <form method="GET" action="<?php echo e(route('attendance.calendar')); ?>" class="d-flex">
                                    <?php if(request()->has('month')): ?>
                                        <input type="hidden" name="month" value="<?php echo e(request('month')); ?>">
                                    <?php endif; ?>
                                    <?php if(request()->has('year')): ?>
                                        <input type="hidden" name="year" value="<?php echo e(request('year')); ?>">
                                    <?php endif; ?>
                                    <select name="employee_id" class="form-select me-2" onchange="this.form.submit()">
                                        <option value=""><?php echo e(__('Select Employee')); ?></option>
                                        <?php $__currentLoopData = $allEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($employee->id); ?>" <?php echo e($selectedEmployee && $selectedEmployee->id == $employee->id ? 'selected' : ''); ?>>
                                                <?php echo e($employee->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if($selectedEmployee): ?>
                        <div class="d-flex flex-wrap mt-2 gap-2" style="font-size: 0.85em;">
                            <span class="badge bg-success">Present</span>
                            <span class="badge bg-danger">Absent</span>
                            <span class="badge bg-warning">Leave</span>
                            <span class="badge" style="background-color: #e3342f;">LATE</span>
                            <span class="badge" style="background-color: #d4edda; color: #155724;">9+ Hours</span>
                            <span class="badge" style="background-color: #fff3cd; color: #856404;">< 9 Hours</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <?php if($selectedEmployee): ?>
                        <div id='calendar' style="min-width: 100%;"></div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <?php if(\Auth::user()->type == 'employee'): ?>
                                <?php echo e(__('No employee record found for your account.')); ?>

                            <?php else: ?>
                                <?php echo e(__('Please select an employee to view attendance calendar')); ?>

                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        /* Responsive Calendar Container */
        #calendar {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Calendar Responsive Styles */
        @media (max-width: 992px) {
            .fc-header-toolbar {
                flex-direction: column;
                gap: 10px;
                padding: 10px 5px;
            }
            
            .fc-toolbar-chunk {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
            }
            
            .fc-toolbar-title {
                font-size: 1.2em;
                text-align: center;
                width: 100%;
                margin: 5px 0;
            }
            
            .fc-button-group {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .fc-button {
                padding: 4px 8px;
                font-size: 0.85em;
            }
            
            .fc-daygrid-day-frame {
                min-height: 90px;
                padding: 3px;
            }
            
            .status-details {
                font-size: 9px;
                padding: 3px;
                line-height: 1.3;
            }
            
            .status-details .badge {
                font-size: 8px;
                padding: 2px 4px;
                margin-bottom: 2px;
            }
            
            .status-details .time-info {
                font-size: 8px;
                margin-top: 2px;
            }
        }
        
        @media (max-width: 768px) {
            .card-header {
                padding: 10px;
            }
            
            .card-header h5 {
                font-size: 1.1em;
            }
            
            .fc-header-toolbar {
                padding: 8px 3px;
            }
            
            .fc-toolbar-title {
                font-size: 1em;
            }
            
            .fc-button {
                padding: 3px 6px;
                font-size: 0.75em;
                margin: 2px;
            }
            
            .fc-daygrid-day-frame {
                min-height: 70px;
                padding: 2px;
            }
            
            .fc-daygrid-day-number {
                font-size: 0.9em;
                padding: 2px 4px;
            }
            
            .status-details {
                font-size: 8px;
                padding: 2px;
                line-height: 1.2;
            }
            
            .status-details .badge {
                font-size: 7px;
                padding: 1px 3px;
                margin-bottom: 1px;
                display: block;
                width: fit-content;
            }
            
            .status-details .time-info {
                font-size: 7px;
                margin-top: 1px;
            }
            
            .status-details .time-info br {
                display: none;
            }
            
            .status-details .time-info {
                display: flex;
                flex-direction: column;
                gap: 1px;
            }
        }
        
        @media (max-width: 576px) {
            .card {
                margin: 5px;
            }
            
            .card-body {
                padding: 8px;
            }
            
            .fc-header-toolbar {
                padding: 5px 2px;
            }
            
            .fc-toolbar-title {
                font-size: 0.9em;
            }
            
            .fc-button {
                padding: 2px 4px;
                font-size: 0.7em;
                margin: 1px;
            }
            
            .fc-daygrid-day-frame {
                min-height: 60px;
                padding: 1px;
            }
            
            .fc-daygrid-day-number {
                font-size: 0.8em;
                padding: 1px 2px;
            }
            
            .fc-col-header-cell {
                padding: 4px 2px;
            }
            
            .fc-col-header-cell-cushion {
                font-size: 0.75em;
            }
            
            .status-details {
                font-size: 7px;
                padding: 1px;
                line-height: 1.1;
            }
            
            .status-details .badge {
                font-size: 6px;
                padding: 1px 2px;
                margin-bottom: 1px;
            }
            
            .status-details .time-info {
                font-size: 6px;
                margin-top: 1px;
            }
            
            /* Stack time info vertically on very small screens */
            .status-details .time-info {
                display: flex;
                flex-direction: column;
            }
        }
        
        @media (max-width: 400px) {
            .fc-daygrid-day-frame {
                min-height: 50px;
            }
            
            .status-details {
                font-size: 6px;
            }
            
            .status-details .badge {
                font-size: 5px;
                padding: 1px;
            }
            
            .status-details .time-info {
                font-size: 5px;
            }
        }
        
        /* Hide previous/next month dates */
        .fc-day-other {
            opacity: 0.3;
            pointer-events: none;
        }
        
        .fc-day-other .status-details {
            display: none !important;
        }
        
        /* Status Details Styling */
        .status-details {
            padding: 6px;
            font-size: 11px;
            line-height: 1.5;
            word-wrap: break-word;
        }
        
        .status-details .badge {
            margin-bottom: 4px;
            font-size: 10px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-details .time-info {
            margin-top: 4px;
            color: #333;
            font-size: 10px;
        }
        
        /* Calendar Day Styling */
        .fc-daygrid-day-frame {
            min-height: 120px;
            padding: 4px;
        }
        
        .fc-daygrid-day {
            overflow: visible;
            border: 1px solid #e0e0e0;
        }
        
        .fc-daygrid-day-top {
            position: relative;
            padding: 4px;
        }
        
        /* Hours Complete/Incomplete Backgrounds */
        .fc-daygrid-day.hours-complete {
            background-color: #d4edda !important;
        }

        .fc-daygrid-day.hours-incomplete {
            background-color: #fff3cd !important;
        }
        
        /* Late Mark Highlighting */
        .fc-daygrid-day.late-mark {
            background-color: #ffebee !important;
            border: 2px solid #e3342f !important;
            border-radius: 4px;
        }
        
        .fc-daygrid-day.late-mark .fc-daygrid-day-frame {
            background-color: #ffebee !important;
        }
        
        /* Current Day Highlight */
        .fc-day-today {
            background-color: #e3f2fd !important;
        }
        
        /* Calendar Header Styling */
        .fc-header-toolbar {
            margin-bottom: 1.5em;
        }
        
        .fc-toolbar-title {
            font-size: 1.5em;
            font-weight: 600;
            color: #333;
        }
        
        /* Button Styling */
        .fc-button {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .fc-button-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .fc-button-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        
        .fc-button-active {
            background-color: #0056b3 !important;
        }
        
        /* Card Body Padding for Mobile */
        @media (max-width: 768px) {
            .card-body {
                padding: 10px;
            }
            
            .card-header {
                padding: 10px;
            }
        }
        
        @media (max-width: 576px) {
            .card-body {
                padding: 8px;
            }
            
            .card-header {
                padding: 8px;
            }
            
            /* Make employee selector responsive */
            .form-select {
                font-size: 0.9em;
                padding: 6px 8px;
            }
        }
        
        /* Horizontal scroll for very small screens */
        @media (max-width: 400px) {
            #calendar {
                overflow-x: scroll;
                -webkit-overflow-scrolling: touch;
            }
            
            .fc-scroller {
                overflow-x: auto !important;
            }
        }
        
        /* Force mobile layout on small screens */
        @media (max-width: 768px) {
            .fc .fc-daygrid-day {
                min-width: 0 !important;
                width: auto !important;
            }
            
            .fc .fc-col-header-cell {
                min-width: 0 !important;
                width: auto !important;
                padding: 4px 2px !important;
            }
            
            .fc .fc-col-header-cell-cushion {
                font-size: 0.7em !important;
                padding: 2px !important;
            }
            
            .fc .fc-daygrid-day-number {
                font-size: 0.75em !important;
                padding: 2px 4px !important;
            }
            
            .fc-daygrid-day-frame {
                min-height: 50px !important;
                padding: 1px !important;
            }
            
            /* Make calendar table responsive */
            .fc-scroller {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }
            
            .fc-daygrid-body {
                min-width: 600px !important;
            }
        }
        
        @media (max-width: 576px) {
            .fc-daygrid-body {
                min-width: 500px !important;
            }
            
            .fc-daygrid-day-frame {
                min-height: 45px !important;
            }
        }
        
        @media (max-width: 400px) {
            .fc-daygrid-body {
                min-width: 400px !important;
            }
            
            .fc-daygrid-day-frame {
                min-height: 40px !important;
            }
        }
        
        /* Touch-friendly buttons */
        @media (hover: none) and (pointer: coarse) {
            .fc-button {
                min-height: 36px;
                min-width: 36px;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('script-page'); ?>
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
            
            <?php if($selectedEmployee): ?>
                var attendanceData = <?php echo json_encode($attendanceData, 15, 512) ?>;
                
                // Detect mobile device
                var isMobile = window.innerWidth <= 768;
                
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: isMobile ? 'dayGridMonth' : 'dayGridMonth',
                    initialDate: '<?php echo e($currentYear); ?>-<?php echo e(str_pad($currentMonth, 2, "0", STR_PAD_LEFT)); ?>-01',
                    headerToolbar: isMobile ? {
                        left: 'prev,next',
                        center: 'title',
                        right: 'today'
                    } : {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,dayGridWeek,dayGridDay'
                    },
                    timeZone: 'UTC',
                    // Hide dates from other months
                    fixedWeekCount: false,
                    showNonCurrentDates: false,
                    // Responsive settings
                    height: isMobile ? 'auto' : 'auto',
                    contentHeight: 'auto',
                    // Mobile optimizations
                    aspectRatio: isMobile ? 1.0 : 1.35,
                    dayMaxEvents: isMobile ? 1 : 3,
                    dayCellDidMount: function(info) {
                        var currentMonth = <?php echo e($currentMonth); ?>;
                        var currentYear = <?php echo e($currentYear); ?>;
                        var cellMonth = info.date.getMonth() + 1; // JavaScript months are 0-indexed
                        var cellYear = info.date.getFullYear();
                        
                        // Skip rendering for dates outside current month
                        if (cellMonth !== currentMonth || cellYear !== currentYear) {
                            // Hide content for other months - make them very subtle
                            info.el.classList.add('fc-day-other');
                            info.el.style.opacity = '0.2';
                            info.el.style.pointerEvents = 'none';
                            // Clear any existing content
                            var frame = info.el.querySelector('.fc-daygrid-day-frame');
                            if (frame) {
                                var existingDetails = frame.querySelector('.status-details');
                                if (existingDetails) {
                                    existingDetails.remove();
                                }
                            }
                            return;
                        }
                        
                        var dateStr = info.date.toISOString().split('T')[0];
                        
                        // Check if we have data for the selected employee
                        if (attendanceData['<?php echo e($selectedEmployee->id); ?>'] && 
                            attendanceData['<?php echo e($selectedEmployee->id); ?>'].data[dateStr]) {
                            
                            var status = attendanceData['<?php echo e($selectedEmployee->id); ?>'].data[dateStr].type;
                            var statusClass = status.replace('_', '-');
                            
                            info.el.classList.add(statusClass);
                            
                            var detailsEl = document.createElement('div');
                            detailsEl.className = 'status-details';
                            
                            if (status === 'present') {
                                var clockIn = attendanceData['<?php echo e($selectedEmployee->id); ?>'].data[dateStr].clock_in;
                                var clockOut = attendanceData['<?php echo e($selectedEmployee->id); ?>'].data[dateStr].clock_out;
                                var isLate = attendanceData['<?php echo e($selectedEmployee->id); ?>'].data[dateStr].is_late || false;
                                
                                var formattedIn = formatTime12Hour(clockIn);
                                var formattedOut = formatTime12Hour(clockOut);
                                var totalTime = calculateTotalTime(clockIn, clockOut);
                                
                                // Calculate total hours worked
                                var totalHours = calculateTotalHours(clockIn, clockOut);
                                
                                // Highlight late marks in red
                                if (isLate) {
                                    info.el.classList.add('late-mark');
                                }
                                
                                // Apply green or red background based on 9 hours completion
                                if (totalHours >= 9) {
                                    info.el.classList.add('hours-complete');
                                } else {
                                    info.el.classList.add('hours-incomplete');
                                }
                                
                                // Responsive badge sizes
                                var isMobile = window.innerWidth <= 768;
                                var lateBadgeSize = isMobile ? '8px' : '11px';
                                var presentBadgeSize = isMobile ? '10px' : '15px';
                                var timeInfoSize = isMobile ? '9px' : '11px';
                                
                                var lateBadge = isLate ? '<span class="badge" style="background-color: #e3342f; color: white; padding: 2px 4px; border-radius: 3px; display: inline-block; font-size: ' + lateBadgeSize + '; font-weight: 600; margin-bottom: 2px;">LATE</span><br>' : '';
                                
                                // On mobile, show compact version
                                var mobileHtml = '';
                                if (isMobile) {
                                    mobileHtml = `${lateBadge}<span class="badge" style="background-color: #3490dc; color: white; padding: 1px 3px; border-radius: 2px; display: block; font-size: ${presentBadgeSize}; font-weight: 600; margin-bottom: 1px; width: fit-content;">
                                        P</span>
                                        <div class="time-info" style="font-size: ${timeInfoSize}; line-height: 1.2;">
                                            ${formattedIn}<br>${formattedOut}
                                        </div>`;
                                } else {
                                    mobileHtml = `${lateBadge}<span class="badge" style="background-color: #3490dc; color: white; padding: 3px 6px; border-radius: 3px; display: inline-block; font-size: ${presentBadgeSize}; font-weight: 600; margin-bottom: 2px;">
                                        Present</span>
                                        <div class="time-info" style="font-size: ${timeInfoSize};">
                                            In: ${formattedIn}<br>
                                            Out: ${formattedOut}<br>
                                            Total: ${totalTime}
                                        </div>`;
                                }
                                
                                var html = mobileHtml;
                                
                                detailsEl.innerHTML = html;
                            } else if (status === 'absent') {
                                var isMobile = window.innerWidth <= 768;
                                var badgeSize = isMobile ? '10px' : '15px';
                                detailsEl.innerHTML = `<span class="badge" style="background-color: #e3342f; color: white; padding: 3px 6px; border-radius: 3px; display: inline-block; font-size: ${badgeSize}; font-weight: 600;">
                                    Absent</span>`;
                            } else if (status === 'leave') {
                                var isMobile = window.innerWidth <= 768;
                                var badgeSize = isMobile ? '10px' : '15px';
                                detailsEl.innerHTML = `<span class="badge" style="background-color: #f6993f; color: white; padding: 3px 6px; border-radius: 3px; display: inline-block; font-size: ${badgeSize}; font-weight: 600;">
                                    Leave</span>`;
                            }
                            
                            info.el.querySelector('.fc-daygrid-day-frame').appendChild(detailsEl);
                        }
                    },
                    // Handle month navigation
                    datesSet: function(dateInfo) {
                        // Update URL when month changes
                        var newMonth = dateInfo.start.getMonth() + 1;
                        var newYear = dateInfo.start.getFullYear();
                        var currentMonth = <?php echo e($currentMonth); ?>;
                        var currentYear = <?php echo e($currentYear); ?>;
                        
                        // Only reload if month/year actually changed
                        if (newMonth != currentMonth || newYear != currentYear) {
                            var url = new URL(window.location);
                            url.searchParams.set('month', newMonth);
                            url.searchParams.set('year', newYear);
                            // Preserve employee_id if present
                            <?php if($selectedEmployee): ?>
                            url.searchParams.set('employee_id', '<?php echo e($selectedEmployee->id); ?>');
                            <?php endif; ?>
                            window.location.href = url.toString();
                        }
                    }
                });
                
                calendar.render();
                
                // Make calendar responsive on window resize
                var resizeTimer;
                var lastWidth = window.innerWidth;
                window.addEventListener('resize', function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        var currentWidth = window.innerWidth;
                        var wasMobile = lastWidth <= 768;
                        var isNowMobile = currentWidth <= 768;
                        
                        // If mobile state changed, re-render completely
                        if (wasMobile !== isNowMobile) {
                            calendar.destroy();
                            location.reload();
                        } else {
                            calendar.updateSize();
                        }
                        lastWidth = currentWidth;
                    }, 300);
                });
                
                // Force calendar to recalculate on load
                setTimeout(function() {
                    calendar.updateSize();
                }, 100);
            <?php endif; ?>
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/attendance/calendar.blade.php ENDPATH**/ ?>