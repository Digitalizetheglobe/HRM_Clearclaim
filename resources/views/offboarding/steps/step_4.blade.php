{{ Form::open(['route' => ['offboarding.update-step', $process->id, 4], 'method' => 'POST', 'id' => 'settlement-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label">{{ __('Employee') }}</label>
                <input type="text" class="form-control" value="{{ $process->employee->name ?? 'N/A' }}" readonly>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label">{{ __('Salary Settlement') }} <span class="text-danger">*</span></label>
                <input type="text" name="salary_settlement" class="form-control" 
                    value="{{ is_array($process->settlement_details) ? ($process->settlement_details['salary_settlement'] ?? '') : '' }}" 
                    required placeholder="{{ __('Enter salary settlement amount') }}">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label">{{ __('Leave Balance Adjustment') }}</label>
                <input type="text" name="leave_balance" class="form-control" 
                    value="{{ is_array($process->settlement_details) ? ($process->settlement_details['leave_balance'] ?? '') : '' }}" 
                    placeholder="{{ __('Enter leave balance details') }}">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label">{{ __('Deductions') }}</label>
                <input type="text" name="deductions" class="form-control" 
                    value="{{ is_array($process->settlement_details) ? ($process->settlement_details['deductions'] ?? '') : '' }}" 
                    placeholder="{{ __('Enter deductions if any') }}">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label">{{ __('Notes') }}</label>
                <textarea name="notes" class="form-control" rows="3" 
                    placeholder="{{ __('Enter any additional notes...') }}">{{ is_array($process->settlement_details) ? ($process->settlement_details['notes'] ?? '') : '' }}</textarea>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Mark as Completed') }}</button>
</div>
{{ Form::close() }}

<script>
    $('#settlement-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(data) {
                show_toastr('Success', data.message || 'Settlement completed successfully', 'success');
                location.reload();
            },
            error: function(data) {
                show_toastr('Error', data.responseJSON.error || 'An error occurred', 'error');
            }
        });
    });
</script>

