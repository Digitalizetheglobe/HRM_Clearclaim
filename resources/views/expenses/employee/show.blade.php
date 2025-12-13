@extends('layouts.admin')

@section('page-title')
    {{ __('Expense Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">{{ __('My Expenses') }}</a></li>
    <li class="breadcrumb-item">{{ __('Expense Details') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Expense Request Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">{{ __('Category') }}</th>
                                <td>{{ $expense->category->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Amount') }}</th>
                                <td><strong>{{ Auth::user()->priceFormat($expense->amount) }}</strong></td>
                            </tr>
                            <tr>
                                <th>{{ __('Expense Date') }}</th>
                                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Submitted At') }}</th>
                                <td>{{ $expense->submitted_at ? \Carbon\Carbon::parse($expense->submitted_at)->format('d M Y H:i') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Status') }}</th>
                                <td>{!! $expense->status_badge !!}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            @if($expense->hr_remark)
                            <tr>
                                <th width="40%">{{ __('HR Remark') }}</th>
                                <td>{{ $expense->hr_remark }}</td>
                            </tr>
                            @endif
                            @if($expense->paid_date)
                            <tr>
                                <th>{{ __('Paid Date') }}</th>
                                <td>{{ \Carbon\Carbon::parse($expense->paid_date)->format('d M Y') }}</td>
                            </tr>
                            @endif
                            @if($expense->payment_mode)
                            <tr>
                                <th>{{ __('Payment Mode') }}</th>
                                <td>{{ ucfirst($expense->payment_mode) }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-12">
                        <h6>{{ __('Description') }}</h6>
                        <p>{{ $expense->description ?? '-' }}</p>
                    </div>
                    @if($expense->receipt_file && count($expense->receipt_file) > 0)
                    <div class="col-md-12">
                        <h6>{{ __('Receipt Files') }}</h6>
                        <div class="row">
                            @php
                                $receiptPath = \App\Models\Utility::get_file('uploads/expense_receipts/');
                            @endphp
                            @foreach($expense->receipt_file as $file)
                            <div class="col-md-3 mb-2">
                                <a href="{{ $receiptPath . $file }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="ti ti-download"></i> {{ __('View Receipt') }}
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if($expense->payment_proof)
                    <div class="col-md-12">
                        <h6>{{ __('Payment Proof') }}</h6>
                        @php
                            $paymentPath = \App\Models\Utility::get_file('uploads/payment_proofs/');
                        @endphp
                        <a href="{{ $paymentPath . $expense->payment_proof }}" target="_blank" class="btn btn-sm btn-success">
                            <i class="ti ti-download"></i> {{ __('View Payment Proof') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

