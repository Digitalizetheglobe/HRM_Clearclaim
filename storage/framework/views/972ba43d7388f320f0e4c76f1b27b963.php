

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Employee')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Employee')); ?></li>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('action-button'); ?>
    <a href="<?php echo e(route('employee.export')); ?>" data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-original-title="<?php echo e(__('Export')); ?>" class="btn btn-sm btn-primary">
        <i class="ti ti-file-export"></i>
    </a>


    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Create Assets')): ?>
            <a href="<?php echo e(route('employee.create')); ?>" 
               data-title="<?php echo e(__('Create New Employee')); ?>" 
               class="btn btn-sm btn-primary ">
                <i class="ti ti-plus"></i>
            </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <ul class="nav nav-tabs" id="employeeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab" aria-controls="active" aria-selected="true">
                                <?php echo e(__('Active Employees')); ?>

                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="left-tab" data-bs-toggle="tab" data-bs-target="#left" type="button" role="tab" aria-controls="left" aria-selected="false">
                                <?php echo e(__('Left Employees')); ?>

                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3" id="employeeTabsContent">
                        <!-- Active Employees Tab -->
                        <div class="tab-pane fade show active" id="active" role="tabpanel" aria-labelledby="active-tab">
                            <div class="table-responsive">
                                <table class="table" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th><?php echo e(__('Employee ID')); ?></th>
                                            <th><?php echo e(__('Name')); ?></th>
                                            <th><?php echo e(__('Email')); ?></th>
                                            <th><?php echo e(__('Department')); ?></th>
                                            <th><?php echo e(__('Designation')); ?></th>
                                            <th><?php echo e(__('Date Of Joining')); ?></th>
                                            <?php if(Auth::user()->type != 'hr' && (Gate::check('Edit Employee') || Gate::check('Delete Employee'))): ?>
                                                <th width="100px"><?php echo e(__('Action')); ?></th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $activeEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Show Employee')): ?>
                                                        <a class="btn btn-outline-primary btn-sm"
                                                            href="<?php echo e(route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))); ?>">
                                                            <?php echo e($employee->formatted_id); ?>

                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary">
                                                            <?php echo e($employee->formatted_id); ?>

                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo e($employee->name ?? '-'); ?></td>
                                                <td><?php echo e($employee->email ?? '-'); ?></td>  

                                                <td>
                                                    <span class="">
                                                        <?php echo e($employee->department?->name ?? '-'); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="">
                                                        <?php echo e($employee->designation?->name ?? '-'); ?>

                                                    </span>
                                                </td>
                                                <td><?php echo e(\Auth::user()->dateFormat($employee->company_doj)); ?></td>
                                                
                                                <?php if(Auth::user()->type != 'hr' && (Gate::check('Edit Employee') || Gate::check('Delete Employee'))): ?>
                                                    <td class="Action" style="white-space: nowrap;">
                                                        <?php if(($employee->user?->is_active ?? 0) == 1 && ($employee->user?->is_disable ?? 0) == 1): ?>
                                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit Employee')): ?>
                                                                <a href="<?php echo e(route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))); ?>" 
                                                                   class="btn btn-sm btn-icon-only bg-info ms-2" 
                                                                   data-bs-toggle="tooltip" 
                                                                   title="<?php echo e(__('Edit')); ?>">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            <?php endif; ?>

                                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete Employee')): ?>
                                                                <?php echo Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['employee.destroy', $employee->id],
                                                                    'style' => 'display:inline'
                                                                ]); ?>

                                                                <a href="#"
                                                                   class="btn btn-sm btn-icon-only bg-danger ms-2 bs-pass-para"
                                                                   data-bs-toggle="tooltip" 
                                                                   title="<?php echo e(__('Delete')); ?>">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                                <?php echo Form::close(); ?>

                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <i class="ti ti-lock"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Left Employees Tab -->
                        <div class="tab-pane fade" id="left" role="tabpanel" aria-labelledby="left-tab">
                            <div class="table-responsive">
                                <table class="table mt-5" id="pc-dt-simple2">
                                    <thead>
                                        <tr>
                                            <th><?php echo e(__('Employee ID')); ?></th>
                                            <th><?php echo e(__('Name')); ?></th>
                                            <th><?php echo e(__('Email')); ?></th>
                                            <th><?php echo e(__('Branch')); ?></th>
                                            <th><?php echo e(__('Department')); ?></th>
                                            <th><?php echo e(__('Designation')); ?></th>
                                            <th><?php echo e(__('Date Of Joining')); ?></th>
                                            <th><?php echo e(__('Termination Date')); ?></th>
                                            <?php if(Auth::user()->type != 'hr' && Gate::check('Show Employee')): ?>
                                                <th width="80px"><?php echo e(__('Action')); ?></th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $leftEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $termination = \App\Models\Termination::where('employee_id', $employee->id)->first();
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="">
                                                        <a class="btn btn-outline-primary btn-sm"
                                                            href="<?php echo e(route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))); ?>">
                                                            <?php echo e($employee->formatted_id); ?>

                                                        </a>
                                                    </span>
                                                </td>
                                                <td><?php echo e($employee->name ?? '-'); ?></td>
                                                <td><?php echo e($employee->email ?? '-'); ?></td>  
                                                <td>
                                                    <span class="">
                                                        <?php echo e($employee->branch?->name ?? '-'); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="">
                                                        <?php echo e($employee->department?->name ?? '-'); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="">
                                                        <?php echo e($employee->designation?->name ?? '-'); ?>

                                                    </span>
                                                </td>
                                                <td><?php echo e(\Auth::user()->dateFormat($employee->company_doj)); ?></td>
                                                <td>
                                                    <?php if($termination): ?>
                                                        <?php echo e(\Auth::user()->dateFormat($termination->termination_date)); ?>

                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <?php if(Auth::user()->type != 'hr' && Gate::check('Show Employee')): ?>
                                                    <td class="Action">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Show Employee')): ?>
                                                            <a href="<?php echo e(route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))); ?>"
                                                               class="btn btn-sm btn-icon-only bg-info"
                                                               data-bs-toggle="tooltip" 
                                                               title="<?php echo e(__('View')); ?>">
                                                                <i class="ti ti-eye text-white"></i>
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
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            // Initialize both tables with the same style
            $('#pc-dt-simple').DataTable();
            $('#pc-dt-simple2').DataTable();
            
            // Delete functionality with confirmation
            $(document).on('click', '.bs-pass-para', function(e) {
                e.preventDefault();
                const button = $(this);
                const form = button.closest('form');
                
                if (!confirm('Are you sure you want to delete this employee?')) {
                    return;
                }

                // Show loading state
                button.prop('disabled', true);
                button.html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST', // Laravel needs POST for DELETE method
                    data: {
                        _method: 'DELETE',
                        _token: '<?php echo e(csrf_token()); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Remove the row with animation
                            button.closest('tr').fadeOut(400, function() {
                                $(this).remove();
                                
                                // Show success message
                                showToast('success', response.message);
                                
                                // Handle empty table state
                                if ($('#pc-dt-simple tbody tr').length === 0) {
                                    $('#pc-dt-simple tbody').append(
                                        '<tr><td colspan="8" class="text-center">No employees found</td></tr>'
                                    );
                                }
                            });
                        } else {
                            showToast('error', response.message);
                            button.prop('disabled', false).html('<i class="ti ti-trash"></i>');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Server error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.status === 403) {
                            errorMsg = 'Unauthorized action';
                        }
                        
                        showToast('error', errorMsg);
                        button.prop('disabled', false).html('<i class="ti ti-trash"></i>');
                    }
                });
            });

            // Toast notification function
            function showToast(type, message) {
                const toast = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
                
                $('.toast-container').html(toast);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/employee/index.blade.php ENDPATH**/ ?>