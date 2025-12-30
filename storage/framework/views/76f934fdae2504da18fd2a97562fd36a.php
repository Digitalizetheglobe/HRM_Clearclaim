<?php echo e(Form::open(['route' => ['onboarding.update-step', $process->id, 4], 'method' => 'POST', 'id' => 'system-access-form'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <h6 class="mb-3"><?php echo e(__('System & Access Provisioning Checklist')); ?></h6>
            <?php
                $defaultAccessItems = [
                    ['name' => 'Biometric access', 'key' => 'biometric'],
                    ['name' => 'Official Email', 'key' => 'email'],
                    ['name' => 'CRM access', 'key' => 'crm'],
                    ['name' => 'WhatsApp Group', 'key' => 'whatsapp'],
                    ['name' => 'Internal Tools', 'key' => 'internal_tools'],
                    ['name' => 'Other systems', 'key' => 'other'],
                ];
                $currentChecklist = is_array($process->system_access_checklist) ? $process->system_access_checklist : [];
            ?>
            
            <div class="checklist-items">
                <?php $__currentLoopData = $defaultAccessItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $existingItem = collect($currentChecklist)->firstWhere('key', $item['key']);
                        $isDone = $existingItem ? ($existingItem['done'] ?? false) : false;
                    ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" 
                            name="checklist[<?php echo e($item['key']); ?>][done]" 
                            value="1" 
                            id="access_<?php echo e($item['key']); ?>"
                            <?php echo e($isDone ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="access_<?php echo e($item['key']); ?>">
                            <?php echo e($item['name']); ?>

                        </label>
                        <input type="hidden" name="checklist[<?php echo e($item['key']); ?>][key]" value="<?php echo e($item['key']); ?>">
                        <input type="hidden" name="checklist[<?php echo e($item['key']); ?>][name]" value="<?php echo e($item['name']); ?>">
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="alert alert-warning mt-3">
                <small><i class="ti ti-info-circle me-1"></i><?php echo e(__('All items must be marked as Done before proceeding to the next step.')); ?></small>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
    <button type="submit" class="btn btn-primary"><?php echo e(__('Save & Proceed')); ?></button>
</div>
<?php echo e(Form::close()); ?>


<script>
    $('#system-access-form').on('submit', function(e) {
        e.preventDefault();
        var checklist = {};
        $(this).find('input[type="checkbox"]').each(function() {
            var key = $(this).attr('name').match(/\[([^\]]+)\]\[done\]/)[1];
            checklist[key] = {
                'key': $(this).siblings('input[type="hidden"][name*="[key]"]').val(),
                'name': $(this).siblings('input[type="hidden"][name*="[name]"]').val(),
                'done': $(this).is(':checked') ? true : false
            };
        });
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: {
                checklist: checklist,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                show_toastr('Success', data.message || '<?php echo e(__("Checklist updated successfully")); ?>', 'success');
                location.reload();
            },
            error: function(data) {
                var errorMsg = data.responseJSON && data.responseJSON.error ? data.responseJSON.error : '<?php echo e(__("An error occurred")); ?>';
                show_toastr('Error', errorMsg, 'error');
            }
        });
    });
</script>

<?php /**PATH D:\HRM_Clearclaim\resources\views/onboarding/steps/step_4.blade.php ENDPATH**/ ?>