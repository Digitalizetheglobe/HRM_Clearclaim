<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <p><strong><?php echo e(__('Instructions:')); ?></strong></p>
                <ol>
                    <li><?php echo e(__('Click the button below to view the employee details page')); ?></li>
                    <li><?php echo e(__('Download or send the Experience Certificate / Relieving Letter')); ?></li>
                    <li><?php echo e(__('Return here and confirm that the document was downloaded/sent')); ?></li>
                </ol>
            </div>
        </div>
        <div class="col-md-12 text-center">
            <a href="<?php echo e(route('employee.show', \Crypt::encrypt($process->employee->id))); ?>" 
               target="_blank"
               class="btn btn-primary btn-lg">
                <i class="ti ti-user me-2"></i><?php echo e(__('View Employee Details')); ?>

            </a>
        </div>
        <div class="col-md-12 mt-4">
            <hr>
            <h6><?php echo e(__('Confirmation')); ?></h6>
            <p><?php echo e(__('Have you downloaded/sent the documents to the employee?')); ?></p>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
    <button type="button" class="btn btn-success" id="confirm-document-btn">
        <i class="ti ti-check me-2"></i><?php echo e(__('Yes, Documents Downloaded/Sent')); ?>

    </button>
</div>

<script>
    $('#confirm-document-btn').on('click', function() {
        if (confirm('<?php echo e(__('Are you sure the documents have been downloaded/sent to the employee?')); ?>')) {
            $.ajax({
                url: '<?php echo e(route('offboarding.update-step', ['id' => $process->id, 'step' => 6])); ?>',
                type: 'POST',
                data: {
                    confirmed: 'yes',
                    document_type: 'experience_certificate',
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        show_toastr('Success', data.message || 'Documents confirmed successfully', 'success');
                        location.reload();
                    }
                },
                error: function(data) {
                    show_toastr('Error', data.responseJSON.error || 'An error occurred', 'error');
                }
            });
        }
    });
</script>
<?php /**PATH D:\HRM_Clearclaim\resources\views/offboarding/steps/step_6.blade.php ENDPATH**/ ?>