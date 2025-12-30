

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Review Resignation')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5><?php echo e(__('Resignation Details')); ?></h5>
            </div>
            <div class="card-body">
                <?php echo e(Form::open(['route' => ['resignation.approve', $resignation->id], 'method' => 'post'])); ?>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label><?php echo e(__('Employee')); ?></label>
                        <input type="text" class="form-control" value="<?php echo e($resignation->employee->name); ?>" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <?php echo e(Form::label('notice_date', __('Resignation Date'), ['class' => 'form-label'])); ?>

                        <?php echo e(Form::date('notice_date', $resignation->notice_date, ['class' => 'form-control', 'required' => 'required'])); ?>

                    </div>
                    <div class="form-group col-md-6">
                        <?php echo e(Form::label('resignation_date', __('Last Working Day'), ['class' => 'form-label'])); ?>

                        <?php echo e(Form::date('resignation_date', $resignation->resignation_date, ['class' => 'form-control', 'required' => 'required'])); ?>

                    </div>
                    <div class="form-group col-md-12">
                        <label><?php echo e(__('Reason')); ?></label>
                        <textarea class="form-control" readonly><?php echo e($resignation->description); ?></textarea>
                    </div>
                    <div class="col-md-12 text-end">
                        <a href="<?php echo e(route('resignation.index')); ?>" class="btn btn-secondary"><?php echo e(__('Cancel')); ?></a>
                        <button type="submit" class="btn btn-primary"><?php echo e(__('Approve')); ?></button>
                    </div>
                </div>
                <?php echo e(Form::close()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/resignation/review.blade.php ENDPATH**/ ?>