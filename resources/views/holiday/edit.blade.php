@php
    $plan = Utility::getChatGPTSettings();
@endphp

{{ Form::model($holiday, ['route' => ['holiday.update', $holiday->id], 'method' => 'PUT']) }}
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
        <div class="form-group">
            {{ Form::label('occasion', __('Occasion'), ['class' => 'col-form-label']) }}
            {{ Form::text('occasion', null, ['class' => 'form-control','placeholder'=>'Enter Occasion']) }}
        </div>
        <div class="row col-md-12">
            <div class="form-group col-md-6">
                {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::date('date', null, ['class' => 'form-control', 'id' => 'holiday_date_select', 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('day', __('Day'), ['class' => 'col-form-label']) }}
                {{ Form::text('day', null, ['class' => 'form-control', 'id' => 'holiday_day_display', 'readonly' => 'readonly']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
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

