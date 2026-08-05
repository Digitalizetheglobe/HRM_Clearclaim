@php
    function breakAfterWords($text, $wordsPerLine = 3) {
        $words = explode(' ', $text);
        $lines = array_chunk($words, $wordsPerLine);
        return implode('<br>', array_map('implode', array_fill(0, count($lines), ' '), $lines));
    }
@endphp
@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Leave Requests') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave Requests') }}</li>
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
                                    <th>{{ __('Applied By') }}</th>
                                    <th>{{ __('Applied On') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Total Days') }}</th>
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaves as $leave)
                                    <tr>
                                        <td>{{ !empty($leave->employee_id) ? $leave->employees->name : '' }}</td>
                                        @php
                                            $appliedUser = \App\Models\User::find($leave->created_by);
                                            $appliedByName = $appliedUser ? $appliedUser->name : (!empty($leave->employees->name) ? $leave->employees->name : '-');
                                        @endphp
                                        <td>{{ $appliedByName }}</td>
                                        <td>{{ \Auth::user()->dateFormat($leave->applied_on) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
                                        <td>
                                            {{ $leave->leave_duration ?? 'Full Day' }}
                                            @if(($leave->leave_duration ?? '') == 'Half Day' && !empty($leave->leave_session))
                                                <br><small class="text-muted">({{ $leave->leave_session }})</small>
                                            @endif
                                        </td>
                                        <td>{{ $leave->total_leave_days }}</td>
                                        <td>
                                            @if($leave->is_lop)
                                                <span class="badge bg-danger">{{ __('LOP') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('Paid') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($leave->status == 'Pending')
                                                <div class="badge bg-warning p-2 px-3 rounded status-badge5">{{ $leave->status }}</div>
                                            @elseif($leave->status == 'Approved')
                                                <div class="badge bg-success p-2 px-3 rounded status-badge5">{{ $leave->status }}</div>
                                            @elseif($leave->status == 'Reject')
                                                <div class="badge bg-danger p-2 px-3 rounded status-badge5">{{ $leave->status }}</div>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-size="lg"
                                                        data-url="{{ URL::to('leave/' . $leave->id . '/action') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Leave Action') }}"
                                                        data-bs-original-title="{{ __('Manage Leave') }}">
                                                        <i class="ti ti-caret-right text-white"></i>
                                                    </a>
                                                </div>
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
