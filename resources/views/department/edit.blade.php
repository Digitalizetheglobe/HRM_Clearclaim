
{{ Form::model($department, ['route' => ['department.update', $department->id], 'method' => 'PUT']) }}
<div class="modal-body">

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::select('branch_id', $branch, null, ['class' => 'form-control ', 'required' => 'required']) }}
                </div>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Department Name')]) }}
                </div>
                @error('name')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('punch_in_time', __('Punch In Time'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::time('punch_in_time', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <small class="text-muted">{{ __('Employees punching in after this time will be marked as late') }}</small>
                @error('punch_in_time')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
