<?php echo e(Form::open(['url' => 'leave/changeaction', 'method' => 'post'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th><?php echo e(__('Employee')); ?></th>
                    <td><?php echo e(!empty($employee->name) ? $employee->name : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Appplied On')); ?></th>
                    <td><?php echo e(\Auth::user()->dateFormat($leave->applied_on)); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Start Date')); ?></th>
                    <td><?php echo e(\Auth::user()->dateFormat($leave->start_date)); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('End Date')); ?></th>
                    <td><?php echo e(\Auth::user()->dateFormat($leave->end_date)); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Leave Duration')); ?></th>
                    <td>
                        <?php echo e($leave->leave_duration ?? 'Full Day'); ?>

                        <?php if(($leave->leave_duration ?? '') == 'Half Day' && !empty($leave->leave_session)): ?>
                            (<?php echo e($leave->leave_session); ?>)
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php echo e(__('Total Days')); ?></th>
                    <td><?php echo e($leave->total_leave_days); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Leave Reason')); ?></th>
                    <td><?php echo e(!empty($leave->leave_reason) ? $leave->leave_reason : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Status')); ?></th>
                    <td><?php echo e(!empty($leave->status) ? $leave->status : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Payment Status')); ?></th>
                    <td>
                        <?php if($leave->is_lop ?? false): ?>
                            <span class="badge bg-danger">LOP (Loss of Pay)</span>
                        <?php elseif($leave->is_paid ?? true): ?>
                            <span class="badge bg-success">Paid Leave</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Unpaid</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <input type="hidden" value="<?php echo e($leave->id); ?>" name="leave_id">
            </table>
        </div>
    </div>
</div>

<?php if(Auth::user()->type == 'company' || Auth::user()->type == 'hr'): ?>
    <div class="modal-footer">
        <input type="submit" value="<?php echo e(__('Approved')); ?>" class="btn btn-success rounded" name="status">
        <input type="submit" value="<?php echo e(__('Reject')); ?>" class="btn btn-danger rounded" name="status">
    </div>
<?php endif; ?>

<?php echo e(Form::close()); ?>

<?php /**PATH D:\HRM_Clearclaim\resources\views/leave/action.blade.php ENDPATH**/ ?>