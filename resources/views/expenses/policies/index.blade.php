@extends('layouts.admin')

@section('page-title')
    {{ __('Expense Policies') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Expense Policies') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('expense-policies.update') }}">
                    @csrf
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="no_bill_no_reimbursement" value="on" 
                                   {{ ($noBillPolicy && $noBillPolicy->value == 'on') ? 'checked' : '' }}>
                            {{ __('No Bill → No Reimbursement') }}
                        </label>
                        <small class="form-text text-muted">{{ __('If enabled, employees must upload receipt to submit expense.') }}</small>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Submit Within (Days)') }} <span class="text-danger">*</span></label>
                        <input type="number" name="submit_within_days" class="form-control" 
                               value="{{ $daysPolicy ? $daysPolicy->days_limit : 30 }}" 
                               min="1" max="365" required>
                        <small class="form-text text-muted">{{ __('Default: 30 days. Expenses older than this will be blocked.') }}</small>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('Update Policies') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


