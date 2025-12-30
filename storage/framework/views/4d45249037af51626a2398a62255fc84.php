<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <p><strong><?php echo e(__('Training, Policy & Agreement Acknowledgement')); ?></strong></p>
                <p><?php echo e(__('Please confirm that the employee has:')); ?></p>
                <ul>
                    <li><?php echo e(__('Completed training')); ?></li>
                    <li><?php echo e(__('Read company policies')); ?></li>
                    <li><?php echo e(__('Accepted employee agreement')); ?></li>
                </ul>
            </div>
        </div>
        <div class="col-md-12">
            <h6><?php echo e(__('Confirmation')); ?></h6>
            <p><?php echo e(__('Has the employee completed training, read policies, and accepted the agreement?')); ?></p>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
    <button type="button" class="btn btn-success" id="confirm-training-policy-btn">
        <i class="ti ti-check me-2"></i><?php echo e(__('Yes, Read & Accepted')); ?>

    </button>
</div>

<script>
    $('#confirm-training-policy-btn').on('click', function() {
        var confirmModalHtml = '<div class="modal fade" id="training-policy-confirm-modal" tabindex="-1" role="dialog">' +
            '<div class="modal-dialog modal-dialog-centered" role="document">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title"><?php echo e(__("Confirm Training & Policy Acknowledgement")); ?></h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
            '</div>' +
            '<div class="modal-body">' +
            '<p class="mb-0"><?php echo e(__("Employee has completed training, read company policies, and accepted the employee agreement.")); ?></p>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__("Cancel")); ?></button>' +
            '<button type="button" class="btn btn-primary" id="training-policy-confirm-yes"><?php echo e(__("Yes, Confirm")); ?></button>' +
            '</div>' +
            '</div></div></div>';
        
        $('#training-policy-confirm-modal').remove();
        $('body').append(confirmModalHtml);
        $('#training-policy-confirm-modal').modal('show');
        
        $('#training-policy-confirm-yes').on('click', function() {
            $('#training-policy-confirm-modal').modal('hide');
            
            $('#training-policy-confirm-modal').on('hidden.bs.modal', function() {
                $(this).remove();
                
                $.ajax({
                    url: '<?php echo e(route('onboarding.update-step', ['id' => $process->id, 'step' => 6])); ?>',
                    type: 'POST',
                    data: {
                        confirmed: 'yes',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        show_toastr('Success', data.message || '<?php echo e(__("Onboarding completed successfully")); ?>', 'success');
                        location.reload();
                    },
                    error: function(data) {
                        show_toastr('Error', data.responseJSON.error || '<?php echo e(__("An error occurred")); ?>', 'error');
                    }
                });
            });
        });
    });
</script>

<?php /**PATH D:\HRM_Clearclaim\resources\views/onboarding/steps/step_6.blade.php ENDPATH**/ ?>