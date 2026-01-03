@extends('layouts.admin')

@section('page-title')
    {{ __('Process Payment') }}
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Payment Processing') }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>{{ __('Employee') }}:</strong> {{ $expense->employee->name ?? '-' }}<br>
                        <strong>{{ __('Category') }}:</strong> {{ $expense->category->name ?? '-' }}<br>
                        <strong>{{ __('Amount') }}:</strong> {{ Auth::user()->priceFormat($expense->amount) }}<br>
                        <strong>{{ __('Expense Date') }}:</strong> {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}
                    </div>
                </div>

                <form method="POST" action="{{ route('finance.expenses.mark-paid', $expense->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Paid Date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="paid_date" class="form-control" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Payment Mode') }} <span class="text-danger">*</span></label>
                                <select name="payment_mode" class="form-control" required>
                                    <option value="bank">{{ __('Bank') }}</option>
                                    <option value="upi">{{ __('UPI') }}</option>
                                    <option value="cash">{{ __('Cash') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('Payment Proof') }}</label>
                                <input type="file" name="payment_proof" class="form-control" 
                                       accept="image/*,application/pdf">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">{{ __('Mark as Paid') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection











