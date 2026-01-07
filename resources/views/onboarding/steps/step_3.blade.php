<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <p><strong>{{ __('Instructions:') }}</strong></p>
                <p>{{ __('Please confirm that you have received the employee acknowledgement on hard copy.') }}</p>
            </div>
        </div>
        <div class="col-md-12">
            <h6>{{ __('Confirmation') }}</h6>
            <p>{{ __('Has the employee acknowledgement been received on hard copy?') }}</p>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="button" class="btn btn-success" id="confirm-acknowledgement-btn">
        <i class="ti ti-check me-2"></i>{{ __('Yes, Acknowledgement Received') }}
    </button>
</div>

<script>
    $('#confirm-acknowledgement-btn').on('click', function() {
        var confirmModalHtml = '<div class="modal fade" id="acknowledgement-confirm-modal" tabindex="-1" role="dialog">' +
            '<div class="modal-dialog modal-dialog-centered" role="document">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title">{{ __("Confirm Acknowledgement") }}</h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
            '</div>' +
            '<div class="modal-body">' +
            '<p class="mb-0">{{ __("Employee acknowledgement received on hard copy") }}</p>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</button>' +
            '<button type="button" class="btn btn-primary" id="acknowledgement-confirm-yes">{{ __("Yes, Confirm") }}</button>' +
            '</div>' +
            '</div></div></div>';
        
        $('#acknowledgement-confirm-modal').remove();
        $('body').append(confirmModalHtml);
        $('#acknowledgement-confirm-modal').modal('show');
        
        $('#acknowledgement-confirm-yes').on('click', function() {
            $('#acknowledgement-confirm-modal').modal('hide');
            
            $('#acknowledgement-confirm-modal').on('hidden.bs.modal', function() {
                $(this).remove();
                
                $.ajax({
                    url: '{{ route('onboarding.update-step', ['id' => $process->id, 'step' => 3]) }}',
                    type: 'POST',
                    data: {
                        confirmed: 'yes',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        show_toastr('Success', data.message || '{{ __("Acknowledgement confirmed successfully") }}', 'success');
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





