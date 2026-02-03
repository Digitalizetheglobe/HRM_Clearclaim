<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('Manager Approval') }} - {{ $process->employee ? $process->employee->name : 'N/A' }}</h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="ti ti-user-check" style="font-size: 48px; color: #ffc107;"></i>
            </div>
            <h4 class="text-warning">{{ __('Manager Approval Pending') }}</h4>
            <p class="text-muted">{{ __('This resignation is waiting for manager approval.') }}</p>
        </div>

        @if($process->resignation)
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notice Date') }}</label>
                        <p class="form-control-plaintext">{{ \Auth::user()->dateFormat($process->resignation->notice_date) }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Resignation Date') }}</label>
                        <p class="form-control-plaintext">{{ \Auth::user()->dateFormat($process->resignation->resignation_date) }}</p>
                    </div>
                </div>
            </div>

            @if($process->resignation->description)
                <div class="mb-3">
                    <label class="form-label">{{ __('Reason') }}</label>
                    <p class="form-control-plaintext">{{ $process->resignation->description }}</p>
                </div>
            @endif
        @endif

        <form id="managerApprovalForm" method="POST" action="{{ route('offboarding.update.step', ['id' => $process->id, 'step' => 1]) }}">
            @csrf
            <div class="mb-3">
                <label for="manager_comment" class="form-label">{{ __('Manager Comment') }}</label>
                <textarea class="form-control" id="manager_comment" name="manager_comment" rows="3" placeholder="{{ __('Add any comments or notes...') }}"></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-danger" onclick="rejectResignation()">
                    <i class="ti ti-x me-2"></i>{{ __('Reject') }}
                </button>
                <button type="button" class="btn btn-success" onclick="approveResignation()">
                    <i class="ti ti-check me-2"></i>{{ __('Approve') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function approveResignation() {
    if (confirm('{{ __('Are you sure you want to approve this resignation?') }}')) {
        var form = document.getElementById('managerApprovalForm');
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action';
        input.value = 'approve';
        form.appendChild(input);
        form.submit();
    }
}

function rejectResignation() {
    var comment = document.getElementById('manager_comment').value;
    if (!comment.trim()) {
        alert('{{ __('Please provide a reason for rejection.') }}');
        return;
    }
    
    if (confirm('{{ __('Are you sure you want to reject this resignation?') }}')) {
        var form = document.getElementById('managerApprovalForm');
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action';
        input.value = 'reject';
        form.appendChild(input);
        form.submit();
    }
}
</script>
