@extends('layouts.admin')

@section('page-title')
    {{ __('My Expenses & Reimbursement') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('My Expenses') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('expenses.create') }}" class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i> {{ __('Add New Expense') }}
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0">{{ __('Total Reimbursed') }}</h6>
                        <h3 class="mb-0 text-success">{{ Auth::user()->priceFormat($totalReimbursed) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0">{{ __('Pending Amount') }}</h6>
                        <h3 class="mb-0 text-warning">{{ Auth::user()->priceFormat($pendingAmount) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0">{{ __('Total Expenses') }}</h6>
                        <h3 class="mb-0 text-primary">{{ $expenses->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                    <td>{{ $expense->category->name ?? '-' }}</td>
                                    <td>{{ Auth::user()->priceFormat($expense->amount) }}</td>
                                    <td>{!! $expense->status_badge !!}</td>
                                    <td>
                                        <a href="{{ route('expenses.show', $expense->id) }}" 
                                           class="btn btn-sm btn-info text-white" 
                                           data-bs-toggle="tooltip" 
                                           title="{{ __('View Details') }}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('No expenses found.') }}</td>
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









