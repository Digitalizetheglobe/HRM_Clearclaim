@extends('layouts.admin')

@section('page-title')
    {{ __('Pending Expense Approvals') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Expense Approvals') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->employee->name ?? '-' }}</td>
                                    <td>{{ $expense->category->name ?? '-' }}</td>
                                    <td>{{ Auth::user()->priceFormat($expense->amount) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('manager.expenses.show', $expense->id) }}" 
                                           class="btn btn-sm btn-info text-white">
                                            <i class="ti ti-eye"></i> {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('No pending expenses found.') }}</td>
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









