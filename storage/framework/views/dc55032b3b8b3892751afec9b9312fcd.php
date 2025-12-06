<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Employee')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Manage Employee')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <div class="float-end">
        
        <?php if(\Auth::user()->type === 'employee'): ?>
            
            <?php if($employee->approval_status !== 'approved'): ?>
                <a href="<?php echo e(route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))); ?>"
                    data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>" class="btn btn-sm btn-primary">
                    <i class="ti ti-pencil"></i>
                </a>
            <?php endif; ?>
        <?php else: ?>
            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit employee')): ?>
                <a href="<?php echo e(route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))); ?>"
                    data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>" class="btn btn-sm btn-primary">
                    <i class="ti ti-pencil"></i>
                </a>
            <?php endif; ?>
        <?php endif; ?>
        
        
        <?php if(\Auth::user()->type !== 'employee' && ($employee->approval_status === 'pending' || empty($employee->approval_status))): ?>
            
            <?php if(\Auth::user()->type === 'company' || \Auth::user()->type === 'hr' || \Auth::user()->can('approve employee')): ?>
                <button type="button" class="btn btn-sm btn-success" 
                    data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="ti ti-check"></i> <?php echo e(__('Approve')); ?>

                </button>
                
                <button type="button" class="btn btn-sm btn-danger" 
                    data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="ti ti-x"></i> <?php echo e(__('Reject')); ?>

                </button>
            <?php endif; ?>
        <?php endif; ?>
        
        
        <?php if(\Auth::user()->type === 'employee' && $employee->approval_status === 'rejected'): ?>
            <form action="<?php echo e(route('employee.request-approval', $employee->id)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-sm btn-warning">
                    <i class="ti ti-refresh"></i> <?php echo e(__('Request Approval Again')); ?>

                </button>
            </form>
        <?php endif; ?>
        
        
        <div class="d-inline">
            <ul class="list-unstyled mb-0 m-2 d-inline">
                <li class="dropdown dash-h-item drp-language d-inline">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0 btn btn-sm btn-info" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text hide-mob text-white"> <?php echo e(__('Offer Letter')); ?>

                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="<?php echo e(route('joiningletter.download.pdf', $employee->id)); ?>" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i><?php echo e(__('PDF')); ?></a>

                        <a href="<?php echo e(route('joininglatter.download.doc', $employee->id)); ?>" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i><?php echo e(__('DOC')); ?></a>
                    </div>
                </li>
            </ul>

            <ul class="list-unstyled mb-0 m-2 d-inline">
                <li class="dropdown dash-h-item drp-language d-inline">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0 btn btn-sm btn-info" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text hide-mob text-white"> <?php echo e(__('Experience Certificate')); ?>

                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="<?php echo e(route('exp.download.pdf', $employee->id)); ?>" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i><?php echo e(__('PDF')); ?></a>

                        <a href="<?php echo e(route('exp.download.doc', $employee->id)); ?>" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i><?php echo e(__('DOC')); ?></a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="alert alert-<?php if($employee->approval_status === 'approved'): ?>success
                                <?php elseif($employee->approval_status === 'rejected'): ?>danger
                                @elsewarning <?php endif; ?>">
                <strong><?php echo e(__('Approval Status')); ?>:</strong> 
                <?php echo e(ucfirst($employee->approval_status ?? 'pending')); ?>

                
                <?php if($employee->approval_status === 'approved' && $employee->approved_at): ?>
                    <br><small><?php echo e(__('Approved on')); ?>: <?php echo e(\Auth::user()->dateFormat($employee->approved_at)); ?> 
                    <?php if($employee->approvedBy): ?> by <?php echo e($employee->approvedBy->name); ?> <?php endif; ?></small>
                <?php endif; ?>
                
                <?php if($employee->approval_status === 'rejected' && $employee->rejection_reason): ?>
                    <br><small><?php echo e(__('Reason')); ?>: <?php echo e($employee->rejection_reason); ?></small>
                <?php endif; ?>

                <?php if(!$employee->approval_status || $employee->approval_status === 'pending'): ?>
                    <br><small><?php echo e(__('Waiting for approval from HR/Company.')); ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                            <div class="card-header">
                                <h6><?php echo e(__('Personal Details')); ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Employee ID')); ?>:</strong> <?php echo e($employeesId); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Name')); ?>:</strong> <?php echo e($employee->name); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Email')); ?>:</strong> <?php echo e($employee->email); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Phone')); ?>:</strong> <?php echo e($employee->phone); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Office Phone 1')); ?>:</strong> <?php echo e($employee->office_phone_one ?? 'N/A'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Office Phone 2')); ?>:</strong> <?php echo e($employee->office_phone_two ?? 'N/A'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Emergency Number')); ?>:</strong> <?php echo e($employee->emergency_number ?? 'N/A'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Date of Birth')); ?>:</strong> 
                                            <?php echo e($employee->dob ? \Auth::user()->dateFormat($employee->dob) : __('Not Set')); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Blood Group')); ?>:</strong> <?php echo e($employee->blood_group ?? 'N/A'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Gender')); ?>:</strong> <?php echo e($employee->gender); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                    </div>
                                    <div class="col-12">
                                        <p><strong><?php echo e(__('Address')); ?>:</strong> <?php echo e($employee->address ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="col-sm-12 col-md-6">
                   <div class="card">
                            <div class="card-header">
                                <h6><?php echo e(__('Company Details')); ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Branch')); ?>:</strong> <?php echo e($employee->branch->name ?? 'N/A'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Department')); ?>:</strong> <?php echo e($employee->department->name ?? 'N/A'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Designation')); ?>:</strong> <?php echo e($employee->designation->name ?? 'N/A'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo e(__('Date of Joining')); ?>:</strong> 
                                            <?php echo e($employee->company_doj ? \Auth::user()->dateFormat($employee->company_doj) : __('Not Set')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="card ">
                        <div class="card-body employee-detail-body fulls-card emp-card">
                            <h5><?php echo e(__('Document Detail')); ?></h5>
                            <hr>
                            <div class="row">
                                <?php
                                    $employeedoc = $employee->documents()->pluck('document_value', 'document_id');
                                ?>
                                <?php if(!$documents->isEmpty()): ?>
                                    <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-6">
                                            <div class="info text-sm">
                                                <strong class="font-bold"><?php echo e($document->name); ?> : </strong>
                                                <span>
                                                    <?php if(!empty($employeedoc[$document->id])): ?>
                                                        <a href="<?php echo e(asset($employeedoc[$document->id])); ?>" 
                                                        target="_blank" 
                                                        class="btn btn-sm btn-primary">
                                                            <i class="ti ti-download"></i> View
                                                        </a>
                                                    <?php else: ?>
                                                        No document uploaded
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <div class="text-center">
                                        No Document Type Added!
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-body employee-detail-body fulls-card emp-card">
                            <h5><?php echo e(__('Experience Detail')); ?></h5>
                            <hr>
                            <div class="row">
                                <?php if(!empty($experienceDetails)): ?>
                                    <?php $__currentLoopData = $experienceDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-12 mb-3">
                                            <strong>Company Name:</strong> <?php echo e($exp['previous_company_name'] ?? '-'); ?><br>
                                            <strong>Designation:</strong> <?php echo e($exp['previous_designation'] ?? '-'); ?><br>
                                            <strong>Start Date:</strong> <?php echo e($exp['start_date'] ?? '-'); ?><br>
                                            <strong>End Date:</strong> <?php echo e($exp['end_date'] ?? '-'); ?><br>
                                            <strong>Previous Salary:</strong> <?php echo e($exp['previous_salary'] ?? '-'); ?>

                                            <hr>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <div class="col-md-12">
                                        <p>No experience detail available.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
           <div class="card">
                <div class="card-header">
                    <h6><?php echo e(__('Education Details')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if(!empty($educationDetails)): ?>
                            <?php $__currentLoopData = $educationDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $edu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-12 mb-3">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Degree:</strong><br>
                                            <?php echo e($edu['degree'] ?? '-'); ?>

                                        </div>
                                        <div class="col-md-3">
                                            <strong>College Name:</strong><br>
                                            <?php echo e($edu['college_name'] ?? '-'); ?>

                                        </div>
                                        <div class="col-md-3">
                                            <strong>Passing Year:</strong><br>
                                            <?php echo e($edu['passing_year'] ?? '-'); ?>

                                        </div>
                                        <div class="col-md-3">
                                            <strong>Grade:</strong><br>
                                            <?php echo e($edu['grade'] ?? '-'); ?>

                                        </div>
                                        <div class="col-md-3">
                                            <strong>Document:</strong><br>
                                            <?php if(isset($edu['document_path'])): ?>
                                                <a href="<?php echo e(asset($edu['document_path'])); ?>" 
                                                target="_blank" 
                                                class="btn btn-sm btn-primary">
                                                    <i class="ti ti-download"></i> View
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <div class="col-md-12">
                                <p>No education details available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
           <div class="card">
                <div class="card-header">
                    <h6><?php echo e(__('Bank Account Detail')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <p><strong><?php echo e(__('Account Holder Name')); ?>:</strong> <?php echo e($employee->account_holder_name ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <p><strong><?php echo e(__('Bank Name')); ?>:</strong> <?php echo e($employee->bank_name ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <p><strong><?php echo e(__('Branch Location')); ?>:</strong> <?php echo e($employee->branch_location ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <p><strong><?php echo e(__('Account Number')); ?>:</strong> <?php echo e($employee->account_number ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <p><strong><?php echo e(__('Bank Identifier Code')); ?>:</strong> <?php echo e($employee->bank_identifier_code ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <p><strong><?php echo e(__('Tax Payer Id')); ?>:</strong> <?php echo e($employee->tax_payer_id ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(\Auth::user()->type !== 'employee'): ?>
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel"><?php echo e(__('Approve Employee Details')); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><?php echo e(__('Are you sure you want to approve this employee\'s details?')); ?></p>
                        <p><?php echo e(__('Once approved, the employee will not be able to edit their information.')); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
                        <form action="<?php echo e(route('employee.approve', $employee->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-success"><?php echo e(__('Approve')); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel"><?php echo e(__('Reject Employee Details')); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?php echo e(route('employee.reject', $employee->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="modal-body">
                            <p><?php echo e(__('Please provide a reason for rejecting this employee\'s details:')); ?></p>
                            <div class="form-group">
                                <textarea name="rejection_reason" class="form-control" rows="3" required 
                                          placeholder="<?php echo e(__('Enter rejection reason...')); ?>"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
                            <button type="submit" class="btn btn-danger"><?php echo e(__('Reject')); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\TEST_time\resources\views/employee/show.blade.php ENDPATH**/ ?>