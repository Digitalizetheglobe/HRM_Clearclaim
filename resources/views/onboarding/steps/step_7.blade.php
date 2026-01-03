<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-success">
                <h5 class="mb-3"><i class="ti ti-check-circle me-2"></i>{{ __('Onboarding Completed!') }}</h5>
                <p>{{ __('Congratulations! The employee has successfully completed all onboarding steps.') }}</p>
            </div>
        </div>
        <div class="col-md-12">
            <h6>{{ __('Employee Information') }}</h6>
            <table class="table table-bordered">
                <tr>
                    <th>{{ __('Name') }}</th>
                    <td>{{ $process->employee->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Email') }}</th>
                    <td>{{ $process->employee->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Completed At') }}</th>
                    <td>{{ $process->onboarding_completed_at ? \Auth::user()->dateFormat($process->onboarding_completed_at) : 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
</div>



