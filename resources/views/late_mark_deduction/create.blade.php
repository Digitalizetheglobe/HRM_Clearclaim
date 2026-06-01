{{ Form::open(['route' => ['late-mark-deductions.store'], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('amount', __('Deduction (in Days)'), ['class' => 'col-form-label']) }}
                {{ Form::number('amount', isset($deduction) ? $deduction->amount : 0, ['class' => 'form-control', 'required' => 'required', 'step' => '0.5', 'min' => '0']) }}
            </div>
        </div>
        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
        <input type="hidden" name="payment_month" value="{{ $month }}">
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
