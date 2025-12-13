

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Notice List')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Notice List')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <?php if(Auth::user()->type != 'hr'): ?> 
        <div class="row align-items-center m-1">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Create Employee')): ?>
                <a href="#" data-size="lg" data-url="<?php echo e(route('notices.create')); ?>" data-ajax-popup="true"
                    data-bs-toggle="tooltip" title="<?php echo e(__('Create New Notice')); ?>" data-title="<?php echo e(__('Add New Notice')); ?>"
                    class="btn btn-sm btn-primary">
                    <i class="ti ti-plus"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Title')); ?></th>
                                <th><?php echo e(__('Description')); ?></th>
                                <th><?php echo e(__('Start Date')); ?></th>
                                <th><?php echo e(__('End Date')); ?></th>
                                <?php if(Auth::user()->type != 'hr' && (Gate::check('Edit Meeting') || Gate::check('Delete Meeting'))): ?>
                                    <th width="130px"><?php echo e(__('Actions')); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $notices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($notice->title); ?></td>
                                    <td><?php echo e(Str::limit($notice->description, 50)); ?></td>
                                    <td><?php echo e($notice->notice_startdate ? \Carbon\Carbon::parse($notice->notice_startdate)->format('d M Y') : '-'); ?></td>
                                    <td><?php echo e($notice->notice_enddate ? \Carbon\Carbon::parse($notice->notice_enddate)->format('d M Y') : '-'); ?></td>
                                    <?php if(Auth::user()->type != 'hr' && (Gate::check('Edit Meeting') || Gate::check('Delete Meeting'))): ?>
                                        <td class="d-flex gap-2">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit Meeting')): ?>
                                                <!-- Edit Button -->
                                                <a href="#" 
                                                    class="btn btn-sm btn-info text-white" 
                                                    data-url="<?php echo e(route('notices.edit', $notice->id)); ?>" 
                                                    data-ajax-popup="true" 
                                                    data-size="lg" 
                                                    data-bs-toggle="tooltip" 
                                                    data-title="<?php echo e(__('Edit Notice')); ?>" 
                                                    data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                    <i class="ti ti-pencil"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete Meeting')): ?>
                                                <!-- Delete Button with Form -->
                                                <form id="delete-form-<?php echo e($notice->id); ?>" method="POST" action="<?php echo e(route('notices.destroy', $notice->id)); ?>" style="display: inline;">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                </form>

                                                <a href="#" class="btn btn-sm btn-danger text-white"
                                                    data-bs-toggle="tooltip"
                                                    title="<?php echo e(__('Delete Notice')); ?>"
                                                    onclick="event.preventDefault(); document.getElementById('delete-form-<?php echo e($notice->id); ?>').submit();">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                            <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#notice-table').DataTable({
            "language": {
                "emptyTable": "No notices found"
            },
            "lengthMenu": [10, 25, 50, 100],
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/notice/index.blade.php ENDPATH**/ ?>