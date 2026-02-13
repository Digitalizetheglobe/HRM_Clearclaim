{{ Form::model($ip, ['route' => ['edit.ip', $ip->id], 'method' => 'POST']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('ip', __('IP Address'), ['class' => 'col-form-label']) }}
            {{ Form::text('ip', null, ['class' => 'form-control', 'placeholder' => 'Enter IP Address']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('type', __('IP Type'), ['class' => 'col-form-label']) }}
            {{ Form::select('type', ['public' => __('Public IP'), 'local' => __('Local IP')], $ip->type, ['class' => 'form-control']) }}
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <small class="text-muted">
                <strong>{{ __('Public IP:') }}</strong> {{ __('For remote access from any location (e.g., home, travel).') }}<br>
                <strong>{{ __('Local IP:') }}</strong> {{ __('For office Wi-Fi access only (e.g., 192.168.1.x).') }}
            </small>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

</div>
{{ Form::close() }}
