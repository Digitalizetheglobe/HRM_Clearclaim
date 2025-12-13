{{ Form::model($todo, ['route' => ['todo.update', $todo->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('task', __('Task Title'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('task', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Task Title')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('priority', __('Priority'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::select('priority', ['high' => __('High'), 'medium' => __('Medium'), 'low' => __('Low')], null, ['class' => 'form-control']) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('expires_at', __('Due Date'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::date('expires_at', $todo->expires_at ? \Carbon\Carbon::parse($todo->expires_at)->format('Y-m-d') : null, ['class' => 'form-control']) }}
                </div>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('is_completed', __('Task Status'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::select('is_completed', [0 => __('Pending'), 1 => __('Completed')], null, ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update Task') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
