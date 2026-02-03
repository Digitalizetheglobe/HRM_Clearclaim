<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('HR Records Feedback') }} - {{ $process->employee ? $process->employee->name : 'N/A' }}</h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="ti ti-message-circle" style="font-size: 48px; color: #28a745;"></i>
            </div>
            <h4 class="text-success">{{ __('Employee Feedback') }}</h4>
            <p class="text-muted">{{ __('Record any feedback from the employee regarding their experience.') }}</p>
        </div>

        @if($process->document_status == 'uploaded')
            <div class="alert alert-success">
                <i class="ti ti-check me-2"></i>
                {{ __('Experience Certificate has been successfully downloaded and confirmed.') }}
            </div>
        @endif

        <form id="feedbackForm" method="POST" action="{{ route('offboarding.update-step', ['id' => $process->id, 'step' => 9]) }}">
            @csrf
            <div class="mb-3">
                <label for="feedback" class="form-label">{{ __('Employee Feedback') }}</label>
                <textarea class="form-control" id="feedback" name="feedback" rows="4" placeholder="{{ __('Enter any feedback from the employee about their experience, suggestions for improvement, etc...') }}"></textarea>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success">
                    <i class="ti ti-check me-2"></i>
                    {{ __('Complete Offboarding') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var feedback = document.getElementById('feedback').value;
    if (!feedback.trim()) {
        alert('{{ __('Please provide employee feedback before completing the offboarding process.') }}');
        return false;
    }
    
    var formData = new FormData(this);
    
    $.ajax({
        url: this.action,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                show_toastr('Success', response.message || 'Offboarding completed successfully', 'success');
                // Close modal and reload page to show the completed step
                $('.modal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 500);
            } else {
                show_toastr('Error', response.error || 'An error occurred', 'error');
            }
        },
        error: function(xhr) {
            var errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'An error occurred';
            show_toastr('Error', errorMsg, 'error');
        }
    });
});
</script>
