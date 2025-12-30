<?php echo e(Form::open(['route' => ['offboarding.update-step', $process->id, 3], 'method' => 'POST', 'id' => 'asset-collection-form'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <h6 class="mb-3"><?php echo e(__('Asset Collection Checklist')); ?></h6>
            <?php
                $defaultAssetItems = [
                    ['name' => 'Laptop', 'key' => 'laptop'],
                    ['name' => 'Charger', 'key' => 'charger'],
                    ['name' => 'Mobile', 'key' => 'mobile'],
                    ['name' => 'Mouse', 'key' => 'mouse'],
                    ['name' => 'SIM card', 'key' => 'sim'],
                    ['name' => 'ID card', 'key' => 'id_card'],
                    ['name' => 'Other assets', 'key' => 'other'],
                ];
                $currentChecklist = is_array($process->asset_collection_checklist) ? $process->asset_collection_checklist : [];
            ?>
            
            <div class="checklist-items">
                <?php $__currentLoopData = $defaultAssetItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $existingItem = collect($currentChecklist)->firstWhere('key', $item['key']);
                        $isCollected = $existingItem ? ($existingItem['collected'] ?? false) : false;
                    ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" 
                            name="checklist[<?php echo e($item['key']); ?>][collected]" 
                            value="1" 
                            id="asset_<?php echo e($item['key']); ?>"
                            <?php echo e($isCollected ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="asset_<?php echo e($item['key']); ?>">
                            <?php echo e($item['name']); ?>

                        </label>
                        <input type="hidden" name="checklist[<?php echo e($item['key']); ?>][key]" value="<?php echo e($item['key']); ?>">
                        <input type="hidden" name="checklist[<?php echo e($item['key']); ?>][name]" value="<?php echo e($item['name']); ?>">
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
    <button type="submit" class="btn btn-primary"><?php echo e(__('Save Checklist')); ?></button>
</div>
<?php echo e(Form::close()); ?>


<script>
    $('#asset-collection-form').on('submit', function(e) {
        e.preventDefault();
        var checklist = {};
        $(this).find('input[type="checkbox"]').each(function() {
            var key = $(this).attr('name').match(/\[([^\]]+)\]\[collected\]/)[1];
            checklist[key] = {
                'key': $(this).siblings('input[type="hidden"][name*="[key]"]').val(),
                'name': $(this).siblings('input[type="hidden"][name*="[name]"]').val(),
                'collected': $(this).is(':checked') ? true : false
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
                show_toastr('Success', data.message || 'Checklist updated successfully', 'success');
                location.reload();
            },
            error: function(data) {
                var errorMsg = data.responseJSON && data.responseJSON.error ? data.responseJSON.error : 'An error occurred';
                show_toastr('Error', errorMsg, 'error');
            }
        });
    });
</script>

<?php /**PATH D:\HRM_Clearclaim\resources\views/offboarding/steps/step_3.blade.php ENDPATH**/ ?>