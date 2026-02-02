@extends('layouts.admin')

@section('page-title')
    {{ __('Employee Resignations') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Resignations') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Department Resignations') }}</h5>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee Name') }}</th>
                                <th>{{ __('Resignation Date') }}</th>
                                <th>{{ __('Last Working Day') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resignations as $resignation)
                                <tr>
                                    <td>
                                        @if($resignation->employee)
                                            {{ $resignation->employee->name }}
                                        @else
                                            {{ __('N/A') }}
                                        @endif
                                    </td>
                                    <td>{{ \Auth::user()->dateFormat($resignation->notice_date) }}</td>
                                    <td>{{ \Auth::user()->dateFormat($resignation->resignation_date) }}</td>
                                    <td>{{ Str::limit($resignation->description, 50) }}</td>
                                    <td>
                                        @if($resignation->status == 'pending')
                                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('Approved') }}</span>
                                        @endif
                                    </td>
                                    <td class="Action">
                                        <span>
                                            @if($resignation->status == 'pending')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="{{ route('resignation.review', $resignation->id) }}" 
                                                    class="mx-3 btn btn-sm align-items-center" 
                                                    data-bs-toggle="tooltip" 
                                                    title="{{ __('Review & Approve') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                            @else
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="{{ route('resignation.review', $resignation->id) }}" 
                                                    class="mx-3 btn btn-sm align-items-center" 
                                                    data-bs-toggle="tooltip" 
                                                    title="{{ __('View Details') }}">
                                                        <i class="ti ti-check text-white"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
