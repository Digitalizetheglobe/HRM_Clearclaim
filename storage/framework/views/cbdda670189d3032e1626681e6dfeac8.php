

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('My Expenses & Reimbursement')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('My Expenses')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <a href="<?php echo e(route('expenses.create')); ?>" class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i> <?php echo e(__('Add New Expense')); ?>

    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0"><?php echo e(__('Total Reimbursed')); ?></h6>
                        <h3 class="mb-0 text-success"><?php echo e(Auth::user()->priceFormat($totalReimbursed)); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0"><?php echo e(__('Pending Amount')); ?></h6>
                        <h3 class="mb-0 text-warning"><?php echo e(Auth::user()->priceFormat($pendingAmount)); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0"><?php echo e(__('Total Expenses')); ?></h6>
                        <h3 class="mb-0 text-primary"><?php echo e($expenses->count()); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Date')); ?></th>
                                <th><?php echo e(__('Category')); ?></th>
                                <th><?php echo e(__('Amount')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(\Carbon\Carbon::parse($expense->expense_date)->format('d M Y')); ?></td>
                                    <td><?php echo e($expense->category->name ?? '-'); ?></td>
                                    <td><?php echo e(Auth::user()->priceFormat($expense->amount)); ?></td>
                                    <td><?php echo $expense->status_badge; ?></td>
                                    <td>
                                        <a href="<?php echo e(route('expenses.show', $expense->id)); ?>" 
                                           class="btn btn-sm btn-info text-white" 
                                           data-bs-toggle="tooltip" 
                                           title="<?php echo e(__('View Details')); ?>">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center"><?php echo e(__('No expenses found.')); ?></td>
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










<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/expenses/employee/index.blade.php ENDPATH**/ ?>