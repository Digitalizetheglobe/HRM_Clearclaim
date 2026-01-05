
<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('On-Boarding Steps')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('On-Boarding Steps')); ?></li>
<?php $__env->stopSection(); ?>

<?php
    $logo = \App\Models\Utility::get_file('uploads/avatar/');
?>

<?php $__env->startPush('css-page'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/dragula.min.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('script-page'); ?>
    <script src="<?php echo e(asset('assets/js/plugins/dragula.min.js')); ?>"></script>

    <script>
        <?php if(\Auth::user()->type == 'company' || Gate::check('Manage Employee')): ?>
            ! function(a) {
                "use strict";

                var t = function() {
                    this.$body = a("body")
                };
                t.prototype.init = function() {

                    a('[data-plugin="dragula"]').each(function() {

                        var t = a(this).data("containers"),
                            n = [];
                        if (t)
                            for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]);
                        else n = [a(this)[0]];
                        var r = a(this).data("handleclass");
                        r ? dragula(n, {
                            moves: function(a, t, n) {
                                return n.classList.contains(r)
                            }
                        }) : dragula(n).on('drop', function(el, target, source, sibling) {
                            var order = [];
                            $("#" + target.id + " > div").each(function() {
                                order[$(this).index()] = $(this).attr('data-id');
                            });

                            var id = $(el).attr('data-id');
                            var old_status = $("#" + source.id).data('status');
                            var new_status = $("#" + target.id).data('status');
                            var stage_id = $(target).attr('data-id');

                            $("#" + source.id).parent().find('.count').text($("#" + source.id +
                                " > div").length);
                            $("#" + target.id).parent().find('.count').text($("#" + target.id +
                                " > div").length);
                            $.ajax({
                                url: '<?php echo e(route('onboarding.order')); ?>',
                                type: 'POST',
                                data: {
                                    order: order,
                                    stage_id: stage_id,
                                    "_token": $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(data) {
                                    show_toastr('Success', 'Process successfully updated',
                                        'success');
                                },
                                error: function(data) {
                                    data = data.responseJSON;
                                    show_toastr('Error', data.error, 'error')
                                }
                            });
                        });
                    })
                }, a.Dragula = new t, a.Dragula.Constructor = t
            }(window.jQuery),
            function(a) {
                "use strict";

                a.Dragula.init()

            }(window.jQuery);
        <?php endif; ?>
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        <?php echo e(Form::open(['route' => ['onboarding.index'], 'method' => 'get', 'id' => 'onboarding_filter'])); ?>

                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('start_date', __('Start Date'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Form::date('start_date', $filter['start_date'], ['class' => 'month-btn form-control current_date'])); ?>

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('end_date', __('End Date'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'month-btn form-control current_date', 'autocomplete' => 'off'])); ?>

                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('employee', __('Employee'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Form::select('employee', $employees, $filter['employee'], ['class' => 'form-control select ', 'id' => 'employee_id'])); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('onboarding_filter').submit(); return false;"
                                            data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="<?php echo e(route('onboarding.index')); ?>" class="btn btn-sm btn-danger"
                                            data-bs-toggle="tooltip" title="" data-bs-original-title="Reset">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php echo e(Form::close()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden mt-0">
        <div class="container-kanban">
            <?php
                $json = [];
                foreach ($stages as $stage) {
                    $json[] = 'kanban-onboarding-' . $stage->id;
                }
            ?>

            <div class="row kanban-wrapper horizontal-scroll-cards" data-plugin="dragula"
                data-containers='<?php echo json_encode($json); ?>'>
                <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php 
                        try {
                            $processes = $stage->processes($filter);
                        } catch (\Exception $e) {
                            $processes = collect([]);
                        }
                    ?>

                    <div class="col">
                        <div class="card">
                            <div class="card-header">
                                <div class="float-end">
                                    <span class="btn btn-sm btn-primary btn-icon count">
                                        <?php echo e(count($processes)); ?>

                                    </span>
                                </div>
                                <h4 class="mb-0"><?php echo e($stage->title); ?></h4>
                            </div>

                            <div class="card-body kanban-box" id="<?php echo e($json[$key]); ?>" data-id="<?php echo e($stage->id); ?>" data-status="<?php echo e($stage->id); ?>">
                                <?php $__currentLoopData = $processes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $process): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="card" data-id="<?php echo e($process->id); ?>">
                                        <div class="pt-3 ps-3">
                                        </div>
                                        <div class="card-header border-0 pb-0 position-relative">
                                            <h5>
                                                <?php if($process->employee): ?>
                                                    <?php if($stage->order == 1): ?>
                                                        <a href="<?php echo e(route('employee.show', \Crypt::encrypt($process->employee->id))); ?>" 
                                                           class="process-link onboarding-step-1-link"
                                                           data-process-id="<?php echo e($process->id); ?>"
                                                           data-employee-id="<?php echo e($process->employee->id); ?>"
                                                           data-step="1">
                                                            <?php echo e($process->employee->name); ?>

                                                        </a>
                                                    <?php elseif($stage->order == 2): ?>
                                                        <a href="<?php echo e(route('employee.edit', \Crypt::encrypt($process->employee->id))); ?>" 
                                                           class="process-link onboarding-step-2-link"
                                                           data-process-id="<?php echo e($process->id); ?>"
                                                           data-employee-id="<?php echo e($process->employee->id); ?>"
                                                           data-step="2">
                                                            <?php echo e($process->employee->name); ?>

                                                        </a>
                                                    <?php else: ?>
                                                        <a href="#" 
                                                            data-url="<?php echo e(route('onboarding.step', ['id' => $process->id, 'step' => $stage->order])); ?>"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            data-title="<?php echo e($stage->title); ?> - <?php echo e($process->employee->name); ?>"
                                                            class="process-link">
                                                            <?php echo e($process->employee->name); ?>

                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php echo e(__('N/A')); ?>

                                                <?php endif; ?>
                                            </h5>

                                            <div class="card-header-right">
                                                <div class="btn-group card-option">
                                                    <button type="button" class="btn dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="feather icon-more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <?php if($stage->order == 1): ?>
                                                            <a href="<?php echo e(route('employee.show', \Crypt::encrypt($process->employee->id))); ?>"
                                                                class="dropdown-item onboarding-step-1-link"
                                                                data-process-id="<?php echo e($process->id); ?>"
                                                                data-employee-id="<?php echo e($process->employee->id); ?>"
                                                                data-step="1"
                                                                target="_blank"><i
                                                                    class="ti ti-eye "></i><span
                                                                    class="ms-2"><?php echo e(__('View Employee Details')); ?></span></a>
                                                        <?php elseif($stage->order == 2): ?>
                                                            <a href="<?php echo e(route('employee.edit', \Crypt::encrypt($process->employee->id))); ?>"
                                                                class="dropdown-item onboarding-step-2-link"
                                                                data-process-id="<?php echo e($process->id); ?>"
                                                                data-employee-id="<?php echo e($process->employee->id); ?>"
                                                                data-step="2"
                                                                target="_blank"><i
                                                                    class="ti ti-edit "></i><span
                                                                    class="ms-2"><?php echo e(__('Edit Employee')); ?></span></a>
                                                        <?php else: ?>
                                                            <a href="#" 
                                                                data-url="<?php echo e(route('onboarding.step', ['id' => $process->id, 'step' => $stage->order])); ?>"
                                                                data-ajax-popup="true"
                                                                data-size="lg"
                                                                data-title="<?php echo e($stage->title); ?> - <?php echo e($process->employee ? $process->employee->name : 'N/A'); ?>"
                                                                class="dropdown-item"><i
                                                                    class="ti ti-eye "></i><span
                                                                    class="ms-2"><?php echo e(__('View Details')); ?></span></a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <ul class="list-inline mb-0 mt-0">
                                                    <?php if($process->employee): ?>
                                                        <small class="text-md"><?php echo e($process->employee->email ?? ''); ?></small>
                                                    <?php endif; ?>

                                                    <li class="list-inline-item d-inline-flex align-items-center"
                                                        data-bs-toggle="tooltip" title="<?php echo e(__('Created at')); ?>">
                                                        <i class="ti ti-clock me-2"></i><?php echo e(\Auth::user()->dateFormat($process->created_at)); ?>

                                                    </li>

                                                    <?php if($stage->order == 4 && $process->system_access_checklist): ?>
                                                        <?php
                                                            $accessItems = is_array($process->system_access_checklist) ? $process->system_access_checklist : [];
                                                            $accessDone = count(array_filter($accessItems, function($item) { return isset($item['done']) && $item['done']; }));
                                                            $accessTotal = count($accessItems);
                                                        ?>
                                                        <li class="list-inline-item">
                                                            <small><?php echo e($accessDone); ?>/<?php echo e($accessTotal); ?> Done</small>
                                                        </li>
                                                    <?php endif; ?>

                                                    <?php if($stage->order == 5 && $process->asset_issuance_checklist): ?>
                                                        <?php
                                                            $assetItems = is_array($process->asset_issuance_checklist) ? $process->asset_issuance_checklist : [];
                                                            $assetIssued = count(array_filter($assetItems, function($item) { return isset($item['issued']) && $item['issued']; }));
                                                            $assetTotal = count($assetItems);
                                                        ?>
                                                        <li class="list-inline-item">
                                                            <small><?php echo e($assetIssued); ?>/<?php echo e($assetTotal); ?> Issued</small>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                                <?php if($process->employee): ?>
                                                    <div class="avatar-group hover-avatar-ungroup">
                                                        <a href="#" class="user-group">
                                                            <?php
                                                                $avatar = ($process->employee->user && !empty($process->employee->user->avatar)) ? $process->employee->user->avatar : 'avatar.png';
                                                            ?>
                                                            <img src="<?php echo e($logo . $avatar); ?>"
                                                                class="hweb " style="width: 28px">
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <span class="empty-container" data-placeholder="Empty"></span>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).ready(function() {
            var now = new Date();
            var month = (now.getMonth() + 1);
            var day = now.getDate();
            if (month < 10) month = "0" + month;
            if (day < 10) day = "0" + day;
            var today = now.getFullYear() + '-' + month + '-' + day;
            $('.current_date').val(today);

            // Track when user clicks on employee link in step 1
            $(document).on('click', 'a.onboarding-step-1-link', function(e) {
                var processId = $(this).data('process-id');
                var employeeId = $(this).data('employee-id');
                
                if (processId && employeeId) {
                    sessionStorage.setItem('onboarding_employee_visit_step1', JSON.stringify({
                        processId: processId,
                        employeeId: employeeId,
                        step: 1,
                        timestamp: new Date().getTime()
                    }));
                }
            });

            // Track when user clicks on employee link in step 2
            $(document).on('click', 'a.onboarding-step-2-link', function(e) {
                var processId = $(this).data('process-id');
                var employeeId = $(this).data('employee-id');
                
                if (processId && employeeId) {
                    sessionStorage.setItem('onboarding_employee_visit_step2', JSON.stringify({
                        processId: processId,
                        employeeId: employeeId,
                        step: 2,
                        timestamp: new Date().getTime()
                    }));
                }
            });

            // Check if user returned from employee page (Step 1)
            function checkEmployeePageReturnStep1() {
                var visitData = sessionStorage.getItem('onboarding_employee_visit_step1');
                
                if (visitData) {
                    try {
                        var data = JSON.parse(visitData);
                        var processId = data.processId;
                        var timestamp = data.timestamp;
                        var timeDiff = new Date().getTime() - timestamp;
                        
                        if (timeDiff < 600000) { // 10 minutes
                            setTimeout(function() {
                                var confirmModalHtml = '<div class="modal fade" id="employee-confirm-modal-step1" tabindex="-1" role="dialog">' +
                                    '<div class="modal-dialog modal-dialog-centered" role="document">' +
                                    '<div class="modal-content">' +
                                    '<div class="modal-header">' +
                                    '<h5 class="modal-title"><?php echo e(__("Employee Creation Verification")); ?></h5>' +
                                    '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                                    '</div>' +
                                    '<div class="modal-body">' +
                                    '<p class="mb-0"><?php echo e(__("Employee created properly. Please confirm.")); ?></p>' +
                                    '</div>' +
                                    '<div class="modal-footer">' +
                                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__("Cancel")); ?></button>' +
                                    '<button type="button" class="btn btn-primary" id="employee-confirm-ok-step1"><?php echo e(__("Confirm")); ?></button>' +
                                    '</div>' +
                                    '</div></div></div>';
                                
                                $('body').append(confirmModalHtml);
                                $('#employee-confirm-modal-step1').modal('show');
                                
                                var userClickedYes = false;
                                
                                $('#employee-confirm-ok-step1').on('click', function() {
                                    userClickedYes = true;
                                    $('#employee-confirm-modal-step1').modal('hide');
                                });
                                
                                $('#employee-confirm-modal-step1').on('hidden.bs.modal', function() {
                                    $(this).remove();
                                    
                                    if (userClickedYes) {
                                        $.ajax({
                                            url: '<?php echo e(url("onboarding")); ?>/' + processId + '/step/1',
                                            type: 'POST',
                                            data: {
                                                confirmed: 'yes',
                                                _token: $('meta[name="csrf-token"]').attr('content')
                                            },
                                            success: function(data) {
                                                show_toastr('Success', data.message || '<?php echo e(__("Step completed successfully")); ?>', 'success');
                                                location.reload();
                                            },
                                            error: function(data) {
                                                show_toastr('Error', data.responseJSON.error || '<?php echo e(__("An error occurred")); ?>', 'error');
                                            }
                                        });
                                    }
                                    
                                    sessionStorage.removeItem('onboarding_employee_visit_step1');
                                });
                            }, 500);
                        } else {
                            sessionStorage.removeItem('onboarding_employee_visit_step1');
                        }
                    } catch (e) {
                        console.error('Error parsing visit data:', e);
                        sessionStorage.removeItem('onboarding_employee_visit_step1');
                    }
                }
            }

            // Check if user returned from employee page (Step 2)
            function checkEmployeePageReturnStep2() {
                var visitData = sessionStorage.getItem('onboarding_employee_visit_step2');
                
                if (visitData) {
                    try {
                        var data = JSON.parse(visitData);
                        var processId = data.processId;
                        var timestamp = data.timestamp;
                        var timeDiff = new Date().getTime() - timestamp;
                        
                        if (timeDiff < 600000) { // 10 minutes
                            setTimeout(function() {
                                var confirmModalHtml = '<div class="modal fade" id="employee-confirm-modal-step2" tabindex="-1" role="dialog">' +
                                    '<div class="modal-dialog modal-dialog-centered" role="document">' +
                                    '<div class="modal-content">' +
                                    '<div class="modal-header">' +
                                    '<h5 class="modal-title"><?php echo e(__("Document Upload Verification")); ?></h5>' +
                                    '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                                    '</div>' +
                                    '<div class="modal-body">' +
                                    '<p class="mb-0"><?php echo e(__("Documents uploaded and verified successfully")); ?></p>' +
                                    '</div>' +
                                    '<div class="modal-footer">' +
                                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__("Cancel")); ?></button>' +
                                    '<button type="button" class="btn btn-primary" id="employee-confirm-ok-step2"><?php echo e(__("Confirm")); ?></button>' +
                                    '</div>' +
                                    '</div></div></div>';
                                
                                $('body').append(confirmModalHtml);
                                $('#employee-confirm-modal-step2').modal('show');
                                
                                var userClickedYes = false;
                                
                                $('#employee-confirm-ok-step2').on('click', function() {
                                    userClickedYes = true;
                                    $('#employee-confirm-modal-step2').modal('hide');
                                });
                                
                                $('#employee-confirm-modal-step2').on('hidden.bs.modal', function() {
                                    $(this).remove();
                                    
                                    if (userClickedYes) {
                                        $.ajax({
                                            url: '<?php echo e(url("onboarding")); ?>/' + processId + '/step/2',
                                            type: 'POST',
                                            data: {
                                                confirmed: 'yes',
                                                _token: $('meta[name="csrf-token"]').attr('content')
                                            },
                                            success: function(data) {
                                                show_toastr('Success', data.message || '<?php echo e(__("Step completed successfully")); ?>', 'success');
                                                location.reload();
                                            },
                                            error: function(data) {
                                                show_toastr('Error', data.responseJSON.error || '<?php echo e(__("An error occurred")); ?>', 'error');
                                            }
                                        });
                                    }
                                    
                                    sessionStorage.removeItem('onboarding_employee_visit_step2');
                                });
                            }, 500);
                        } else {
                            sessionStorage.removeItem('onboarding_employee_visit_step2');
                        }
                    } catch (e) {
                        console.error('Error parsing visit data:', e);
                        sessionStorage.removeItem('onboarding_employee_visit_step2');
                    }
                }
            }

            // Check on page load
            checkEmployeePageReturnStep1();
            checkEmployeePageReturnStep2();

            // Also check when page becomes visible
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    setTimeout(function() {
                        checkEmployeePageReturnStep1();
                        checkEmployeePageReturnStep2();
                    }, 500);
                }
            });

            // Check on focus
            $(window).on('focus', function() {
                setTimeout(function() {
                    checkEmployeePageReturnStep1();
                    checkEmployeePageReturnStep2();
                }, 500);
            });
        });
    </script>
<?php $__env->stopPush(); ?>





<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/onboarding/index.blade.php ENDPATH**/ ?>