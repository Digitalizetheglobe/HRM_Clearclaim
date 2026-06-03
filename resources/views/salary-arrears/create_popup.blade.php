{{ Form::open(['route' => ['salary-arrears-popup.store'], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group mb-3">
                {{ Form::label('pending_month', __('Pending Month'), ['class' => 'col-form-label']) }} <span class="text-danger">*</span>
                {{ Form::month('pending_month', isset($arrear) ? \Carbon\Carbon::parse($arrear->pending_month)->format('Y-m') : '', ['class' => 'form-control', 'required' => 'required']) }}
                <small class="text-muted">{{ __('The past month this arrear corresponds to.') }}</small>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group mb-3">
                {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }} <span class="text-danger">*</span>
                {{ Form::number('amount', isset($arrear) ? $arrear->amount : 0, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0']) }}
                <small class="text-muted">{{ __('Set amount to 0 to remove an existing arrear.') }}</small>
            </div>
        </div>
        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
        <input type="hidden" name="payment_month" value="{{ $month }}">
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ isset($arrear) ? __('Update') : __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
