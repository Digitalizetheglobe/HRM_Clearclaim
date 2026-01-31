@extends('layouts.admin')

@section('page-title')
    {{ __('Finance Completed Payments') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.expenses.index') }}">{{ __('Finance Payments') }}</a></li>
    <li class="breadcrumb-item">{{ __('Completed') }}</li>
@endsection

@section('action-button')
    <div class="float-end">
        <a href="{{ route('finance.expenses.index') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Pending') }}
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Completed Payments') }}</h5>
                <small class="text-muted">{{ __('Total Paid: ') . $expenses->count() }}</small>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Paid Date') }}</th>
                                <th>{{ __('Payment Mode') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->employee->name ?? '-' }}</td>
                                    <td>{{ $expense->category->name ?? '-' }}</td>
                                    <td><strong>{{ Auth::user()->priceFormat($expense->amount) }}</strong></td>
                                    <td>{{ $expense->paid_date ? \Carbon\Carbon::parse($expense->paid_date)->format('d M Y') : '-' }}</td>
                                    <td>{{ $expense->payment_mode ? ucfirst($expense->payment_mode) : '-' }}</td>
                                    <td>
                                        <a href="{{ route('finance.expenses.show', $expense->id) }}" 
                                           class="btn btn-sm btn-info text-white">
                                            <i class="ti ti-eye"></i> {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="py-4">
                                            <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                            <p class="mt-2 text-muted">{{ __('No completed payments found.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
















