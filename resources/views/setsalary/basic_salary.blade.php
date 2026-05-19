{{ Form::model($employee, ['route' => ['employee.salary.update', $employee->id], 'method' => 'POST']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('employee_name', __('Employee Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('employee_name', $employee->name, ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group">
            {{ Form::label('salary', __('Salary'), ['class' => 'col-form-label']) }}
            {{ Form::number('salary', null, ['class' => 'form-control ', 'required' => 'required']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <button type="submit" class="btn  btn-primary">{{ __('Add Salary') }}</button>
</div>
{{ Form::close() }}
