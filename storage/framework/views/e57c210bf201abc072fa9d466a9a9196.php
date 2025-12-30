<?php echo e(Form::open(['route' => ['onboarding.update-step', $process->id, 5], 'method' => 'POST', 'id' => 'asset-issuance-form'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <h6 class="mb-3"><?php echo e(__('Asset Issuance Checklist')); ?></h6>
            <?php
                $defaultAssetItems = [
                    ['name' => 'Laptop', 'key' => 'laptop'],
                    ['name' => 'Chargers', 'key' => 'chargers'],
                    ['name' => 'Mobile', 'key' => 'mobile'],
                    ['name' => 'Mouse', 'key' => 'mouse'],
                    ['name' => 'SIM card', 'key' => 'sim_card'],
                    ['name' => 'ID card', 'key' => 'id_card'],
                    ['name' => 'Other assets', 'key' => 'other'],
                ];
                $currentChecklist = is_array($process->asset_issuance_checklist) ? $process->asset_issuance_checklist : [];
            ?>
            
            <div class="checklist-items">
                <?php $__currentLoopData = $defaultAssetItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $existingItem = collect($currentChecklist)->firstWhere('key', $item['key']);
                        $isIssued = $existingItem ? ($existingItem['issued'] ?? false) : false;
                    ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" 
                            name="checklist[<?php echo e($item['key']); ?>][issued]" 
                            value="1" 
                            id="asset_<?php echo e($item['key']); ?>"
                            <?php echo e($isIssued ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="asset_<?php echo e($item['key']); ?>">
                            <?php echo e($item['name']); ?>

                        </label>
                        <input type="hidden" name="checklist[<?php echo e($item['key']); ?>][key]" value="<?php echo e($item['key']); ?>">
                        <input type="hidden" name="checklist[<?php echo e($item['key']); ?>][name]" value="<?php echo e($item['name']); ?>">
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="alert alert-warning mt-3">
                <small><i class="ti ti-info-circle me-1"></i><?php echo e(__('All assets must be marked as Issued before proceeding to the next step.')); ?></small>
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
    $('#asset-issuance-form').on('submit', function(e) {
        e.preventDefault();
        var checklist = {};
        $(this).find('input[type="checkbox"]').each(function() {
            var key = $(this).attr('name').match(/\[([^\]]+)\]\[issued\]/)[1];
            checklist[key] = {
                'key': $(this).siblings('input[type="hidden"][name*="[key]"]').val(),
                'name': $(this).siblings('input[type="hidden"][name*="[name]"]').val(),
                'issued': $(this).is(':checked') ? true : false
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

<?php /**PATH D:\HRM_Clearclaim\resources\views/onboarding/steps/step_5.blade.php ENDPATH**/ ?>