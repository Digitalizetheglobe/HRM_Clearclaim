@php
    $setting = App\Models\Utility::settings();
    $plan = Utility::getChatGPTSettings();
@endphp
{{ Form::open(['url' => 'holiday', 'method' => 'post']) }}
<div class="modal-body">

    @if ($plan->enable_chatgpt == 'on')
    <div class="card-footer text-end">
        <a href="#" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true"
            data-url="{{ route('generate', ['holiday']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
            title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
            <i class="fas fa-robot"></i>{{ __(' Generate With AI') }}
        </a>
    </div>
    @endif

    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('occasion', __('Occasion'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
            {{ Form::text('occasion', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Occasion']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
            {{ Form::date('date', null, ['class' => 'form-control current_date', 'id' => 'holiday_date_select', 'required' => 'required', 'autocomplete' => 'off']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('day', __('Day'), ['class' => 'col-form-label']) }}
            {{ Form::text('day', null, ['class' => 'form-control', 'id' => 'holiday_day_display', 'readonly' => 'readonly', 'placeholder' => 'Select Date']) }}
        </div>
        @if(isset($setting['is_enabled']) && $setting['is_enabled'] =='on')
        <div class="form-group col-md-6">
            {{ Form::label('synchronize_type', __('Synchroniz in Google Calendar ?'), ['class' => 'form-label']) }}
            <div class=" form-switch">
                <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow"
                     value="google_calender">
                <label class="form-check-label" for="switch-shadow"></label>
            </div>
        </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>

{{ Form::close() }}

<script>
    $(document).ready(function() {
        var now = new Date();
        var month = (now.getMonth() + 1);
        var day = now.getDate();
        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;
        var today = now.getFullYear() + '-' + month + '-' + day;
        if (!$('#holiday_date_select').val()) {
            $('#holiday_date_select').val(today);
        }
        $('#holiday_date_select').trigger('change');
    });

    $(document).on('change', '#holiday_date_select', function() {
        var dateVal = $(this).val();
        if (dateVal) {
            var dateObj = new Date(dateVal);
            if (!isNaN(dateObj.getTime())) {
                var dayName = dateObj.toLocaleDateString('en-US', { weekday: 'long' });
                $('#holiday_day_display').val(dayName);
            } else {
                $('#holiday_day_display').val('');
            }
        } else {
            $('#holiday_day_display').val('');
        }
    });
</script>