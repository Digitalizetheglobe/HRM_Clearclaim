<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Expenses & Reimbursement Management')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Expenses Management')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <?php if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin'): ?>
        <a href="<?php echo e(route('expense-categories.index')); ?>" class="btn btn-sm btn-primary">
            <i class="ti ti-tag"></i> <?php echo e(__('Manage Categories')); ?>

        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-12 mb-3">
        <div class="row">
            <?php if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin'])): ?>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0"><?php echo e(__('Pending Approvals')); ?></h6>
                        <h3 class="mb-0 text-warning"><?php echo e($stats['total_pending']); ?></h3>
                        <small class="text-muted"><?php echo e(__('Amount: ') . Auth::user()->priceFormat($stats['pending_amount'])); ?></small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0"><?php echo e(__('Finance Pending')); ?></h6>
                        <h3 class="mb-0 text-primary"><?php echo e($stats['total_finance_pending']); ?></h3>
                        <small class="text-muted"><?php echo e(__('Amount: ') . Auth::user()->priceFormat($stats['finance_pending_amount'])); ?></small>
                    </div>
                </div>
            </div>
            <?php if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin'])): ?>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0"><?php echo e(__('Paid')); ?></h6>
                        <h3 class="mb-0 text-success"><?php echo e($stats['total_paid']); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0"><?php echo e(__('Rejected')); ?></h6>
                        <h3 class="mb-0 text-danger"><?php echo e($stats['total_rejected']); ?></h3>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pending Approvals Tab -->
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <?php if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(!$isFinanceEmployee ? 'active' : ''); ?>" data-bs-toggle="tab" href="#pending" role="tab">
                            <?php echo e(__('Pending Approvals')); ?> <span class="badge bg-warning"><?php echo e($pending->count()); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($isFinanceEmployee ? 'active' : ''); ?>" data-bs-toggle="tab" href="#finance" role="tab">
                            <?php echo e(__('Finance Pending')); ?> <span class="badge bg-primary"><?php echo e($financePending->count()); ?></span>
                        </a>
                    </li>
                    <?php if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#paid" role="tab">
                            <?php echo e(__('Paid')); ?> <span class="badge bg-success"><?php echo e($paid->count()); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#rejected" role="tab">
                            <?php echo e(__('Rejected')); ?> <span class="badge bg-danger"><?php echo e($rejected->count()); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Pending Tab -->
                    <?php if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin'])): ?>
                    <div class="tab-pane fade <?php echo e(!$isFinanceEmployee ? 'show active' : ''); ?>" id="pending" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Employee')); ?></th>
                                        <th><?php echo e(__('Category')); ?></th>
                                        <th><?php echo e(__('Amount')); ?></th>
                                        <th><?php echo e(__('Expense Date')); ?></th>
                                        <th><?php echo e(__('Submitted At')); ?></th>
                                        <th><?php echo e(__('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $pending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($expense->employee->name ?? '-'); ?></td>
                                            <td><?php echo e($expense->category->name ?? '-'); ?></td>
                                            <td><strong><?php echo e(Auth::user()->priceFormat($expense->amount)); ?></strong></td>
                                            <td><?php echo e(\Carbon\Carbon::parse($expense->expense_date)->format('d M Y')); ?></td>
                                            <td><?php echo e($expense->submitted_at ? \Carbon\Carbon::parse($expense->submitted_at)->format('d M Y H:i') : '-'); ?></td>
                                            <td>
                                                <a href="<?php echo e(route('expenses.show', $expense->id)); ?>" 
                                                   class="btn btn-sm btn-primary text-white" title="<?php echo e(__('Review & Approve')); ?>">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <?php if(in_array(Auth::user()->type, ['company', 'super admin'])): ?>
                                                <form action="<?php echo e(route('expenses.destroy', $expense->id)); ?>" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('<?php echo e(__('Are you sure you want to delete this expense? This action cannot be undone.')); ?>');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger text-white" title="<?php echo e(__('Delete')); ?>">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div class="py-4">
                                                    <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                                    <p class="mt-2 text-muted"><?php echo e(__('No pending expense requests found.')); ?></p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Finance Pending Tab -->
                    <div class="tab-pane fade <?php echo e($isFinanceEmployee ? 'show active' : ''); ?>" id="finance" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table" id="pc-dt-simple-2">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Employee')); ?></th>
                                        <th><?php echo e(__('Category')); ?></th>
                                        <th><?php echo e(__('Amount')); ?></th>
                                        <th><?php echo e(__('Approved At')); ?></th>
                                        <th><?php echo e(__('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $financePending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($expense->employee->name ?? '-'); ?></td>
                                            <td><?php echo e($expense->category->name ?? '-'); ?></td>
                                            <td><strong><?php echo e(Auth::user()->priceFormat($expense->amount)); ?></strong></td>
                                            <td><?php echo e($expense->hr_approved_at ? \Carbon\Carbon::parse($expense->hr_approved_at)->format('d M Y H:i') : '-'); ?></td>
                                            <td>
                                                <a href="<?php echo e(route('expenses.show', $expense->id)); ?>" 
                                                   class="btn btn-sm btn-primary text-white">
                                                    <i class="ti ti-credit-card"></i> <?php echo e(__('Process Payment')); ?>

                                                </a>
                                                <?php if(in_array(Auth::user()->type, ['company', 'super admin'])): ?>
                                                <form action="<?php echo e(route('expenses.destroy', $expense->id)); ?>" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('<?php echo e(__('Are you sure you want to delete this expense? This action cannot be undone.')); ?>');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger text-white" title="<?php echo e(__('Delete')); ?>">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <div class="py-4">
                                                    <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                                    <p class="mt-2 text-muted"><?php echo e(__('No expenses pending finance processing.')); ?></p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paid Tab -->
                    <?php if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin'])): ?>
                    <div class="tab-pane fade" id="paid" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table" id="pc-dt-simple-4">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Employee')); ?></th>
                                        <th><?php echo e(__('Category')); ?></th>
                                        <th><?php echo e(__('Amount')); ?></th>
                                        <th><?php echo e(__('Paid Date')); ?></th>
                                        <th><?php echo e(__('Payment Mode')); ?></th>
                                        <th><?php echo e(__('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $paid; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($expense->employee->name ?? '-'); ?></td>
                                            <td><?php echo e($expense->category->name ?? '-'); ?></td>
                                            <td><strong><?php echo e(Auth::user()->priceFormat($expense->amount)); ?></strong></td>
                                            <td><?php echo e($expense->paid_date ? \Carbon\Carbon::parse($expense->paid_date)->format('d M Y') : '-'); ?></td>
                                            <td><?php echo e($expense->payment_mode ? ucfirst($expense->payment_mode) : '-'); ?></td>
                                            <td>
                                                <a href="<?php echo e(route('expenses.show', $expense->id)); ?>" 
                                                   class="btn btn-sm btn-info text-white">
                                                    <i class="ti ti-eye"></i> <?php echo e(__('View')); ?>

                                                </a>
                                                <?php if(in_array(Auth::user()->type, ['company', 'super admin'])): ?>
                                                <form action="<?php echo e(route('expenses.destroy', $expense->id)); ?>" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('<?php echo e(__('Are you sure you want to delete this expense? This action cannot be undone.')); ?>');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger text-white" title="<?php echo e(__('Delete')); ?>">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div class="py-4">
                                                    <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                                    <p class="mt-2 text-muted"><?php echo e(__('No paid expenses found.')); ?></p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Rejected Tab -->
                    <?php if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin'])): ?>
                    <div class="tab-pane fade" id="rejected" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table" id="pc-dt-simple-3">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Employee')); ?></th>
                                        <th><?php echo e(__('Category')); ?></th>
                                        <th><?php echo e(__('Amount')); ?></th>
                                        <th><?php echo e(__('Rejected At')); ?></th>
                                        <th><?php echo e(__('Rejection Reason')); ?></th>
                                        <th><?php echo e(__('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $rejected; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($expense->employee->name ?? '-'); ?></td>
                                            <td><?php echo e($expense->category->name ?? '-'); ?></td>
                                            <td><strong><?php echo e(Auth::user()->priceFormat($expense->amount)); ?></strong></td>
                                            <td><?php echo e($expense->hr_approved_at ? \Carbon\Carbon::parse($expense->hr_approved_at)->format('d M Y H:i') : '-'); ?></td>
                                            <td><?php echo e(Str::limit($expense->hr_remark ?? '-', 50)); ?></td>
                                            <td>
                                                <a href="<?php echo e(route('expenses.show', $expense->id)); ?>" 
                                                   class="btn btn-sm btn-info text-white">
                                                    <i class="ti ti-eye"></i> <?php echo e(__('View')); ?>

                                                </a>
                                                <?php if(in_array(Auth::user()->type, ['company', 'super admin'])): ?>
                                                <form action="<?php echo e(route('expenses.destroy', $expense->id)); ?>" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('<?php echo e(__('Are you sure you want to delete this expense? This action cannot be undone.')); ?>');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger text-white" title="<?php echo e(__('Delete')); ?>">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div class="py-4">
                                                    <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                                    <p class="mt-2 text-muted"><?php echo e(__('No rejected expenses found.')); ?></p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/expenses/hr/index.blade.php ENDPATH**/ ?>