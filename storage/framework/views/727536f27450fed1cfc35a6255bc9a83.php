<?php
    $setting = App\Models\Utility::settings();
?>

<?php $__env->startSection('content'); ?>
<style>

    .fc-prev-button, .fc-next-button {
        padding: 5px 8px !important; /* Smaller arrow buttons */
        font-size: 14px !important;
        background-color: #007bff !important; /* Bootstrap primary color */
        border-radius: 5px !important;
        border: none !important;
        color: white !important;
    

    .fc-prev-button:hover, .fc-next-button:hover {
        background-color: #0056b3 !important;
    }

    #calendar {
        margin-bottom: 10px; /* Space between calendar and arrows */
    }

    .calendar-navigation {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
    }

    .loading {
    position: relative;
    pointer-events: none;
    opacity: 0.7;
    }

    .loading:after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.7) url('<?php echo e(asset("assets/img/loading.gif")); ?>') no-repeat center;
        background-size: 50px 50px;
        z-index: 1000;
    }

</style>
<div>
    <div class="row">
        <?php if(session('status')): ?>
            <div class="alert alert-success" role="alert">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>

    <?php if(Auth::user()->type == 'company' || Auth::user()->type == 'hr' || Auth::user()->type == 'Director'): ?>

        <!-- Welcome Section -->
        <div class="col-12 mb-3">
            <div class="row align-items-top">
                <div class="col-md-8">
                    <?php
                        $hour = date('H');
                        if ($hour < 12) {
                            $greeting = 'Good morning';
                        } elseif ($hour < 17) {
                            $greeting = 'Good afternoon';
                        } else {
                            $greeting = 'Good evening';
                        }
                        // Get full name
                        $userName = Auth::user()->name ?? 'User';
                        
                    ?>
                    <h2 class="mb-2" style="font-size: 25px; font-weight: bold; color:rgb(0, 190, 92);"><?php echo e($greeting); ?>, <?php echo e($userName); ?>!</h2>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="btn-group me-2 z-1">
                        <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="dateFilterButton">
                            Today
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-value="today">Today</a></li>
                            <li><a class="dropdown-item" href="#" data-value="yesterday">Yesterday</a></li>
                            <li><a class="dropdown-item" href="#" data-value="custom">Select Date</a></li>
                        </ul>
                    </div>
                    <input type="date" class="form-control" id="customDatePicker" style="display: none; width: 150px;">

                </div>
            </div>
        </div>


            <!-- Employee specific content -->
        


            <div class="col-xxl-9">
                <div class="row">
                    <!-- Left Side Cards -->
                    <div class="col-xl-12">

            
                       <div class="row">
                            <div class="col-xxl-12">
                                <div class="col-xl-12">


                                    <div class="row">

                                                                            <!-- first Card - Employees -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="card" style="border-radius: 10px; background-color: #fff; cursor: pointer;" onclick="window.location.href='employee'">
                                                <div class="card-body" style="padding: 20px;">
                                                    <div class="align-items-center">
                                                        <div class="col-auto">
                                                            <div style="background-color: #B55CC4; width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                                                <i class="fa-solid fa-user-tie" style="font-size: 25px; color: #fff;"></i>
                                                            </div>
                                                        </div><br>
                                                        <div class="col-auto" style="display: flex; align-items: center; gap: 5px;">
                                                            <h6 style="font-size: 14px; color: #515356; margin: 0;">Total,</h6>
                                                            <h4 class="m-0 text-primary" style="font-size: 15px; color:#555657 !important; font-weight: 800; margin: 0;">Employees</h4>
                                                        </div>
                                                        <div class="col-auto">
                                                            <h6 style="font-size: 14px; color: #0569a6;"> </h6>
                                                            <h4 class="m-0 text-primary" style="font-size: 30px; color : #000 !important; "> <?php echo e($countUser + $countEmployee); ?>  </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Fourth Card - Department -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="card" style="border-radius: 10px; background-color: #fff; cursor: pointer;" >
                                                <div class="card-body" style="padding: 20px;">
                                                    <div class="align-items-center">
                                                        <div class="col-auto">
                                                            <div style="background-color: #299dc6; width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                                                <i class="fa-solid fa-sitemap"  style="font-size: 25px; color: #fff;"></i>
                                                            </div>
                                                        </div><br>
                                                        <div class="col-auto" style="display: flex; align-items: center; gap: 5px;">
                                                            <h6 style="font-size: 14px; color: #515356; margin: 0;">Total,</h6>
                                                            <h4 class="m-0 text-primary" style="font-size: 15px; color: #555657 !important; font-weight: 800; margin: 0;">Department</h4>
                                                        </div>
                                                        <div class="col-auto">
                                                            <h6 style="font-size: 14px; color: #0569a6;"> </h6>
                                                            <h4 class="m-0 text-primary" style="font-size: 30px; color : #000 !important; "> <?php echo e($totalDepartment); ?>  </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    
                                        
                                        <!-- Six Card - Ticket -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="card" style="border-radius: 10px; background-color: #fff; cursor: pointer;" onclick="window.location.href='ticket'">
                                                <div class="card-body" style="padding: 20px;">
                                                    <div class="align-items-center">
                                                        <div class="col-auto">
                                                            <div style="background-color: #FD3995; width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                                                <i class="fa-solid fa-ticket" style="font-size: 25px; color: #fff;"></i>
                                                            </div>
                                                        </div><br>
                                                        <div class="col-auto" style="display: flex; align-items: center; gap: 5px;">
                                                            <h6 style="font-size: 14px; color: #515356; margin: 0;">Total,</h6>
                                                            <h4 class="m-0 text-primary" style="font-size: 15px; color: #555657 !important; font-weight: 800; margin: 0;">Ticket</h4>
                                                        </div>
                                                        <div class="col-auto">
                                                            <h6 style="font-size: 14px; color: #6c757d;"> </h6>
                                                            <h4 class="m-0 text-primary" style="font-size: 30px; color:#000 !important; "> <?php echo e($countTicket); ?>  </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    

                        <!-- Additional Data Below Cards -->
                        <div class="row">
                            <!-- Today's Attendance Card -->
                            <div class="col-12 col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header card-body table-border-style d-flex flex-wrap justify-content-between align-items-center">
                                        <h5 class="mb-2 mb-md-0" style="font-size: 20px; color: black;">
                                            <?php echo e(__("Today's Attendance")); ?>

                                        </h5>
                                    </div>
                                    <div class="card-body" style="height: 300px; overflow: auto; padding-top: 25px;">
                                        <div class="table-responsive">
                                            <table class="table table-bordered text-center" id="attendanceTable">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo e(__('Employee Name')); ?></th>
                                                        <th><?php echo e(__('Clock-In Time')); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__empty_1 = true; $__currentLoopData = $presentEmployeesWithClockIn; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                        <tr>
                                                            <td><?php echo e($data['employee']->name ?? 'N/A'); ?></td>
                                                            <td><?php echo e($data['clock_in'] ?? '--:--'); ?></td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                        <tr>
                                                            <td colspan="2"><?php echo e(__('No attendance records found for today.')); ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Employees On Leave / Absent Card -->
                            <div class="col-12 col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header card-body table-border-style d-flex flex-wrap justify-content-between align-items-center">
                                        <h5 class="mb-2 mb-md-0" style="font-size:20px; color:black; margin: 0;">
                                            <?php echo e(__('Employees On Leave / Absent')); ?>

                                        </h5>
                                    </div>
                                    <div class="card-body" style="height: 300px; overflow: auto; padding-top:25px;">
                                        <div class="table-responsive">
                                            <table class="table table-bordered text-center" id="leaveAbsentTable">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo e(__('Employee Name')); ?></th>
                                                        <th><?php echo e(__('Status')); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $notClockIns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td><?php echo e($employee->name ?? 'N/A'); ?></td>
                                                            <td><span class="badge bg-danger">Absent</span></td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php $__currentLoopData = $employeesNotWorkingToday; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td><?php echo e($employee['employee_name']); ?></td>
                                                            <td><span class="badge bg-warning"><?php echo e($employee['status']); ?></span></td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($notClockIns->isEmpty() && (empty($employeesNotWorkingToday) || count($employeesNotWorkingToday) == 0)): ?>
                                                        <tr>
                                                            <td colspan="2"><?php echo e(__('All employees are present')); ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-xl-12">
                            <div class="card">
                                    <div class="card-header card-body table-border-style d-flex flex-wrap justify-content-between align-items-center">
                                        <h5 class="mb-2 mb-md-0" style="font-size:20px; color:black; margin: 0;">
                                            <?php echo e(__('Notices')); ?>

                                        </h5>
                                        
                                    </div>
                                    <div class="card-body" style="height: 300px; overflow: auto; padding: ; padding-top:25px;">
                                        <div class="table-responsive">
                                            <table class="table table-bordered text-center">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 60%;">Title</th>
                                                        <th style="width: 40%;">Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $notices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td style="word-wrap: break-word; white-space: normal;">
                                                                <?php echo e(Str::limit($notice->title, 50, '...')); ?>

                                                            </td>
                                                            <td>
                                                                <?php echo e(\Carbon\Carbon::parse($notice->notice_startdate)->format('d M Y')); ?> - 
                                                                <?php echo e(\Carbon\Carbon::parse($notice->notice_enddate)->format('d M Y')); ?>

                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                        </div>


                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header card-body table-border-style">
                                    <h5 style="font-size:20px;color:black"><?php echo e(__('Project Details')); ?></h5>
                                </div>
                                <div class="card-body" style="height: 300px; overflow:auto">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo e(__('Project Name')); ?></th>
                                                    <th><?php echo e(__('Start Date')); ?></th>
                                                    <th><?php echo e(__('End Date')); ?></th>
                                                    <?php if(Auth::user()->type != 'employee'): ?>
                                                        <th><?php echo e(__('Assigned Employees')); ?></th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                                <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <tr>
                                                        <td><?php echo e($project->project_name); ?></td>
                                                        <td><?php echo e(\Carbon\Carbon::parse($project->project_startdate)->format('d M Y')); ?></td>
                                                        <td><?php echo e(\Carbon\Carbon::parse($project->project_enddate)->format('d M Y')); ?></td>
                                                        <?php if(Auth::user()->type != 'employee'): ?>
                                                            <td>
                                                                <?php if(is_array($project->assigned_data)): ?>
                                                                    <?php $empCount = 0; ?>
                                                                    <?php $__currentLoopData = $project->assigned_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <?php $__currentLoopData = $assignment['employee_ids'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employeeId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <?php if(isset($employees[$employeeId])): ?>
                                                                                <span class="badge bg-success me-1 mb-1">
                                                                                    <?php echo e($employees[$employeeId]->user->name ?? __('Unknown')); ?>

                                                                                </span>
                                                                                <?php $empCount++; ?>
                                                                                <?php if($empCount % 5 == 0): ?>
                                                                                    <br>
                                                                                <?php endif; ?>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                <?php endif; ?>
                                                            </td>
                                                        <?php endif; ?>
                                    
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center"><?php echo e(__('No projects assigned')); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                                <div class="col-xl-12">
                                    <div class="card">
                                        <div class="card-header card-body table-border-style" style="">
                                            <h5 style="font-size:20px;color:black"><?php echo e(__('Meeting List')); ?></h5>
                                        </div>
                                        <div class="card-body" style="height: 324px; overflow:auto;">
                                            <div class="table-responsive"> 
                                                <table class="table">
                                                    <thead>
                                                        <tr >
                                                        <th><?php echo e(__('Meeting title')); ?></th>
                                                        <th><?php echo e(__('Meeting Date')); ?></th>
                                                        <th><?php echo e(__('Meeting Time')); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="list">
                                                        <?php $__currentLoopData = $meetings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $meeting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                            <td><?php echo e($meeting->title); ?></td>
                                                            <td><?php echo e(\Auth::user()->dateFormat($meeting->date)); ?></td>
                                                            <td><?php echo e(\Auth::user()->timeFormat($meeting->time)); ?></td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </tbody>
                                                </table>
                                            </div>
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
                                <h5 style="font-size:20px;color:black"><?php echo e(__("Upcoming Events This Month")); ?></h5>
                            </div>
                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                <?php if(count($allEvents) > 0): ?>
                                    <div class="list-group">
                                        <?php $__currentLoopData = $allEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                // Extract first name from title (e.g., "Dipali Sevakram Tayade's Anniversary" -> "Dipali's Anniversary")
                                                $title = $event['title'];
                                                if (strpos($title, "'s") !== false) {
                                                    $namePart = explode("'s", $title)[0];
                                                    $eventType = explode("'s", $title)[1] ?? '';
                                                    $firstName = explode(' ', $namePart)[0];
                                                    $title = $firstName . "'s" . $eventType;
                                                }
                                            ?>
                                            <span class="list-group-item list-group-item-action flex-column align-items-start">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?php echo e($title); ?></h6>
                                                    <small><?php echo e(\Carbon\Carbon::parse($event['start'])->format('D, M d')); ?></small>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <?php if($event['type'] == 'birthday'): ?>
                                                            <span class="badge bg-success">Birthday</span>
                                                        <?php elseif($event['type'] == 'anniversary'): ?>
                                                            <span class="badge bg-primary">Anniversary</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-info">Event</span>
                                                        <?php endif; ?>
                                                    </small>
                                                    <?php if(\Carbon\Carbon::parse($event['start'])->isToday()): ?>
                                                        <span class="badge bg-warning">Today</span>
                                                    <?php endif; ?>
                                                </div>
                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center p-3">
                                        <p>No upcoming events this month</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card flex-grow-1">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h5><?php echo e(__('Calendar')); ?></h5>
                                        <input type="hidden" id="path_admin" value="<?php echo e(url('/')); ?>">
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if(isset($setting['is_enabled']) && $setting['is_enabled'] == 'on'): ?>
                                            <select class="form-control" name="calender_type" id="calender_type"
                                                style="float: right; width: 1px;" onchange="get_data()">
                                                <option value="local_calender" selected="true"><?php echo e(__('Local Calendar')); ?></option>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body " style="padding-top:0px;">
                                <div id='calendar'  class='calendar'></div>
                            </div>
                        </div>

                    </div>
                </div>


        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script src="<?php echo e(asset('assets/js/plugins/main.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/apexcharts.min.js')); ?>"></script>

    <?php if(Auth::user()->type == 'company' || Auth::user()->type == 'hr' || Auth::user()->type == 'Director'): ?>
    <script type="text/javascript">
        $(document).ready(function() {
            get_data();
        });

        function get_data() {
            var calender_type = $('#calender_type :selected').val();

            $('#calendar').removeClass('local_calender google_calender');
            if (!calender_type) {
                calender_type = 'local_calender';
            }
            $('#calendar').addClass(calender_type);

            $.ajax({
                data: {
                    "_token": "<?php echo e(csrf_token()); ?>",
                    'calender_type': calender_type
                },
                success: function(data) {
                    var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                        headerToolbar: {
                            left: 'prev', // Only navigation arrows
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
                    });
                    calendar.render();
                }
            });
        }
    </script>

    <?php else: ?>
        <script>
            $(document).ready(function() {
                get_data();
            });

            function get_data() {
                var calender_type = $('#calender_type :selected').val();

                $('#event_calendar').removeClass('local_calender');
                $('#event_calendar').removeClass('google_calender');
                if (calender_type == undefined) {
                    calender_type = 'local_calender';
                }
                $('#event_calendar').addClass(calender_type);

                $.ajax({
                    url: $("#path_admin").val() + "/event/get_event_data",
                    method: "POST",
                    data: {
                        "_token": "<?php echo e(csrf_token()); ?>",
                        'calender_type': calender_type
                    },
                    success: function(data) {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById('event_calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                timeGridDay: "<?php echo e(__('Day')); ?>",
                                timeGridWeek: "<?php echo e(__('Week')); ?>",
                                // dayGridMonth: "<?php echo e(__('Month')); ?>"
                            },
                            // slotLabelFormat: {
                            //     hour: '2-digit',
                            //     minute: '2-digit',
                            //     hour12: false,
                            // },
                            themeSystem: 'tailwind',
                            slotDuration: '00:10:00',
                            allDaySlot: true,
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            events: data,
                            height: '400px',
                            // timeFormat: 'H(:mm)',

                        });

                        calendar.render();
                    }
                });
            };
        </script>
    <?php endif; ?>

    <?php if(Auth::user()->type == 'company' || Auth::user()->type == 'hr' || Auth::user()->type == 'Director'): ?>
        <script>
            (function() {
                var totalEmployees = <?php echo e($totalEmployees); ?>;
                var presentEmployees = <?php echo e(count($presentEmployeesWithClockIn)); ?>;
                var attendancePercentage = <?php echo e(round($attendancePercentage, 2)); ?>;
                
                var options = {
                    series: [attendancePercentage],
                    chart: {
                        height: 380,
                        type: 'radialBar',
                        offsetY: -20,
                        sparkline: {
                            enabled: true
                        }
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -90,
                            endAngle: 90,
                            track: {
                                background: "#eef5ff",
                                strokeWidth: '98%',
                                margin: 5,
                            
                            },
                            dataLabels: {
                                name: {
                                    show: true
                                },
                                value: {
                                    offsetY: -50,
                                    fontSize: '20px'
                                }
                            }
                        }
                    },
                    grid: {
                        padding: {
                            top: -10
                        }
                    },
                    colors: ["#68A288"],
                    labels: [''],
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: function(val) {
                                return `Out of ${totalEmployees} employees, ${presentEmployees} are present.`;
                            }
                        }
                    }
                };

                var chart = new ApexCharts(document.querySelector("#attendance-chart"), options);
                chart.render();
            })();
        </script>

        <style>
            .apexcharts-tooltip {
                background: #000 !important;
                color: #fff !important;
                border-radius: 8px;
                font-size: 14px;
            }
        </style>
    <?php endif; ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('script-page'); ?>
    <script src="<?php echo e(asset('assets/js/plugins/apexcharts.min.js')); ?>"></script>
    <script>
        (function() {
            var options = {
                chart: {
                    height: 265,
                    type: 'bar',
                    toolbar: {
                        show: false,
                    },
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '50%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 4,
                    curve: 'smooth'
                },
                series: <?php echo json_encode($chartData['data']); ?>,
                xaxis: {
                    categories: <?php echo json_encode($chartData['labels']); ?>,
                },
                colors: ['#b4d1c4', '#68a288'],
                fill: {
                    type: 'solid',
                },
                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                },
                markers: {
                    size: 4,
                    colors: ['#000', '#FF3A6E'],
                    opacity: 2.5,
                    strokeWidth: 4,
                    hover: {
                        size: 8,
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#income-expense-chart"), options);
            chart.render();
        })();
    </script>

    <script>
        $(document).ready(function() {
            // Handle date filter dropdown selection - only for items with data-value attribute
            $('.dropdown-item[data-value]').on('click', function(e) {
                e.preventDefault();
                const filterType = $(this).data('value');
                $('#dateFilterButton').text($(this).text());
                
                if (filterType === 'custom') {
                    $('#customDatePicker').show().focus();
                } else {
                    $('#customDatePicker').hide();
                    loadDashboardData(filterType);
                }
            });
            
            // Handle custom date selection
            $('#customDatePicker').on('change', function() {
                const selectedDate = $(this).val();
                if (selectedDate) {
                    loadDashboardData('custom', selectedDate);
                }
            });
            
            function loadDashboardData(filterType, customDate = null) {
                let url = '<?php echo e(route("dashboard.filter")); ?>';
                let data = {
                    _token: '<?php echo e(csrf_token()); ?>',
                    filter_type: filterType
                };
                
                if (filterType === 'custom' && customDate) {
                    data.custom_date = customDate;
                }
                
                $('.card-body').addClass('loading');
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            $('#todayEnquiryCount').text(response.todayEnquiryCount);
                            
                            updateTable('#attendanceTable tbody', response.presentEmployeesWithClockIn, 'attendance');
                            updateLeaveAbsentTable(response.notClockIns, response.employeesNotWorkingToday);
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        alert('Error loading dashboard data');
                    },
                    complete: function() {
                        $('.card-body').removeClass('loading');
                    }
                });
            }

            function updateTable(tableSelector, data, tableType) {
                const $table = $(tableSelector);
                $table.empty();
                
                if (data.length === 0) {
                    let noDataText = 'No attendance records found.';
                    let colspan = 2;
                    
                    $table.append('<tr><td colspan="' + colspan + '">' + noDataText + '</td></tr>');
                    return;
                }
                
                data.forEach(function(item) {
                    let row = `<tr>
                        <td>${item.employee ? item.employee.name : 'N/A'}</td>
                        <td>${item.clock_in || '--:--'}</td>
                    </tr>`;
                    $table.append(row);
                });
            }

            function updateLeaveAbsentTable(absentEmployees, leaveEmployees) {
                const $tableBody = $('#leaveAbsentTable tbody');
                $tableBody.empty();
                
                // Check if both are empty
                if (absentEmployees.length === 0 && leaveEmployees.length === 0) {
                    $tableBody.append('<tr><td colspan="2">All employees are present</td></tr>');
                    return;
                }
                
                // Add absent employees
                absentEmployees.forEach(function(employee) {
                    const row = `<tr>
                        <td>${employee.name || 'N/A'}</td>
                        <td><span class="badge bg-danger">Absent</span></td>
                    </tr>`;
                    $tableBody.append(row);
                });
                
                // Add leave employees
                leaveEmployees.forEach(function(employee) {
                    const row = `<tr>
                        <td>${employee.employee_name}</td>
                        <td><span class="badge bg-warning">${employee.status}</span></td>
                    </tr>`;
                    $tableBody.append(row);
                });
            }

            
            function formatDateRange(start, end) {
                try {
                    const startDate = new Date(start);
                    const endDate = new Date(end);
                    return `${startDate.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })}`;
                } catch (e) {
                    console.error('Error formatting date:', e);
                    return '--';
                }
            }
        });
    </script>

    <script>
        // Update current time display
        document.addEventListener("DOMContentLoaded", function () {
            let currentTimeElement = document.getElementById("currentDateTime");
            
            function updateTimeDisplay() {
                if (currentTimeElement) {
                    let now = new Date();
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
                    currentTimeElement.textContent = `${month} ${day}, ${year}, ${hours}:${minutes}:${seconds} ${ampm}`;
                }
            }

            // Initialize time display and update every second
            updateTimeDisplay();
            setInterval(updateTimeDisplay, 1000);
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/dashboard/company.blade.php ENDPATH**/ ?>