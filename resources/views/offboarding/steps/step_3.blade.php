<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('HR Approval') }} - {{ $process->employee ? $process->employee->name : 'N/A' }}</h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="ti ti-user-check" style="font-size: 48px; color: #17a2b8;"></i>
            </div>
            <h4 class="text-info">{{ __('Pending HR Approval') }}</h4>
            <p class="text-muted">{{ __('This resignation is waiting for HR department approval.') }}</p>
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

            @if($process->manager_comment)
                <div class="mb-3">
                    <label class="form-label">{{ __('Manager Comment') }}</label>
                    <div class="alert alert-warning">
                        <small class="text-muted">{{ __('Approved by Manager') }}:</small>
                        <p class="mb-0">{{ $process->manager_comment }}</p>
                    </div>
                </div>
            @endif
        @endif

        <form id="hrApprovalForm" method="POST" action="{{ route('offboarding.update-step', [$process->id, 3]) }}">
            @csrf
            <div class="mb-3">
                <label for="hr_comment" class="form-label">{{ __('HR Comment') }}</label>
                <textarea class="form-control" id="hr_comment" name="hr_comment" rows="3" placeholder="{{ __('Add any comments or notes...') }}"></textarea>
            </div>

            <div class="mb-3">
                <label for="last_working_day" class="form-label">{{ __('Last Working Day') }} *</label>
                <input type="date" class="form-control" id="last_working_day" name="last_working_day" 
                       value="{{ $process->resignation ? $process->resignation->resignation_date : '' }}" required>
            </div>

            <div class="mb-3">
                <label for="notice_period_days" class="form-label">{{ __('Notice Period Days') }} *</label>
                <input type="number" class="form-control" id="notice_period_days" name="notice_period_days" 
                       min="0" placeholder="{{ __('Number of notice period days') }}" required>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-danger" onclick="rejectResignation()">
                    <i class="ti ti-x me-2"></i>{{ __('Reject') }}
                </button>
                <button type="button" class="btn btn-info" onclick="approveResignation()">
                    <i class="ti ti-check me-2"></i>{{ __('Approve') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Calculate notice period days when last working day changes
    $('#last_working_day').on('change', function() {
        var lastWorkingDay = new Date($(this).val());
        var noticeDate = new Date('{{ $process->resignation ? $process->resignation->notice_date : '' }}');
        
        if (lastWorkingDay && noticeDate) {
            var timeDiff = lastWorkingDay.getTime() - noticeDate.getTime();
            var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            $('#notice_period_days').val(daysDiff);
        }
    });
    
    // Initial calculation
    $('#last_working_day').trigger('change');
});

function approveResignation() {
    var lastWorkingDay = $('#last_working_day').val();
    var noticePeriodDays = $('#notice_period_days').val();
    
    if (!lastWorkingDay || !noticePeriodDays) {
        alert('{{ __('Please fill in all required fields.') }}');
        return;
    }
    
    if (confirm('{{ __('Are you sure you want to approve this resignation?') }}')) {
        var form = document.getElementById('hrApprovalForm');
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action';
        input.value = 'approve';
        form.appendChild(input);
        form.submit();
    }
}

function rejectResignation() {
    var comment = document.getElementById('hr_comment').value;
    if (!comment.trim()) {
        alert('{{ __('Please provide a reason for rejection.') }}');
        return;
    }
    
    if (confirm('{{ __('Are you sure you want to reject this resignation?') }}')) {
        var form = document.getElementById('hrApprovalForm');
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action';
        input.value = 'reject';
        form.appendChild(input);
        form.submit();
    }
}
</script>
