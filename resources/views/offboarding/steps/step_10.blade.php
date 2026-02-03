<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('Offboarding Completed') }} - {{ $process->employee ? $process->employee->name : 'N/A' }}</h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="ti ti-circle-check" style="font-size: 48px; color: #28a745;"></i>
            </div>
            <h4 class="text-success">{{ __('Offboarding Process Completed') }}</h4>
            <p class="text-muted">{{ __('The employee offboarding process has been successfully completed.') }}</p>
        </div>

        <div class="alert alert-success">
            <i class="ti ti-check me-2"></i>
            {{ __('All offboarding steps have been completed successfully.') }}
        </div>

        @if($process->employee)
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Employee Name') }}</label>
                        <p class="form-control-plaintext">{{ $process->employee->name }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Employee Email') }}</label>
                        <p class="form-control-plaintext">{{ $process->employee->email }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Process Started') }}</label>
                    <p class="form-control-plaintext">{{ \Auth::user()->dateFormat($process->created_at) }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Process Completed') }}</label>
                    <p class="form-control-plaintext">{{ \Auth::user()->dateFormat($process->updated_at) }}</p>
                </div>
            </div>
        </div>

        @if($process->employee_feedback)
            <div class="mb-3">
                <label class="form-label">{{ __('Employee Feedback') }}</label>
                <p class="form-control-plaintext">{{ $process->employee_feedback }}</p>
            </div>
        @endif

        <div class="text-center mt-4">
            <button type="button" class="btn btn-info" onclick="showOffboardingCompletedMessage()">
                <i class="ti ti-info-circle me-2"></i>
                {{ __('View Details') }}
            </button>
            <button type="button" class="btn btn-secondary ms-2" onclick="window.parent.location.reload()">
                <i class="ti ti-refresh me-2"></i>
                {{ __('Close') }}
            </button>
        </div>
    </div>
</div>

<script>
function showOffboardingCompletedMessage() {
    // Use parent window to show the modal since we're in an iframe/popup
    if (window.parent && window.parent.showOffboardingCompletedPopup) {
        window.parent.showOffboardingCompletedPopup();
    } else {
        // Fallback: create modal in current window
        alert('{{ __("This employee has been properly offboarded.") }}');
    }
}
</script>
    </div>
</div>
