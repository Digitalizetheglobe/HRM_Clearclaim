@extends('layouts.admin')

@section('page-title')
    {{ __('HR Approved Expenses') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.expenses.index') }}">{{ __('HR Approvals') }}</a></li>
    <li class="breadcrumb-item">{{ __('Approved') }}</li>
@endsection

@section('action-button')
    <div class="float-end">
        <a href="{{ route('hr.expenses.index') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Pending') }}
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Approved Expenses') }}</h5>
                <small class="text-muted">{{ __('Total Approved: ') . $expenses->count() }}</small>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Expense Date') }}</th>
                                <th>{{ __('Approved At') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->employee->name ?? '-' }}</td>
                                    <td>{{ $expense->category->name ?? '-' }}</td>
                                    <td><strong>{{ Auth::user()->priceFormat($expense->amount) }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                    <td>{{ $expense->hr_approved_at ? \Carbon\Carbon::parse($expense->hr_approved_at)->format('d M Y H:i') : '-' }}</td>
                                    <td>{!! $expense->status_badge !!}</td>
                                    <td>
                                        <a href="{{ route('hr.expenses.show', $expense->id) }}" 
                                           class="btn btn-sm btn-info text-white">
                                            <i class="ti ti-eye"></i> {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="py-4">
                                            <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                            <p class="mt-2 text-muted">{{ __('No approved expenses found.') }}</p>
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





