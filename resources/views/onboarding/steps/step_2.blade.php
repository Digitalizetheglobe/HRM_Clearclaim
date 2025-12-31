<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <p><strong>{{ __('Instructions:') }}</strong></p>
                <ol>
                    <li>{{ __('Click the button below to edit the employee details page') }}</li>
                    <li>{{ __('Upload and verify required documents') }}</li>
                    <li>{{ __('Return here and confirm that documents have been uploaded and verified') }}</li>
                </ol>
            </div>
        </div>
        <div class="col-md-12 text-center">
            <a href="{{ route('employee.edit', \Crypt::encrypt($process->employee->id)) }}" 
               target="_blank"
               class="btn btn-primary btn-lg">
                <i class="ti ti-edit me-2"></i>{{ __('Edit Employee & Upload Documents') }}
            </a>
        </div>
        <div class="col-md-12 mt-4">
            <hr>
            <h6>{{ __('Confirmation') }}</h6>
            <p>{{ __('Have you uploaded and verified all required documents?') }}</p>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="button" class="btn btn-success" id="confirm-document-upload-btn">
        <i class="ti ti-check me-2"></i>{{ __('Yes, Documents Uploaded & Verified') }}
    </button>
</div>

<script>
    $('#confirm-document-upload-btn').on('click', function() {
        var confirmModalHtml = '<div class="modal fade" id="document-upload-confirm-modal" tabindex="-1" role="dialog">' +
            '<div class="modal-dialog modal-dialog-centered" role="document">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title">{{ __("Confirm Document Upload") }}</h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
            '</div>' +
            '<div class="modal-body">' +
            '<p class="mb-0">{{ __("Documents uploaded and verified successfully") }}</p>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</button>' +
            '<button type="button" class="btn btn-primary" id="document-upload-confirm-yes">{{ __("Yes, Confirm") }}</button>' +
            '</div>' +
            '</div></div></div>';
        
        $('#document-upload-confirm-modal').remove();
        $('body').append(confirmModalHtml);
        $('#document-upload-confirm-modal').modal('show');
        
        $('#document-upload-confirm-yes').on('click', function() {
            $('#document-upload-confirm-modal').modal('hide');
            
            $('#document-upload-confirm-modal').on('hidden.bs.modal', function() {
                $(this).remove();
                
                $.ajax({
                    url: '{{ route('onboarding.update-step', ['id' => $process->id, 'step' => 2]) }}',
                    type: 'POST',
                    data: {
                        confirmed: 'yes',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            show_toastr('Success', data.message || '{{ __("Documents verified successfully") }}', 'success');
                            location.reload();
                        }
                    },
                    error: function(data) {
                        show_toastr('Error', data.responseJSON.error || '{{ __("An error occurred") }}', 'error');
                    }
                });
            });
        });
    });
</script>


