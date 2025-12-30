@php
    $plan = Utility::getChatGPTSettings();
@endphp

{{ Form::model($leave, ['route' => ['leave.update', $leave->id], 'method' => 'PUT']) }}
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
                    {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'placeholder' => __('Select Employee')]) }}
                </div>
            </div>
        </div>
    @else
        {!! Form::hidden('employee_id', !empty($employees) ? $employees->id : 0, ['id' => 'employee_id']) !!}
    @endif

    {{-- Leave Duration Selection --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_duration', __('Leave Duration'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                <select name="leave_duration" id="leave_duration" class="form-control select" required>
                    <option value="">{{ __('Select Leave Duration') }}</option>
                    <option value="Full Day" @if($leave->leave_duration == 'Full Day') selected @endif>{{ __('Full Day') }}</option>
                    <option value="Half Day" @if($leave->leave_duration == 'Half Day') selected @endif>{{ __('Half Day') }}</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Leave Session (only for Half Day) --}}
    <div class="row" id="leave_session_row" style="display: {{ $leave->leave_duration == 'Half Day' ? 'block' : 'none' }};">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_session', __('Leave Session'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                <select name="leave_session" id="leave_session" class="form-control select">
                    <option value="">{{ __('Select Session') }}</option>
                    <option value="First Half" @if($leave->leave_session == 'First Half') selected @endif>{{ __('First Half') }}</option>
                    <option value="Second Half" @if($leave->leave_session == 'Second Half') selected @endif>{{ __('Second Half') }}</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Start and End Date --}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::text('start_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off', 'id' => 'start_date', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::text('end_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off', 'id' => 'end_date', 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_reason', __('Leave Reason'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::textarea('leave_reason', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Leave Reason'), 'rows' => '3']) }}
            </div>
        </div>
    </div>
    @role('Company')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('status', __('Status'), ['class' => 'col-form-label']) }}
                    <select name="status" id="" class="form-control select2">
                        <option value="">{{ __('Select Status') }}</option>
                        <option value="Pending" @if ($leave->status == 'Pending') selected="" @endif>{{ __('Pending') }}
                        </option>
                        <option value="Approved" @if ($leave->status == 'Approved') selected="" @endif>{{ __('Approved') }}
                        </option>
                        <option value="Reject" @if ($leave->status == 'Reject') selected="" @endif>{{ __('Reject') }}
                        </option>
                    </select>
                </div>
            </div>
        </div>
    @endrole
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

</div>
{{ Form::close() }}

    <script>
    $(document).ready(function() {
        setTimeout(() => {
            var employee_id = $('#employee_id').val();
            if (employee_id) {
                $('#employee_id').trigger('change');
            }
        }, 100);

        // Show/hide leave session based on leave duration
        $(document).on('change', '#leave_duration', function() {
            var leaveDuration = $(this).val();
            if (leaveDuration === 'Half Day') {
                $('#leave_session_row').show();
                $('#leave_session').prop('required', true);
                // For half day, start and end date should be the same
                if ($('#start_date').val()) {
                    $('#end_date').val($('#start_date').val());
                }
                $('#end_date').prop('readonly', true);
            } else if (leaveDuration === 'Full Day') {
                $('#leave_session_row').hide();
                $('#leave_session').prop('required', false);
                $('#leave_session').val('');
                $('#end_date').prop('readonly', false);
            }
        });

        // When start date changes for half day, update end date
        $(document).on('change', '#start_date', function() {
            if ($('#leave_duration').val() === 'Half Day') {
                $('#end_date').val($(this).val());
            }
        });

        // Initialize on page load
        if ($('#leave_duration').val() === 'Half Day') {
            $('#end_date').prop('readonly', true);
        }
    });
</script>
