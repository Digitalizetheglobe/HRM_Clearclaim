<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-success">
                <h5 class="mb-3"><i class="ti ti-check-circle me-2"></i><?php echo e(__('Onboarding Completed!')); ?></h5>
                <p><?php echo e(__('Congratulations! The employee has successfully completed all onboarding steps.')); ?></p>
            </div>
        </div>
        <div class="col-md-12">
            <h6><?php echo e(__('Employee Information')); ?></h6>
            <table class="table table-bordered">
                <tr>
                    <th><?php echo e(__('Name')); ?></th>
                    <td><?php echo e($process->employee->name ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Email')); ?></th>
                    <td><?php echo e($process->employee->email ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Completed At')); ?></th>
                    <td><?php echo e($process->onboarding_completed_at ? \Auth::user()->dateFormat($process->onboarding_completed_at) : 'N/A'); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
</div>

<?php /**PATH D:\HRM_Clearclaim\resources\views/onboarding/steps/step_7.blade.php ENDPATH**/ ?>