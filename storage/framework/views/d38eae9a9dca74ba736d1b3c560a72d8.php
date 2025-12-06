

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Monthly Attendance')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Manage Monthly Attendance Report')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('action-button'); ?>

    <?php
        $emp = isset($_GET['employee_id']) && !empty($_GET['employee_id']) ? $_GET['employee_id'] : [];
        $employees = implode(', ', $emp);
    ?>

    <a href="#" class="btn btn-sm btn-primary" onclick="exportToExcel()" data-bs-toggle="tooltip" title="<?php echo e(__('Export')); ?>"
        data-original-title="<?php echo e(__('Export')); ?>">
        <span class="btn-inner--icon"><i class="ti ti-file-export"></i></span>
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <!-- ... other buttons ... -->

    
    <a href="#" id="exportBtn" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="<?php echo e(__('Export to Excel')); ?>">
        <span class="btn-inner--icon"><i class="ti ti-file-export"></i></span> <?php echo e(__('Export to Excel')); ?>

    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <!-- Include required libraries -->
    <script type="text/javascript" src="<?php echo e(asset('js/html2pdf.bundle.min.js')); ?>"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>

    <script>
        var filename = $('#filename').val();

        // PDF Export function (existing)
        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(element).save();
        }

        // New Excel Export function with error handling
        function exportToExcel() {
            // Use the displayed month, not just the filter input
            var displayedMonth = $('#displayed_month').val();
            if (!displayedMonth) {
                alert('No month selected');
                return;
            }
            try {
                // Get other filter values
                var branch = $('#branch_id').val() || 0;
                var department = $('#department_id').val() || 0;
                var employee = $('#employee_id').val() ? $('#employee_id').val().join(',') : 0;
                // Build the export URL
                var url = "<?php echo e(route('report.attendance', ['month' => 'MONTH', 'branch' => 'BRANCH', 'department' => 'DEPARTMENT', 'employee' => 'EMPLOYEE'])); ?>";
                url = url.replace('MONTH', encodeURIComponent(displayedMonth));
                url = url.replace('BRANCH', branch);
                url = url.replace('DEPARTMENT', department);
                url = url.replace('EMPLOYEE', employee);
                window.location.href = url;
            } catch (error) {
                console.error('Export error:', error);
                alert('Error during export: ' + error.message);
            }
        }

        // Initialize button click event
        $(document).ready(function() {
            $('#exportBtn').on('click', function(e) {
                e.preventDefault();
                exportToExcel();
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        <?php echo e(Form::open(['route' => ['report.monthly.attendance'], 'method' => 'get', 'id' => 'report_monthly_attendance'])); ?>

                        <!-- Add hidden input for displayed month -->
                        <input type="hidden" id="displayed_month" value="<?php echo e(isset($_GET['month']) ? $_GET['month'] : \Carbon\Carbon::now()->format('Y-m')); ?>">
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('month', __(' Month'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Form::month('month', isset($_GET['month']) ? $_GET['month'] : '', ['class' => 'month-btn form-control current_date', 'autocomplete' => 'off', 'placeholder' => 'Select month'])); ?>                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('branch', __('Branch'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Form::select('branch_id', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control select branch_id', 'id' => 'branch-select branch_id'])); ?>

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box" id="department_div">
                                            <?php echo e(Form::label('department', __('Department'), ['class' => 'form-label'])); ?>

                                            <select class="form-control select department_id" name="department"
                                                id="department_id" placeholder="Select Department">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box" id="employee_div">
                                            <?php echo e(Form::label('employee', __('Employee'), ['class' => 'form-label'])); ?>

                                            <select class="form-control select" name="employee_id[]" id="employee_id"
                                                placeholder="Select Employee">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('report_monthly_attendance').submit(); return false;"
                                            data-bs-toggle="tooltip" title="<?php echo e(__('Apply')); ?>"
                                            data-original-title="<?php echo e(__('apply')); ?>">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="<?php echo e(route('report.monthly.attendance')); ?>" class="btn btn-sm btn-danger "
                                            data-bs-toggle="tooltip" title="<?php echo e(__('Reset')); ?>"
                                            data-original-title="<?php echo e(__('Reset')); ?>">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php echo e(Form::close()); ?>

                </div>
            </div>
        </div>
    </div>

   

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <!-- Left Side: Status Indicators -->
                        <div class="d-flex align-items-center flex-wrap">
                            <div class="me-3 d-flex align-items-center">
                                <div class="color-indicator present me-2"></div>
                                <span>Present</span>
                            </div>
                            <div class="me-3 d-flex align-items-center">
                                <div class="color-indicator absent me-2"></div>
                                <span>Absent</span>
                            </div>
                            <div class="me-3 d-flex align-items-center">
                                <div class="color-indicator leave me-2"></div>
                                <span>Leave</span>
                            </div>
                        </div>

                        <!-- Right Side: Selected Month Info -->
                        <div class="text-end fw-bold">
                            Selected Attendance Month :
                            <span class="">
                                <?php echo e(isset($_GET['month']) ? \Carbon\Carbon::parse($_GET['month'])->format('M Y') : \Carbon\Carbon::now()->format('M Y')); ?>

                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive py-4 attendance-table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="active"><?php echo e(__('Name')); ?></th>
                                        <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $dateInfo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $date = $key;
                                                $day = $dateInfo['day'];
                                            ?>
                                                <th class="day-header">
                                                    <div class="date-number"><?php echo e($date); ?></div>
                                                    <div class="day-abbr"><?php echo e($day); ?></div>
                                                </th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $employeesAttendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                            <td><?php echo e($employee['name']); ?></td>
                                            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $dateInfo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $date = $key; // '01', '02', etc.
                                                    $dateFormat = $dateInfo['full_date']; // e.g., "2025-07-01"
                                                    $dayOfWeek = \Carbon\Carbon::parse($dateFormat)->format('l'); // "Monday", etc.

                                                    $statusEntry = $employee['status'][$date] ?? null;  // controller passed 'P', 'A', etc.
                                                ?>

                                                <td>
                                                    <?php if($dateFormat > date('Y-m-d')): ?>
                                                        
                                                    <?php elseif($statusEntry && $statusEntry['status'] == 'L'): ?>
                                                        <span class="badge bg-warning p-2 hexagon" title="Leave: <?php echo e($statusEntry['type'] ?? ''); ?>"> </span>
                                                    <?php elseif($statusEntry && $statusEntry['status'] == 'P'): ?>
                                                        <span class="badge bg-success p-2 triangle"> </span>
                                                    <?php elseif($statusEntry && $statusEntry['status'] == 'A'): ?>
                                                        <span class="badge bg-danger p-2 square"> </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger p-2 square"> </span>
                                                    <?php endif; ?>
                                                </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).ready(function() {
            var b_id = $('#branch_id').val();
            // getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch_id]', function() {
            var branch_id = $(this).val();

            getDepartment(branch_id);
        });

        function getDepartment(bid) {

            $.ajax({
                url: '<?php echo e(route('monthly.getdepartment')); ?>',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "<?php echo e(csrf_token()); ?>",
                },
                success: function(data) {

                    $('.department_id').empty();
                    var emp_selct = `<select class="department_id form-control multi-select" id="choices-multiple" multiple="" required="required" name="department_id[]">
                </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value=""> <?php echo e(__('Select Department')); ?> </option>');
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

        $(document).on('change', '.department_id', function() {
            var department_id = $(this).val();
            getEmployee(department_id);
        });

        function getEmployee(did) {

            $.ajax({
                url: '<?php echo e(route('monthly.getemployee')); ?>',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "<?php echo e(csrf_token()); ?>",
                },
                success: function(data) {

                    $('#employee_id').empty();

                    $("#employee_div").html('');
                    // $('#employee_div').append('<select class="form-control" id="employee_id" name="employee_id[]"  multiple></select>');
                    $('#employee_div').append(
                        '<label for="employee" class="form-label"><?php echo e(__('Employee')); ?></label><select class="form-control" id="employee_id" name="employee_id[]"  multiple></select>'
                    );

                    $('#employee_id').append('<option value=""><?php echo e(__('Select Employee')); ?></option>');

                    $.each(data, function(key, value) {
                        $('#employee_id').append('<option value="' + key + '">' + value + '</option>');
                    });

                    var multipleCancelButton = new Choices('#employee_id', {
                        removeItemButton: true,
                    });
                }
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            var now = new Date();
            var month = (now.getMonth() + 1);
            if (month < 10) month = "0" + month;
            var today = now.getFullYear() + '-' + month;
            $('.current_date').val(today);
        });
    </script>

    <style>
        .color-indicator {
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }

        .color-indicator.present {
            background-color: #65d943;  /* green */
        }

        .color-indicator.absent {
            background-color: #ff3a63;  /* red */
        }

        .color-indicator.week-off-removed {
            background-color: #3ec9d6;  /* gray */
        }

        .color-indicator.leave {
            background-color: #ffa21d;  /* yellow/orange */
        }

    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\TEST_time\resources\views/report/monthlyAttendance.blade.php ENDPATH**/ ?>