@extends('layouts.admin')

@section('page-title')
    {{ __('Expense Approval') }}
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Expense Request Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>{{ __('Employee') }}:</strong> {{ $expense->employee->name ?? '-' }}<br>
                        <strong>{{ __('Category') }}:</strong> {{ $expense->category->name ?? '-' }}<br>
                        <strong>{{ __('Amount') }}:</strong> {{ Auth::user()->priceFormat($expense->amount) }}<br>
                        <strong>{{ __('Expense Date') }}:</strong> {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}
                    </div>
                    <div class="col-md-6">
                        <strong>{{ __('Description') }}:</strong><br>
                        <p>{{ $expense->description ?? '-' }}</p>
                    </div>
                </div>

                @if($expense->receipt_file && count($expense->receipt_file) > 0)
                <div class="mb-3">
                    <strong>{{ __('Receipt Files') }}:</strong><br>
                    @php
                        $receiptPath = \App\Models\Utility::get_file('uploads/expense_receipts/');
                    @endphp
                    @foreach($expense->receipt_file as $file)
                        <a href="{{ $receiptPath . $file }}" target="_blank" class="btn btn-sm btn-info">
                            <i class="ti ti-download"></i> {{ __('View Receipt') }}
                        </a>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('manager.expenses.approve', $expense->id) }}" class="d-inline">
                    @csrf
                    <div class="form-group">
                        <label>{{ __('Remark') }}</label>
                        <textarea name="remark" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">{{ __('Approve') }}</button>
                </form>

                <form method="POST" action="{{ route('manager.expenses.reject', $expense->id) }}" class="d-inline">
                    @csrf
                    <div class="form-group">
                        <label>{{ __('Rejection Reason') }}</label>
                        <textarea name="remark" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">{{ __('Reject') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

