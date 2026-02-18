{{ Form::model($ip, ['route' => ['edit.ip', $ip->id], 'method' => 'POST']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('ip', __('IP Address'), ['class' => 'col-form-label']) }}
            {{ Form::text('ip', null, ['class' => 'form-control', 'placeholder' => 'Enter IP Address (e.g., 103.197.74.48)']) }}
            <small class="form-text text-muted">
                {{ __('Only the first three parts of the IP will be checked (e.g., 103.197.74.x)') }}
            </small>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
