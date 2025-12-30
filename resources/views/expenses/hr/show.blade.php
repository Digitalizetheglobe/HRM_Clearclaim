@extends('layouts.admin')

@section('page-title')
    {{ __('Expense Details') }}
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
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">{{ __('Employee') }}</th>
                                <td>{{ $expense->employee->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Category') }}</th>
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
                        @php
                            $receiptPath = \App\Models\Utility::get_file('uploads/expense_receipts/');
                        @endphp
                        <div class="row">
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

                <!-- Action Buttons based on Status -->
                @php
                    $user = Auth::user();
                    $employee = \App\Models\Employee::where('user_id', $user->id)->first();
                    $companyId = $user->creatorId();
                    $isAdmin = in_array($user->type, ['company', 'super admin']);
                    
                    // Find HR Department (matching controller logic)
                    $hrDepartment = \App\Models\Department::where('created_by', $companyId)
                        ->where(function($q) {
                            $q->whereRaw('LOWER(name) LIKE ?', ['%human resource%'])
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%humanresource%'])  // No space
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%hr%'])
                              ->orWhereRaw('LOWER(name) = ?', ['human resource'])
                              ->orWhereRaw('LOWER(name) = ?', ['humanresource'])
                              ->orWhereRaw('LOWER(name) = ?', ['hr'])
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%human resources%'])  // Plural
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%humanresources%']);  // Plural no space
                        })
                        ->first();
                    $isHREmployee = $employee && $hrDepartment && $employee->department_id == $hrDepartment->id;
                    $canApproveReject = $isAdmin || $isHREmployee;
                @endphp
                @if($expense->status == 'pending_hr' && $canApproveReject)
                <div class="row">
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('expenses.approve', $expense->id) }}" class="mb-3">
                            @csrf
                            <div class="form-group mb-3">
                                <label>{{ __('Approval Remark (Optional)') }}</label>
                                <textarea name="remark" class="form-control" rows="3" placeholder="{{ __('Add any remarks...') }}"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check"></i> {{ __('Approve & Send to Finance') }}
                            </button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('expenses.reject', $expense->id) }}">
                            @csrf
                            <div class="form-group mb-3">
                                <label>{{ __('Rejection Reason') }} <span class="text-danger">*</span></label>
                                <textarea name="remark" class="form-control" rows="3" required placeholder="{{ __('Please provide reason for rejection...') }}"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger">
                                <i class="ti ti-x"></i> {{ __('Reject') }}
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                @php
                    // Find Finance Department
                    $financeDepartment = \App\Models\Department::where('created_by', $companyId)
                        ->where(function($q) {
                            $q->whereRaw('LOWER(name) LIKE ?', ['%finance%'])
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%finanace%'])  // Handle misspelling
                              ->orWhereRaw('LOWER(name) = ?', ['finance'])
                              ->orWhereRaw('LOWER(name) = ?', ['finanace']); // Handle misspelling
                        })
                        ->first();
                    $isFinanceEmployee = $employee && $financeDepartment && $employee->department_id == $financeDepartment->id;
                    $canProcessPayment = $isAdmin || $isFinanceEmployee;
                @endphp
                @if($expense->status == 'pending_finance' && $canProcessPayment)
                <div class="row">
                    <div class="col-md-12">
                        <h6>{{ __('Process Payment') }}</h6>
                        <form method="POST" action="{{ route('expenses.mark-paid', $expense->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Paid Date') }} <span class="text-danger">*</span></label>
                                        <input type="date" name="paid_date" class="form-control" 
                                               value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Payment Mode') }} <span class="text-danger">*</span></label>
                                        <select name="payment_mode" class="form-control" required>
                                            <option value="bank">{{ __('Bank') }}</option>
                                            <option value="upi">{{ __('UPI') }}</option>
                                            <option value="cash">{{ __('Cash') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Payment Proof') }}</label>
                                        <input type="file" name="payment_proof" class="form-control" 
                                               accept="image/*,application/pdf">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check"></i> {{ __('Mark as Paid') }}
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Delete Button (Company/Admin only) -->
                @if(in_array(Auth::user()->type, ['company', 'super admin']))
                <div class="row mt-4">
                    <div class="col-12">
                        <hr>
                        <form action="{{ route('expenses.destroy', $expense->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('{{ __('Are you sure you want to delete this expense? This action cannot be undone.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" title="{{ __('Delete Expense') }}">
                                <i class="ti ti-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
