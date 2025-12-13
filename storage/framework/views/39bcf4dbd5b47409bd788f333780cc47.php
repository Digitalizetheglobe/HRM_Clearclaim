<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Expense Details')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('expenses.index')); ?>"><?php echo e(__('My Expenses')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Expense Details')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5><?php echo e(__('Expense Request Details')); ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%"><?php echo e(__('Category')); ?></th>
                                <td><?php echo e($expense->category->name ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Amount')); ?></th>
                                <td><strong><?php echo e(Auth::user()->priceFormat($expense->amount)); ?></strong></td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Expense Date')); ?></th>
                                <td><?php echo e(\Carbon\Carbon::parse($expense->expense_date)->format('d M Y')); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Submitted At')); ?></th>
                                <td><?php echo e($expense->submitted_at ? \Carbon\Carbon::parse($expense->submitted_at)->format('d M Y H:i') : '-'); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Status')); ?></th>
                                <td><?php echo $expense->status_badge; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <?php if($expense->hr_remark): ?>
                            <tr>
                                <th width="40%"><?php echo e(__('HR Remark')); ?></th>
                                <td><?php echo e($expense->hr_remark); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if($expense->paid_date): ?>
                            <tr>
                                <th><?php echo e(__('Paid Date')); ?></th>
                                <td><?php echo e(\Carbon\Carbon::parse($expense->paid_date)->format('d M Y')); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if($expense->payment_mode): ?>
                            <tr>
                                <th><?php echo e(__('Payment Mode')); ?></th>
                                <td><?php echo e(ucfirst($expense->payment_mode)); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-md-12">
                        <h6><?php echo e(__('Description')); ?></h6>
                        <p><?php echo e($expense->description ?? '-'); ?></p>
                    </div>
                    <?php if($expense->receipt_file && count($expense->receipt_file) > 0): ?>
                    <div class="col-md-12">
                        <h6><?php echo e(__('Receipt Files')); ?></h6>
                        <div class="row">
                            <?php
                                $receiptPath = \App\Models\Utility::get_file('uploads/expense_receipts/');
                            ?>
                            <?php $__currentLoopData = $expense->receipt_file; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-3 mb-2">
                                <a href="<?php echo e($receiptPath . $file); ?>" target="_blank" class="btn btn-sm btn-info">
                                    <i class="ti ti-download"></i> <?php echo e(__('View Receipt')); ?>

                                </a>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if($expense->payment_proof): ?>
                    <div class="col-md-12">
                        <h6><?php echo e(__('Payment Proof')); ?></h6>
                        <?php
                            $paymentPath = \App\Models\Utility::get_file('uploads/payment_proofs/');
                        ?>
                        <a href="<?php echo e($paymentPath . $expense->payment_proof); ?>" target="_blank" class="btn btn-sm btn-success">
                            <i class="ti ti-download"></i> <?php echo e(__('View Payment Proof')); ?>

                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/expenses/employee/show.blade.php ENDPATH**/ ?>