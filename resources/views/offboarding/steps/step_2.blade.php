<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('Resignation / Initiated Exit') }} - {{ $process->employee ? $process->employee->name : 'N/A' }}</h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="ti ti-file-text" style="font-size: 48px; color: #17a2b8;"></i>
            </div>
            <h4 class="text-info">{{ __('Resignation Review') }}</h4>
            <p class="text-muted">{{ __('Review the resignation details and approve to continue with offboarding process.') }}</p>
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
                        <label class="form-label">{{ __('Last Working Day') }}</label>
                        <p class="form-control-plaintext">{{ \Auth::user()->dateFormat($process->resignation->resignation_date) }}</p>
                    </div>
                </div>
            </div>

            @if($process->resignation->description)
                <div class="mb-3">
                    <label class="form-label">{{ __('Reason for Resignation') }}</label>
                    <p class="form-control-plaintext">{{ $process->resignation->description }}</p>
                </div>
            @endif

            @if($process->manager_comment)
                <div class="mb-3">
                    <label class="form-label">{{ __('Manager Comment') }}</label>
                    <div class="alert alert-warning">
                        <small class="text-muted">{{ __('Manager Approval Status') }}:</small>
                        <p class="mb-0">{{ $process->manager_comment }}</p>
                    </div>
                </div>
            @endif

            <form id="resignationApprovalForm" method="POST" action="{{ route('offboarding.update-step', ['id' => $process->id, 'step' => 2]) }}">
                @csrf
                <div class="mb-3">
                    <label for="hr_comment" class="form-label">{{ __('HR Comment') }}</label>
                    <textarea class="form-control" id="hr_comment" name="hr_comment" rows="3" placeholder="{{ __('Add any comments or notes...') }}"></textarea>
                </div>

                <div class="mb-3">
                    <label for="last_working_day" class="form-label">{{ __('Confirm Last Working Day') }} *</label>
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
                    <button type="button" class="btn btn-success" onclick="approveResignation()">
                        <i class="ti ti-check me-2"></i>{{ __('Approve') }}
                    </button>
                </div>
            </form>
        @else
            <div class="alert alert-warning">
                {{ __('No resignation details found.') }}
            </div>
        @endif
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
    
    if (confirm('{{ __('Are you sure you want to approve this resignation and continue with offboarding?') }}')) {
        var form = document.getElementById('resignationApprovalForm');
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action';
        input.value = 'approve';
        form.appendChild(input);
        
        $.ajax({
            url: form.action,
            type: 'POST',
            data: $(form).serialize(),
            success: function(data) {
                show_toastr('Success', data.message || 'Resignation approved successfully', 'success');
                // Redirect to offboarding page after approval
                setTimeout(function() {
                    window.location.href = '{{ route("offboarding.index") }}';
                }, 1000);
            },
            error: function(data) {
                var errorMsg = data.responseJSON && data.responseJSON.error ? data.responseJSON.error : 'An error occurred';
                show_toastr('Error', errorMsg, 'error');
            }
        });
    }
}

function rejectResignation() {
    var comment = document.getElementById('hr_comment').value;
    if (!comment.trim()) {
        alert('{{ __('Please provide a reason for rejection.') }}');
        return;
    }
    
    if (confirm('{{ __('Are you sure you want to reject this resignation?') }}')) {
        var form = document.getElementById('resignationApprovalForm');
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action';
        input.value = 'reject';
        form.appendChild(input);
        
        $.ajax({
            url: form.action,
            type: 'POST',
            data: $(form).serialize(),
            success: function(data) {
                show_toastr('Success', data.message || 'Resignation rejected successfully', 'success');
                location.reload();
            },
            error: function(data) {
                var errorMsg = data.responseJSON && data.responseJSON.error ? data.responseJSON.error : 'An error occurred';
                show_toastr('Error', errorMsg, 'error');
            }
        });
    }
}
</script>

