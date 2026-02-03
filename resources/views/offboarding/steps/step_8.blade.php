<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('HR Uploads / Downloads') }} - {{ $process->employee ? $process->employee->name : 'N/A' }}</h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="ti ti-download" style="font-size: 48px; color: #17a2b8;"></i>
            </div>
            <h4 class="text-info">{{ __('Download Experience Certificate') }}</h4>
            <p class="text-muted">{{ __('Please download the Experience Certificate from the employee details page.') }}</p>
        </div>

        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            {{ __('Click the button below to go to the employee details page where you can download the Experience Certificate.') }}
        </div>

        @if($process->employee)
            <div class="text-center">
                <a href="{{ route('employee.show', \Crypt::encrypt($process->employee->id)) }}" 
                   class="btn btn-primary btn-lg" 
                   target="_blank">
                    <i class="ti ti-external-link me-2"></i>
                    {{ __('View Employee Details & Download Certificate') }}
                </a>
            </div>
        @endif

        <div class="mt-4">
            <small class="text-muted">
                {{ __('After downloading the certificate, you will be asked to confirm the download when you return to this page.') }}
            </small>
        </div>
    </div>
</div>
