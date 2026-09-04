@extends('layouts.admin')

@section('page-title')
    {{ __('Expenses & Reimbursement Management') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Expenses Management') }}</li>
@endsection

@section('action-button')
    @if(Auth::user()->hasCompanyAccess() || Auth::user()->type == 'super admin')
        <a href="{{ route('expense-categories.index') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-tag"></i> {{ __('Manage Categories') }}
        </a>
    @endif
@endsection
<!-- latest code add -->

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-12 mb-3">
        <div class="row">
            @if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin']))
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0">{{ __('Pending Approvals') }}</h6>
                        <h3 class="mb-0 text-warning">{{ $stats['total_pending'] }}</h3>
                        <small class="text-muted">{{ __('Amount: ') . Auth::user()->priceFormat($stats['pending_amount']) }}</small>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0">{{ __('Finance Pending') }}</h6>
                        <h3 class="mb-0 text-primary">{{ $stats['total_finance_pending'] }}</h3>
                        <small class="text-muted">{{ __('Amount: ') . Auth::user()->priceFormat($stats['finance_pending_amount']) }}</small>
                    </div>
                </div>
            </div>
            @if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin']))
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0">{{ __('Paid') }}</h6>
                        <h3 class="mb-0 text-success">{{ $stats['total_paid'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-0">{{ __('Rejected') }}</h6>
                        <h3 class="mb-0 text-danger">{{ $stats['total_rejected'] }}</h3>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Pending Approvals Tab -->
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    @if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin']))
                    <li class="nav-item">
                        <a class="nav-link {{ !$isFinanceEmployee ? 'active' : '' }}" data-bs-toggle="tab" href="#pending" role="tab">
                            {{ __('Pending Approvals') }} <span class="badge bg-warning">{{ $pending->count() }}</span>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link {{ $isFinanceEmployee ? 'active' : '' }}" data-bs-toggle="tab" href="#finance" role="tab">
                            {{ __('Finance Pending') }} <span class="badge bg-primary">{{ $financePending->count() }}</span>
                        </a>
                    </li>
                    @if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin']))
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#paid" role="tab">
                            {{ __('Paid') }} <span class="badge bg-success">{{ $paid->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#rejected" role="tab">
                            {{ __('Rejected') }} <span class="badge bg-danger">{{ $rejected->count() }}</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Pending Tab -->
                    @if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin']))
                    <div class="tab-pane fade {{ !$isFinanceEmployee ? 'show active' : '' }}" id="pending" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Expense Date') }}</th>
                                        <th>{{ __('Submitted At') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pending as $expense)
                                        <tr>
                                            <td>{{ $expense->employee->name ?? '-' }}</td>
                                            <td>{{ $expense->category->name ?? '-' }}</td>
                                            <td><strong>{{ Auth::user()->priceFormat($expense->amount) }}</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                            <td>{{ $expense->submitted_at ? \Carbon\Carbon::parse($expense->submitted_at)->format('d M Y H:i') : '-' }}</td>
                                            <td>
                                                <a href="{{ route('expenses.show', $expense->id) }}" 
                                                   class="btn btn-sm btn-primary text-white" title="{{ __('Review & Approve') }}">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                @if(in_array(Auth::user()->type, ['company', 'super admin']))
                                                <form action="{{ route('expenses.destroy', $expense->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this expense? This action cannot be undone.') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger text-white" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div class="py-4">
                                                    <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                                    <p class="mt-2 text-muted">{{ __('No pending expense requests found.') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Finance Pending Tab -->
                    <div class="tab-pane fade {{ $isFinanceEmployee ? 'show active' : '' }}" id="finance" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table" id="pc-dt-simple-2">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Approved At') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($financePending as $expense)
                                        <tr>
                                            <td>{{ $expense->employee->name ?? '-' }}</td>
                                            <td>{{ $expense->category->name ?? '-' }}</td>
                                            <td><strong>{{ Auth::user()->priceFormat($expense->amount) }}</strong></td>
                                            <td>{{ $expense->hr_approved_at ? \Carbon\Carbon::parse($expense->hr_approved_at)->format('d M Y H:i') : '-' }}</td>
                                            <td>
                                                <a href="{{ route('expenses.show', $expense->id) }}" 
                                                   class="btn btn-sm btn-primary text-white">
                                                    <i class="ti ti-credit-card"></i> {{ __('Process Payment') }}
                                                </a>
                                                @if(in_array(Auth::user()->type, ['company', 'super admin']))
                                                <form action="{{ route('expenses.destroy', $expense->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this expense? This action cannot be undone.') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger text-white" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <div class="py-4">
                                                    <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                                    <p class="mt-2 text-muted">{{ __('No expenses pending finance processing.') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paid Tab -->
                    @if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin']))
                    <div class="tab-pane fade" id="paid" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table" id="pc-dt-simple-4">
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
                                    @forelse($paid as $expense)
                                        <tr>
                                            <td>{{ $expense->employee->name ?? '-' }}</td>
                                            <td>{{ $expense->category->name ?? '-' }}</td>
                                            <td><strong>{{ Auth::user()->priceFormat($expense->amount) }}</strong></td>
                                            <td>{{ $expense->paid_date ? \Carbon\Carbon::parse($expense->paid_date)->format('d M Y') : '-' }}</td>
                                            <td>{{ $expense->payment_mode ? ucfirst($expense->payment_mode) : '-' }}</td>
                                            <td>
                                                <a href="{{ route('expenses.show', $expense->id) }}" 
                                                   class="btn btn-sm btn-info text-white">
                                                    <i class="ti ti-eye"></i> {{ __('View') }}
                                                </a>
                                                @if(in_array(Auth::user()->type, ['company', 'super admin']))
                                                <form action="{{ route('expenses.destroy', $expense->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this expense? This action cannot be undone.') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger text-white" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div class="py-4">
                                                    <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                                    <p class="mt-2 text-muted">{{ __('No paid expenses found.') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Rejected Tab -->
                    @if($isHREmployee || in_array(Auth::user()->type, ['company', 'super admin']))
                    <div class="tab-pane fade" id="rejected" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table" id="pc-dt-simple-3">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Rejected At') }}</th>
                                        <th>{{ __('Rejection Reason') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rejected as $expense)
                                        <tr>
                                            <td>{{ $expense->employee->name ?? '-' }}</td>
                                            <td>{{ $expense->category->name ?? '-' }}</td>
                                            <td><strong>{{ Auth::user()->priceFormat($expense->amount) }}</strong></td>
                                            <td>{{ $expense->hr_approved_at ? \Carbon\Carbon::parse($expense->hr_approved_at)->format('d M Y H:i') : '-' }}</td>
                                            <td>{{ Str::limit($expense->hr_remark ?? '-', 50) }}</td>
                                            <td>
                                                <a href="{{ route('expenses.show', $expense->id) }}" 
                                                   class="btn btn-sm btn-info text-white">
                                                    <i class="ti ti-eye"></i> {{ __('View') }}
                                                </a>
                                                @if(in_array(Auth::user()->type, ['company', 'super admin']))
                                                <form action="{{ route('expenses.destroy', $expense->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this expense? This action cannot be undone.') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger text-white" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div class="py-4">
                                                    <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                                    <p class="mt-2 text-muted">{{ __('No rejected expenses found.') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
