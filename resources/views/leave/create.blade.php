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
            {{ __('Any additional leaves must be applied as Leave Without Pay (LWP).') }}
        </div>
    @elseif(isset($monthlyLeaveInfo) && $monthlyLeaveInfo['remaining'] <= 0.5)
        <div class="alert alert-info">
            <i class="ti ti-info-circle"></i> 
            <strong>{{ __('Monthly Leave Limit Notice') }}</strong><br>
            {{ __('You have used '.$monthlyLeaveInfo['used'].' paid leaves this month. Only '.$monthlyLeaveInfo['remaining'].' paid leave(s) remaining.') }}
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
    $(document).ready(function() {
        // Set current date
        var now = new Date();
        var month = (now.getMonth() + 1).toString().padStart(2, '0');
        var day = now.getDate().toString().padStart(2, '0');
        var today = now.getFullYear() + '-' + month + '-' + day;
        $('.current_date').val(today);

        // Function to calculate business days between two dates (excluding weekends)
        function getBusinessDays(startDate, endDate) {
            var count = 0;
            var current = new Date(startDate);
            var end = new Date(endDate);
            
            while (current <= end) {
                var day = current.getDay();
                if (day !== 0 && day !== 6) { // Skip Sunday (0) and Saturday (6)
                    count++;
                }
                current.setDate(current.getDate() + 1);
            }
            return count;
        }

    });


    // Show/hide date fields and session based on leave duration
    $(document).on('change', '#leave_duration', function() {
        var leaveDuration = $(this).val();
        
        if (leaveDuration) {
            // Show date fields when duration is selected
            $('#date_row').show();
            $('#start_date').prop('required', true);
            $('#end_date').prop('required', true);
            
            if (leaveDuration === 'Half Day') {
                // Show session field for half day
                $('#leave_session_row').show();
                $('#leave_session').prop('required', true);
                // For half day, start and end date should be the same
                if ($('#start_date').val()) {
                    $('#end_date').val($('#start_date').val());
                }
                $('#end_date').prop('readonly', true);
            } else {
                // Hide session field for full day
                $('#leave_session_row').hide();
                $('#leave_session').prop('required', false);
                $('#leave_session').val('');
                $('#end_date').prop('readonly', false);
            }
        } else {
            // Hide everything if no duration selected
            $('#date_row').hide();
            $('#leave_session_row').hide();
            $('#start_date').prop('required', false);
            $('#end_date').prop('required', false);
            $('#leave_session').prop('required', false);
        }
    });

    // When start date changes for half day, update end date
    $(document).on('change', '#start_date', function() {
        if ($('#leave_duration').val() === 'Half Day') {
            $('#end_date').val($(this).val());
        }
    });

    // Add this to your @push('script-page') section
$(document).on('change', '#start_date, #end_date', function() {
    var startDate = new Date($('#start_date').val());
    var endDate = new Date($('#end_date').val());
    var leaveDuration = $('#leave_duration').val();
    
    if (startDate && endDate) {
        // Calculate difference in days
        var diffTime = Math.abs(endDate - startDate);
        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        // For half day, it's 0.5 days
        if (leaveDuration === 'Half Day') {
            diffDays = 0.5;
        }
        
        $('#total_leave_days').val(diffDays);
    }
});
</script>