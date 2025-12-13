

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('HR Approved Expenses')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('hr.expenses.index')); ?>"><?php echo e(__('HR Approvals')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Approved')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <div class="float-end">
        <a href="<?php echo e(route('hr.expenses.index')); ?>" class="btn btn-sm btn-primary">
            <i class="ti ti-arrow-left"></i> <?php echo e(__('Back to Pending')); ?>

        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5><?php echo e(__('Approved Expenses')); ?></h5>
                <small class="text-muted"><?php echo e(__('Total Approved: ') . $expenses->count()); ?></small>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Employee')); ?></th>
                                <th><?php echo e(__('Category')); ?></th>
                                <th><?php echo e(__('Amount')); ?></th>
                                <th><?php echo e(__('Expense Date')); ?></th>
                                <th><?php echo e(__('Approved At')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($expense->employee->name ?? '-'); ?></td>
                                    <td><?php echo e($expense->category->name ?? '-'); ?></td>
                                    <td><strong><?php echo e(Auth::user()->priceFormat($expense->amount)); ?></strong></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($expense->expense_date)->format('d M Y')); ?></td>
                                    <td><?php echo e($expense->hr_approved_at ? \Carbon\Carbon::parse($expense->hr_approved_at)->format('d M Y H:i') : '-'); ?></td>
                                    <td><?php echo $expense->status_badge; ?></td>
                                    <td>
                                        <a href="<?php echo e(route('hr.expenses.show', $expense->id)); ?>" 
                                           class="btn btn-sm btn-info text-white">
                                            <i class="ti ti-eye"></i> <?php echo e(__('View')); ?>

                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="py-4">
                                            <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                            <p class="mt-2 text-muted"><?php echo e(__('No approved expenses found.')); ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/expenses/hr/approved.blade.php ENDPATH**/ ?>