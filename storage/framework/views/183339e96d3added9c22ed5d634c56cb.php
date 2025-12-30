<?php echo e(Form::open(['route' => ['offboarding.update-step', $process->id, 7], 'method' => 'POST', 'id' => 'feedback-form'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label"><?php echo e(__('Employee')); ?></label>
                <input type="text" class="form-control" value="<?php echo e($process->employee->name ?? 'N/A'); ?>" readonly>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label"><?php echo e(__('Employee Feedback')); ?> <span class="text-danger">*</span></label>
                <textarea name="feedback" class="form-control" rows="6" required 
                    placeholder="<?php echo e(__('Record employee feedback about their experience, reasons for leaving, suggestions, etc...')); ?>"><?php echo e($process->employee_feedback); ?></textarea>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
    <button type="submit" class="btn btn-primary"><?php echo e(__('Submit Feedback')); ?></button>
</div>
<?php echo e(Form::close()); ?>


<script>
    $('#feedback-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(data) {
                show_toastr('Success', data.message || 'Feedback recorded successfully', 'success');
                location.reload();
            },
            error: function(data) {
                show_toastr('Error', data.responseJSON.error || 'An error occurred', 'error');
            }
        });
    });
</script>

<?php /**PATH D:\HRM_Clearclaim\resources\views/offboarding/steps/step_7.blade.php ENDPATH**/ ?>