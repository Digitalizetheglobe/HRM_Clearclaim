<?php
    function breakAfterWords($text, $wordsPerLine = 3) {
        $words = explode(' ', $text);
        $lines = array_chunk($words, $wordsPerLine);
        return implode('<br>', array_map('implode', array_fill(0, count($lines), ' '), $lines));
    }
?>


<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Leave')); ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Leave ')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <a href="<?php echo e(route('leave.export')); ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="<?php echo e(__('Export')); ?>">
        <i class="ti ti-file-export"></i>
    </a>


    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Create Leave')): ?>
        <a href="#" data-url="<?php echo e(route('leave.create')); ?>" data-ajax-popup="true"
            data-title="<?php echo e(__('Create New Leave')); ?>" data-size="lg" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="<?php echo e(__('Create')); ?>">
            <i class="ti ti-plus"></i>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php if(\Auth::user()->type == 'employee' && isset($leaveBalance)): ?>
        <div class="row mb-4">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo e(__('Leave Balance Summary')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="text-white mb-2"><?php echo e(__('Total Year Leaves')); ?></h6>
                                        <h3 class="mb-0"><?php echo e(number_format($leaveBalance['total_year_leaves'], 2)); ?></h3>
                                        <small class="text-white-50"><?php echo e(__('Pro-rata entitlement')); ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="text-white mb-2"><?php echo e(__('This Month Leaves')); ?></h6>
                                        <h3 class="mb-0"><?php echo e(number_format($leaveBalance['monthly_limit'], 2)); ?></h3>
                                        <small class="text-white-50"><?php echo e(__('Monthly limit (paid)')); ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h6 class="text-white mb-2"><?php echo e(__('Total Monthly Used')); ?></h6>
                                        <h3 class="mb-0"><?php echo e(number_format($leaveBalance['this_month_paid_used'], 2)); ?></h3>
                                        <small class="text-white-50">
                                            <?php echo e(__('Used: ')); ?><?php echo e(number_format($leaveBalance['this_month_paid_used'], 2)); ?> / 
                                            <?php echo e(number_format($leaveBalance['monthly_limit'], 2)); ?> | 
                                            <?php echo e(__('Remaining: ')); ?><?php echo e(number_format($leaveBalance['remaining_paid_this_month'], 2)); ?>

                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="text-white mb-2"><?php echo e(__('Yearly Remaining')); ?></h6>
                                        <h3 class="mb-0"><?php echo e(number_format($leaveBalance['yearly_remaining'], 2)); ?></h3>
                                        <small class="text-white-50"><?php echo e(__('Available balance')); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if($leaveBalance['this_month_paid_used'] >= $leaveBalance['monthly_limit']): ?>
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="ti ti-alert-triangle"></i> 
                                <strong><?php echo e(__('Notice:')); ?></strong> 
                                <?php echo e(__('You have used all '.$leaveBalance['monthly_limit'].' paid leaves for this month. Any additional leaves will be Leave Without Pay (LWP).')); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
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
                                    <th><?php echo e(__('Applied On')); ?></th>
                                    <th><?php echo e(__('Start Date')); ?></th>
                                    <th><?php echo e(__('End Date')); ?></th>
                                    <th><?php echo e(__('Duration')); ?></th>
                                    <th><?php echo e(__('Total Days')); ?></th>
                                    <th><?php echo e(__('Leave Type')); ?></th>
                                    <th><?php echo e(__('Leave Reason')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                        <th width="200px"><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>    
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <?php if(\Auth::user()->type != 'employee'): ?>
                                            <td><?php echo e(!empty($leave->employee_id) ? $leave->employees->name : ''); ?>

                                            </td>
                                        <?php endif; ?>
                                        <td><?php echo e(\Auth::user()->dateFormat($leave->applied_on)); ?></td>
                                        <td><?php echo e(\Auth::user()->dateFormat($leave->start_date)); ?></td>
                                        <td><?php echo e(\Auth::user()->dateFormat($leave->end_date)); ?></td>
                                        <td>
                                            <?php echo e($leave->leave_duration ?? 'Full Day'); ?>

                                            <?php if(($leave->leave_duration ?? '') == 'Half Day' && !empty($leave->leave_session)): ?>
                                                <br><small class="text-muted">(<?php echo e($leave->leave_session); ?>)</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($leave->total_leave_days); ?></td>
                                        <td>
                                            <?php if($leave->is_lop): ?>
                                                <span class="badge bg-danger"><?php echo e(__('LOP')); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?php echo e(__('Paid')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- <td><?php echo e($leave->leave_reason); ?></td> -->
                                        <td><?php echo breakAfterWords($leave->leave_reason); ?></td>
                                        <td>
                                            <?php if($leave->status == 'Pending'): ?>
                                                <div class="badge bg-warning p-2 px-3 rounded status-badge5">
                                                    <?php echo e($leave->status); ?></div>
                                            <?php elseif($leave->status == 'Approved'): ?>
                                                <div class="badge bg-success p-2 px-3 rounded status-badge5">
                                                    <?php echo e($leave->status); ?></div>
                                            <?php elseif($leave->status == 'Reject'): ?>
                                                <div class="badge bg-danger p-2 px-3 rounded status-badge5">
                                                    <?php echo e($leave->status); ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <?php if(\Auth::user()->type != 'employee'): ?>
                                            <td class="Action">

                                                <span>
                                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                                        <div class="action-btn bg-success ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                data-size="lg"
                                                                data-url="<?php echo e(URL::to('leave/' . $leave->id . '/action')); ?>"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="<?php echo e(__('Leave Action')); ?>"
                                                                data-bs-original-title="<?php echo e(__('Manage Leave')); ?>">
                                                                <i class="ti ti-caret-right text-white"></i>
                                                            </a>
                                                        </div>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit Leave')): ?>
                                                            <div class="action-btn bg-info ms-2">
                                                                <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                    data-size="lg"
                                                                    data-url="<?php echo e(URL::to('leave/' . $leave->id . '/edit')); ?>"
                                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                    title="" data-title="<?php echo e(__('Edit Leave')); ?>"
                                                                    data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete Leave')): ?>
                                                            <?php if(\Auth::user()->type != 'employee'): ?>
                                                                <div class="action-btn bg-danger ms-2">
                                                                    <?php echo Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['leave.destroy', $leave->id],
                                                                        'id' => 'delete-form-' . $leave->id,
                                                                    ]); ?>

                                                                    <a href="#"
                                                                        class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-bs-original-title="Delete" aria-label="Delete"><i
                                                                            class="ti ti-trash text-white text-white"></i></a>
                                                                    </form>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="action-btn bg-success ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                data-size="lg"
                                                                data-url="<?php echo e(URL::to('leave/' . $leave->id . '/action')); ?>"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="<?php echo e(__('Leave Action')); ?>"
                                                                data-bs-original-title="<?php echo e(__('Manage Leave')); ?>">
                                                                <i class="ti ti-caret-right text-white"></i>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>

                                                </span>
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
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).on('change', '#employee_id', function() {
            var employee_id = $(this).val();

            $.ajax({
                url: '<?php echo e(route('leave.jsoncount')); ?>',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "<?php echo e(csrf_token()); ?>",
                },
                success: function(data) {
                    var oldval = $('#leave_type_id').val();
                    $('#leave_type_id').empty();
                    $('#leave_type_id').append(
                        '<option value=""><?php echo e(__('Select Leave Type')); ?></option>');

                    $.each(data, function(key, value) {

                        if (value.total_leave == value.days) {
                            $('#leave_type_id').append('<option value="' + value.id +
                                '" disabled>' + value.title + '&nbsp(' + value.total_leave +
                                '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type_id').append('<option value="' + value.id + '">' +
                                value.title + '&nbsp(' + value.total_leave + '/' + value
                                .days + ')</option>');
                        }
                        if (oldval) {
                            if (oldval == value.id) {
                                $("#leave_type_id option[value=" + oldval + "]").attr(
                                    "selected", "selected");
                            }
                        }
                    });
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/leave/index.blade.php ENDPATH**/ ?>