<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Expense Details')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5><?php echo e(__('Expense Request Details')); ?></h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%"><?php echo e(__('Employee')); ?></th>
                                <td><?php echo e($expense->employee->name ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Category')); ?></th>
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
                        <?php
                            $receiptPath = \App\Models\Utility::get_file('uploads/expense_receipts/');
                        ?>
                        <div class="row">
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

                <!-- Action Buttons based on Status -->
                <?php
                    $user = Auth::user();
                    $employee = \App\Models\Employee::where('user_id', $user->id)->first();
                    $companyId = $user->creatorId();
                    $isAdmin = in_array($user->type, ['company', 'super admin']);
                    
                    // Find HR Department
                    $hrDepartment = \App\Models\Department::where('created_by', $companyId)
                        ->where(function($q) {
                            $q->whereRaw('LOWER(name) LIKE ?', ['%human resource%'])
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%hr%'])
                              ->orWhereRaw('LOWER(name) = ?', ['human resource'])
                              ->orWhereRaw('LOWER(name) = ?', ['hr']);
                        })
                        ->first();
                    $isHREmployee = $employee && $hrDepartment && $employee->department_id == $hrDepartment->id;
                    $canApproveReject = $isAdmin || $isHREmployee;
                ?>
                <?php if($expense->status == 'pending_hr' && $canApproveReject): ?>
                <div class="row">
                    <div class="col-md-6">
                        <form method="POST" action="<?php echo e(route('expenses.approve', $expense->id)); ?>" class="mb-3">
                            <?php echo csrf_field(); ?>
                            <div class="form-group mb-3">
                                <label><?php echo e(__('Approval Remark (Optional)')); ?></label>
                                <textarea name="remark" class="form-control" rows="3" placeholder="<?php echo e(__('Add any remarks...')); ?>"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check"></i> <?php echo e(__('Approve & Send to Finance')); ?>

                            </button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="POST" action="<?php echo e(route('expenses.reject', $expense->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="form-group mb-3">
                                <label><?php echo e(__('Rejection Reason')); ?> <span class="text-danger">*</span></label>
                                <textarea name="remark" class="form-control" rows="3" required placeholder="<?php echo e(__('Please provide reason for rejection...')); ?>"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger">
                                <i class="ti ti-x"></i> <?php echo e(__('Reject')); ?>

                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                    // Find Finance Department
                    $financeDepartment = \App\Models\Department::where('created_by', $companyId)
                        ->where(function($q) {
                            $q->whereRaw('LOWER(name) LIKE ?', ['%finance%'])
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%finanace%'])  // Handle misspelling
                              ->orWhereRaw('LOWER(name) = ?', ['finance'])
                              ->orWhereRaw('LOWER(name) = ?', ['finanace']); // Handle misspelling
                        })
                        ->first();
                    $isFinanceEmployee = $employee && $financeDepartment && $employee->department_id == $financeDepartment->id;
                    $canProcessPayment = $isAdmin || $isFinanceEmployee;
                ?>
                <?php if($expense->status == 'pending_finance' && $canProcessPayment): ?>
                <div class="row">
                    <div class="col-md-12">
                        <h6><?php echo e(__('Process Payment')); ?></h6>
                        <form method="POST" action="<?php echo e(route('expenses.mark-paid', $expense->id)); ?>" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo e(__('Paid Date')); ?> <span class="text-danger">*</span></label>
                                        <input type="date" name="paid_date" class="form-control" 
                                               value="<?php echo e(date('Y-m-d')); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo e(__('Payment Mode')); ?> <span class="text-danger">*</span></label>
                                        <select name="payment_mode" class="form-control" required>
                                            <option value="bank"><?php echo e(__('Bank')); ?></option>
                                            <option value="upi"><?php echo e(__('UPI')); ?></option>
                                            <option value="cash"><?php echo e(__('Cash')); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo e(__('Payment Proof')); ?></label>
                                        <input type="file" name="payment_proof" class="form-control" 
                                               accept="image/*,application/pdf">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check"></i> <?php echo e(__('Mark as Paid')); ?>

                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Delete Button (Company/Admin only) -->
                <?php if(in_array(Auth::user()->type, ['company', 'super admin'])): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <hr>
                        <form action="<?php echo e(route('expenses.destroy', $expense->id)); ?>" 
                              method="POST" 
                              onsubmit="return confirm('<?php echo e(__('Are you sure you want to delete this expense? This action cannot be undone.')); ?>');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger" title="<?php echo e(__('Delete Expense')); ?>">
                                <i class="ti ti-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/expenses/hr/show.blade.php ENDPATH**/ ?>