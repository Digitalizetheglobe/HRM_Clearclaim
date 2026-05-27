@php
    $setting = App\Models\Utility::settings();
    $plan = Utility::getChatGPTSettings();
@endphp
{{ Form::open(['url' => 'leave', 'method' => 'post']) }}
<div class="modal-body">

    @if ($plan->enable_chatgpt == 'on')
        <div class="card-footer text-end">
            <a href="#" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true"
                data-url="{{ route('generate', ['leave']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
                <i class="fas fa-robot"></i>{{ __(' Generate With AI') }}
            </a>
        </div>
    @endif

    @if (\Auth::user()->type != 'employee')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                    {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
                </div>
            </div>
        </div>
    @else
        {!! Form::hidden('employee_id', !empty($employees) ? $employees->id : 0, ['id' => 'employee_id']) !!}
    @endif

    {{-- Monthly Leave Limit Warning --}}
    @if(isset($monthlyLeaveInfo) && $monthlyLeaveInfo['exceeded'])
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle"></i> 
            <strong>{{ __('Monthly Leave Limit Reached!') }}</strong><br>
            {{ __('You have already used '.$monthlyLeaveInfo['used'].' paid leaves this month. Maximum 2 paid leaves allowed per month.') }}<br>
            <strong>{{ __('Your leave will be automatically marked as LOP (Loss of Pay) and will NOT be deducted from your annual leave balance.') }}</strong>
        </div>
    @elseif(isset($monthlyLeaveInfo) && $monthlyLeaveInfo['remaining'] <= 0.5)
        <div class="alert alert-info">
            <i class="ti ti-info-circle"></i> 
            <strong>{{ __('Monthly Leave Limit Notice') }}</strong><br>
            {{ __('You have used '.$monthlyLeaveInfo['used'].' paid leaves this month. Only '.$monthlyLeaveInfo['remaining'].' paid leave(s) remaining.') }}<br>
            {{ __('Any leaves beyond this will be automatically marked as LOP.') }}
        </div>
    @endif

    {{-- Leave Duration Selection --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_duration', __('Leave Duration'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                <select name="leave_duration" id="leave_duration" class="form-control select" required>
                    <option value="">{{ __('Select Leave Duration') }}</option>
                    <option value="Full Day">{{ __('Full Day') }}</option>
                    <option value="Half Day">{{ __('Half Day') }}</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Leave Type Selection (Paid/LOP) --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_type_selection', __('Leave Type'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                <select name="leave_type_selection" id="leave_type_selection" class="form-control select" required>
                    <option value="">{{ __('Select Leave Type') }}</option>
                    @if(isset($monthlyLeaveInfo) && $monthlyLeaveInfo['exceeded'])
                        {{-- Only LOP option if limit exceeded --}}
                        <option value="lop">{{ __('LOP (Loss of Pay) - Required') }}</option>
                    @elseif(isset($monthlyLeaveInfo) && $monthlyLeaveInfo['remaining'] > 0)
                        {{-- Both options if still have remaining paid leaves --}}
                        <option value="paid">{{ __('Paid Leave (Remaining: ') }}{{ number_format($monthlyLeaveInfo['remaining'], 2) }}{{ __(')') }}</option>
                        <option value="lop">{{ __('LOP (Loss of Pay)') }}</option>
                    @else
                        {{-- Default: both options --}}
                        <option value="paid">{{ __('Paid Leave') }}</option>
                        <option value="lop">{{ __('LOP (Loss of Pay)') }}</option>
                    @endif
                </select>
                <small class="form-text text-muted">
                    @if(isset($monthlyLeaveInfo) && $monthlyLeaveInfo['exceeded'])
                        <span class="text-danger">{{ __('You must select LOP as you have used all paid leaves this month.') }}</span>
                    @else
                        {{ __('Paid leaves are deducted from your annual 15-leave balance. LOP leaves are not deducted.') }}
                    @endif
                </small>
            </div>
        </div>
    </div>

    {{-- Leave Session (only for Half Day) --}}
    <div class="row" id="leave_session_row" style="display: none;">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_session', __('Leave Session'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                <select name="leave_session" id="leave_session" class="form-control select">
                    <option value="">{{ __('Select Session') }}</option>
                    <option value="First Half">{{ __('First Half') }}</option>
                    <option value="Second Half">{{ __('Second Half') }}</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Start and End Date (only show when duration is selected) --}}
    <div class="row" id="date_row" style="display: none;">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::text('start_date', null, ['class' => 'form-control d_week current_date', 'autocomplete' => 'off', 'id' => 'start_date', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::text('end_date', null, ['class' => 'form-control d_week current_date', 'autocomplete' => 'off', 'id' => 'end_date', 'required' => 'required']) }}
            </div>
        </div>
    </div>

    {{-- Leave Reason --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_reason', __('Leave Reason'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::textarea('leave_reason', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Leave Reason'), 'rows' => '3']) }}
            </div>
        </div>
    </div>

    {{-- Google Calendar Sync --}}
    @if (isset($setting['is_enabled']) && $setting['is_enabled'] == 'on')
        <div class="form-group col-md-6">
            {{ Form::label('synchronize_type', __('Synchronize in Google Calendar?'), ['class' => 'form-label']) }}
            <div class="form-switch">
                <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calendar">
                <label class="form-check-label" for="switch-shadow"></label>
            </div>
        </div>
    @endif
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary" id="submit-btn">
</div>
{{ Form::close() }}

<script>
    // ── Leave limit constants from server ────────────────────────────────────
    var MONTHLY_LIMIT     = {{ $monthlyLeaveInfo['limit']     ?? 2 }};
    var MONTHLY_USED      = {{ $monthlyLeaveInfo['used']      ?? 0 }};
    var MONTHLY_REMAINING = Math.max(0, MONTHLY_LIMIT - MONTHLY_USED);
    // ────────────────────────────────────────────────────────────────────────

    $(document).ready(function () {

        // Set today as default date
        var now   = new Date();
        var today = now.getFullYear() + '-'
                  + String(now.getMonth() + 1).padStart(2, '0') + '-'
                  + String(now.getDate()).padStart(2, '0');
        $('.current_date').val(today);

    });

    // ── Show / hide date & session rows based on duration ───────────────────
    $(document).on('change', '#leave_duration', function () {
        var duration = $(this).val();

        if (duration) {
            $('#date_row').show();
            $('#start_date').prop('required', true);
            $('#end_date').prop('required', true);

            if (duration === 'Half Day') {
                $('#leave_session_row').show();
                $('#leave_session').prop('required', true);
                if ($('#start_date').val()) {
                    $('#end_date').val($('#start_date').val());
                }
                $('#end_date').prop('readonly', true);
            } else {
                $('#leave_session_row').hide();
                $('#leave_session').prop('required', false).val('');
                $('#end_date').prop('readonly', false);
            }

            // Re-apply max-date restriction after duration change
            applyEndDateMax();

        } else {
            $('#date_row, #leave_session_row').hide();
            $('#start_date, #end_date').prop('required', false);
            $('#leave_session').prop('required', false);
        }
    });

    // ── When start date changes ──────────────────────────────────────────────
    $(document).on('change', '#start_date', function () {
        var duration = $('#leave_duration').val();

        // For Half Day — end date = start date
        if (duration === 'Half Day') {
            $('#end_date').val($(this).val());
        } else {
            // Clear end date so user re-picks within the allowed range
            $('#end_date').val('');
        }

        applyEndDateMax();
        validateDays();
    });

    // ── When end date changes ────────────────────────────────────────────────
    $(document).on('change', '#end_date', validateDays);

    // ── When leave type changes ──────────────────────────────────────────────
    $(document).on('change', '#leave_type_selection', function () {
        applyEndDateMax();
        validateDays();
    });

    /**
     * Set the max attribute on end_date based on:
     *  - Paid leave  → start date + (MONTHLY_REMAINING - 1) days
     *  - LOP leave   → no restriction (any number of days)
     *  - Half Day    → same as start date (already readonly)
     */
    function applyEndDateMax() {
        var startVal  = $('#start_date').val();
        var leaveType = $('#leave_type_selection').val();
        var duration  = $('#leave_duration').val();

        if (!startVal || duration === 'Half Day') return;

        if (leaveType === 'paid') {
            // Max days the employee can still take this month (paid)
            var maxDays = MONTHLY_REMAINING;   // e.g. 2 if nothing used yet

            if (maxDays <= 0) {
                // No paid days left — lock end_date same as start
                $('#end_date').val(startVal).attr('max', startVal);
                showError('You have no paid leaves remaining this month. Please select <strong>LOP</strong>.');
                lockSubmit();
                return;
            }

            // Calculate max end date = start + (maxDays - 1)
            var startDate  = new Date(startVal);
            var maxEndDate = new Date(startDate);
            maxEndDate.setDate(startDate.getDate() + maxDays - 1);

            var maxStr = maxEndDate.getFullYear() + '-'
                       + String(maxEndDate.getMonth() + 1).padStart(2, '0') + '-'
                       + String(maxEndDate.getDate()).padStart(2, '0');

            $('#end_date').attr('max', maxStr);

            // If currently selected end date exceeds new max, reset it
            if ($('#end_date').val() && $('#end_date').val() > maxStr) {
                $('#end_date').val(maxStr);
            }

        } else {
            // LOP — remove restriction
            $('#end_date').removeAttr('max');
        }

        clearError();
        unlockSubmit();
    }

    /**
     * Validate selected days against monthly limit and update UI.
     */
    function validateDays() {
        var startVal  = $('#start_date').val();
        var endVal    = $('#end_date').val();
        var leaveType = $('#leave_type_selection').val();
        var duration  = $('#leave_duration').val();

        if (!startVal || !endVal) return;

        var totalDays;
        if (duration === 'Half Day') {
            totalDays = 0.5;
        } else {
            var start = new Date(startVal);
            var end   = new Date(endVal);
            totalDays = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
        }

        if (leaveType === 'paid') {
            if (totalDays > MONTHLY_REMAINING) {
                var remaining = MONTHLY_REMAINING > 0 ? MONTHLY_REMAINING : 0;
                showError(
                    '⚠️ You can only take <strong>' + remaining + '</strong> paid leave day(s) this month '
                    + '(Limit: ' + MONTHLY_LIMIT + ' | Used: ' + MONTHLY_USED + '). '
                    + (remaining === 0
                        ? 'Please select <strong>LOP</strong>.'
                        : 'Please reduce to <strong>' + remaining + '</strong> day(s) or select <strong>LOP</strong>.')
                );
                lockSubmit();
            } else {
                clearError();
                unlockSubmit();
            }
        } else {
            // LOP — always allowed
            clearError();
            unlockSubmit();
        }
    }

    function showError(msg) {
        $('#monthly-limit-error').remove();
        $('<div id="monthly-limit-error" class="alert alert-danger mt-2 mb-0">' + msg + '</div>')
            .insertBefore('#submit-btn');
    }

    function clearError()  { $('#monthly-limit-error').remove(); }
    function lockSubmit()  { $('#submit-btn').prop('disabled', true); }
    function unlockSubmit(){ $('#submit-btn').prop('disabled', false); }
</script>