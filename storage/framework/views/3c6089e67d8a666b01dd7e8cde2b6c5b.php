<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Attendance List')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Attendance List')); ?></li>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('script-page'); ?>
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
                url: '<?php echo e(route('monthly.getdepartment')); ?>',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "<?php echo e(csrf_token()); ?>",
                },
                success: function(data) {

                    $('.department_id').empty();
                    var emp_selct = `<select class="form-control department_id" name="department_id" id="choices-multiple"
                                            placeholder="Select Department" >
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
            
            var routeUrl = '<?php echo e(route("attendance.processMissingPunchOuts")); ?>';
            var token = '<?php echo e(csrf_token()); ?>';
            
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
<?php $__env->stopPush(); ?>
<?php $__env->startSection('action-button'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php if(session('status')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo session('   '); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        <?php echo e(Form::open(['route' => ['attendanceemployee.index'], 'method' => 'get', 'id' => 'attendanceemployee_filter'])); ?>

                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">

                                    <div class="col-3">
                                        <label class="form-label"><?php echo e(__('Type')); ?></label> <br>

                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="monthly" value="monthly" name="type"
                                                class="form-check-input"
                                                <?php echo e(isset($_GET['type']) && $_GET['type'] == 'monthly' ? 'checked' : 'checked'); ?>>
                                            <label class="form-check-label" for="monthly"><?php echo e(__('Monthly')); ?></label>
                                        </div>
                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="daily" value="daily" name="type"
                                                class="form-check-input"
                                                <?php echo e(isset($_GET['type']) && $_GET['type'] == 'daily' ? 'checked' : ''); ?>>
                                            <label class="form-check-label" for="daily"><?php echo e(__('Daily')); ?></label>
                                        </div>

                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('month', __('Month'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'month-btn form-control month-btn'])); ?>

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('date', __('Date'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Form::date('date', isset($_GET['date']) ? $_GET['date'] : '', ['class' => 'form-control month-btn'])); ?>

                                        </div>
                                    </div>
                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                <?php echo e(Form::label('branch', __('Branch'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control select branch_id', 'id' => 'branch_id'])); ?>

                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                <?php echo e(Form::label('department', __('department'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control select department_id', 'id' => 'department_id'])); ?>

                                            </div>

                                            
                                            

                                        </div>
                                        
                                    <?php endif; ?>

                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">

                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('attendanceemployee_filter').submit(); return false;"
                                            data-bs-toggle="tooltip" title="<?php echo e(__('Apply')); ?>"
                                            data-original-title="<?php echo e(__('apply')); ?>">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>

                                        <a href="<?php echo e(route('attendanceemployee.index')); ?>" class="btn btn-sm btn-danger "
                                            data-bs-toggle="tooltip" title="<?php echo e(__('Reset')); ?>"
                                            data-original-title="<?php echo e(__('Reset')); ?>">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off "></i></span>
                                        </a>

                                        <a href="#" data-url="<?php echo e(route('attendance.file.import')); ?>"
                                            data-ajax-popup="true" data-title="<?php echo e(__('Import  Attendance CSV File')); ?>"
                                            data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                            data-bs-original-title="<?php echo e(__('Import')); ?>">
                                            <i class="ti ti-file"></i>
                                        </a>

                                        <a href="<?php echo e(route('attendance.export', request()->query())); ?>" class="btn btn-sm btn-primary" 
                                            data-bs-toggle="tooltip" title="<?php echo e(__('Export')); ?>" 
                                            data-bs-original-title="<?php echo e(__('Export')); ?>">
                                            <i class="ti ti-download"></i>
                                        </a>

                                        <?php if(\Auth::user()->type != 'employee'): ?>
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                id="processMissingPunchOutsBtn"
                                                onclick="return processMissingPunchOuts();"
                                                data-bs-toggle="tooltip" 
                                                title="<?php echo e(__('Process Missing Punch Outs')); ?>" 
                                                data-bs-original-title="<?php echo e(__('Process Missing Punch Outs')); ?>">
                                                <i class="ti ti-clock"></i> <?php echo e(__('Process Missing Punch Outs')); ?>

                                            </button>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php echo e(Form::close()); ?>

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
                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                        <th><?php echo e(__('Employee')); ?></th>
                                    <?php endif; ?>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <th><?php echo e(__('Clock-In Time')); ?></th>
                                    <th><?php echo e(__('Clock-Out Time')); ?></th>
                                    <th><?php echo e(__('Total Hours')); ?></th>
                                    <th><?php echo e(__('Difference')); ?></th>
                                    <?php if(Gate::check('Edit Attendance') || Gate::check('Delete Attendance')): ?>
                                        <th width="200px"><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $attendanceEmployee; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
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
                                    ?>
                                    <tr>
                                        <?php if(\Auth::user()->type != 'employee'): ?>
                                            <td><?php echo e(!empty($attendance->employee) ? $attendance->employee->name : ''); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo e(\Auth::user()->dateFormat($attendance->date)); ?></td>
                                        <td><?php echo e($attendance->status); ?></td>
                                        <td><?php echo e($attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00'); ?>

                                        </td>
                                        <td><?php echo e($attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00'); ?>

                                        </td>
                                        <td><?php echo e($totalHoursFormatted); ?></td>
                                        <td><?php echo e($differenceFormatted); ?></td>
                                        <?php if(Gate::check('Edit Attendance') || Gate::check('Delete Attendance')): ?>
                                            <td class="Action">
                                                <div class="d-flex align-items-center justify-content-start">
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit Attendance')): ?>
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="btn btn-sm d-flex align-items-center justify-content-center"
                                                                data-size="lg"
                                                                data-url="<?php echo e(URL::to('attendanceemployee/' . $attendance->id . '/edit')); ?>"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="<?php echo e(__('Edit Attendance')); ?>"
                                                                data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>


                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete Attendance')): ?>
                                                        <div class="action-btn bg-danger ms-2">
                                                            <?php echo Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['attendanceemployee.destroy', $attendance->id],
                                                                'id' => 'delete-form-' . $attendance->id,
                                                                'class' => 'd-inline-block',
                                                                'style' => 'margin: 0;'
                                                            ]); ?>

                                                            <a href="#"
                                                                class="btn btn-sm d-flex align-items-center justify-content-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                            <?php echo Form::close(); ?>

                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
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
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/attendance/index.blade.php ENDPATH**/ ?>