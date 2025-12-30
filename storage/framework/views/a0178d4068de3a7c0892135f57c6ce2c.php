

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Add New Expense')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('expenses.index')); ?>"><?php echo e(__('My Expenses')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Add New Expense')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5><?php echo e(__('Expense Request Form')); ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('expenses.store')); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label"><?php echo e(__('Category')); ?> <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-control" required>
                                    <option value=""><?php echo e(__('Select Category')); ?></option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($category->id); ?>" <?php echo e(old('category_id') == $category->id ? 'selected' : ''); ?>>
                                            <?php echo e($category->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label"><?php echo e(__('Amount')); ?> <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" 
                                       value="<?php echo e(old('amount')); ?>" required>
                                <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label"><?php echo e(__('Expense Date')); ?> <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control" 
                                       value="<?php echo e(old('expense_date')); ?>" required>
                                <?php $__errorArgs = ['expense_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label"><?php echo e(__('Receipt File(s)')); ?></label>
                                <input type="file" name="receipt_file[]" class="form-control" 
                                       accept="image/*,application/pdf" multiple>
                                <small class="text-muted"><?php echo e(__('You can upload multiple files (JPG, PNG, PDF). Max 10MB per file.')); ?></small>
                                <?php $__errorArgs = ['receipt_file.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label"><?php echo e(__('Description')); ?></label>
                                <textarea name="description" class="form-control" rows="4" 
                                          placeholder="<?php echo e(__('Enter expense description...')); ?>"><?php echo e(old('description')); ?></textarea>
                                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="invalid-feedback" role="alert"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-end">
                        <a href="<?php echo e(route('expenses.index')); ?>" class="btn btn-secondary"><?php echo e(__('Cancel')); ?></a>
                        <button type="submit" class="btn btn-primary"><?php echo e(__('Submit Expense Request')); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>








<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/expenses/employee/create.blade.php ENDPATH**/ ?>