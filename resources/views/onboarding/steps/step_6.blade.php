<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <p><strong>{{ __('Training, Policy & Agreement Acknowledgement') }}</strong></p>
                <p>{{ __('Please confirm that the employee has:') }}</p>
                <ul>
                    <li>{{ __('Completed training') }}</li>
                    <li>{{ __('Read company policies') }}</li>
                    <li>{{ __('Accepted employee agreement') }}</li>
                </ul>
            </div>
        </div>
        <div class="col-md-12">
            <h6>{{ __('Confirmation') }}</h6>
            <p>{{ __('Has the employee completed training, read policies, and accepted the agreement?') }}</p>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="button" class="btn btn-success" id="confirm-training-policy-btn">
        <i class="ti ti-check me-2"></i>{{ __('Yes, Read & Accepted') }}
    </button>
</div>

<script>
    $('#confirm-training-policy-btn').on('click', function() {
        var confirmModalHtml = '<div class="modal fade" id="training-policy-confirm-modal" tabindex="-1" role="dialog">' +
            '<div class="modal-dialog modal-dialog-centered" role="document">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title">{{ __("Confirm Training & Policy Acknowledgement") }}</h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
            '</div>' +
            '<div class="modal-body">' +
            '<p class="mb-0">{{ __("Employee has completed training, read company policies, and accepted the employee agreement.") }}</p>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</button>' +
            '<button type="button" class="btn btn-primary" id="training-policy-confirm-yes">{{ __("Yes, Confirm") }}</button>' +
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
                    url: '{{ route('onboarding.update-step', ['id' => $process->id, 'step' => 6]) }}',
                    type: 'POST',
                    data: {
                        confirmed: 'yes',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        show_toastr('Success', data.message || '{{ __("Onboarding completed successfully") }}', 'success');
                        location.reload();
                    },
                    error: function(data) {
                        show_toastr('Error', data.responseJSON.error || '{{ __("An error occurred") }}', 'error');
                    }
                });
            });
        });
    });
</script>

