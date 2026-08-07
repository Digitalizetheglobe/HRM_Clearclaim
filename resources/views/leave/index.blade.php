@php
    if (!function_exists('breakAfterWords')) {
        function breakAfterWords($text, $wordsPerLine = 3) {
            $words = explode(' ', $text);
            $lines = array_chunk($words, $wordsPerLine);
            return implode('<br>', array_map('implode', array_fill(0, count($lines), ' '), $lines));
        }
    }
@endphp
@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Leave') }}
@endsection


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave ') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('leave.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a>


    @can('Create Leave')
        <a href="#" data-url="{{ route('leave.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Leave') }}" data-size="lg" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr' || (isset($isManager) && $isManager))
        <button type="button" class="btn btn-sm btn-success d-none ms-1" id="bulkApproveBtn">
            <i class="ti ti-check"></i> {{ __('Bulk Approve') }}
        </button>
    @endif
@endsection

@section('content')

    @if (\Auth::user()->type == 'employee' && isset($leaveBalance))
        <div class="row mb-4">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Leave Balance Summary') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="text-white mb-2">{{ __('Total Year Leaves') }}</h6>
                                        <h3 class="mb-0">{{ number_format($leaveBalance['total_year_leaves'], 2) }}</h3>
                                        <small class="text-white-50">{{ __('Pro-rata entitlement') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="text-white mb-2">{{ __('This Month Leaves') }}</h6>
                                        <h3 class="mb-0">{{ number_format($leaveBalance['monthly_limit'], 2) }}</h3>
                                        <small class="text-white-50">{{ __('Monthly limit (paid)') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h6 class="text-white mb-2">{{ __('Total Monthly Used') }}</h6>
                                        <h3 class="mb-0">{{ number_format($leaveBalance['this_month_paid_used'], 2) }}</h3>
                                        <small class="text-white-50">
                                            {{ __('Used: ') }}{{ number_format($leaveBalance['this_month_paid_used'], 2) }} / 
                                            {{ number_format($leaveBalance['monthly_limit'], 2) }} | 
                                            {{ __('Remaining: ') }}{{ number_format($leaveBalance['remaining_paid_this_month'], 2) }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="text-white mb-2">{{ __('Yearly Remaining') }}</h6>
                                        <h3 class="mb-0">{{ number_format($leaveBalance['yearly_remaining'], 2) }}</h3>
                                        <small class="text-white-50">{{ __('Available balance') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($leaveBalance['this_month_paid_used'] >= $leaveBalance['monthly_limit'])
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="ti ti-alert-triangle"></i> 
                                <strong>{{ __('Notice:') }}</strong> 
                                {{ __('You have used all '.$leaveBalance['monthly_limit'].' paid leaves for this month. Any additional leaves will be Leave Without Pay (LWP).') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    {{-- <h5> </h5> --}}
                    <!-- Status Tabs & Monthly Filter -->
                    @php $currentStatus = $status ?? 'Approved'; @endphp
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">
                        <ul class="nav nav-tabs mb-0" id="statusTabs">
                            <li class="nav-item">
                                <a class="nav-link {{ $currentStatus == 'Approved' ? 'active' : '' }}" 
                                   href="{{ route('leave.index', ['status' => 'Approved', 'month' => request('month')]) }}">
                                    {{ __('Approved') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $currentStatus == 'Pending' ? 'active' : '' }}" 
                                   href="{{ route('leave.index', ['status' => 'Pending', 'month' => request('month')]) }}">
                                    {{ __('Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ ($currentStatus == 'Reject' || $currentStatus == 'Rejected') ? 'active' : '' }}" 
                                   href="{{ route('leave.index', ['status' => 'Reject', 'month' => request('month')]) }}">
                                    {{ __('Rejected') }}
                                </a>
                            </li>
                        </ul>

                        <form method="GET" action="{{ route('leave.index') }}" class="d-flex align-items-center gap-2" id="leaveMonthFilterForm">
                            <input type="hidden" name="status" value="{{ $currentStatus }}">
                            <label for="month_filter" class="form-label mb-0 fw-bold text-nowrap">{{ __('Select Month:') }}</label>
                            <input type="month" name="month" id="month_filter" class="form-control form-control-sm" 
                                   value="{{ request('month') }}" onchange="this.form.submit()">
                            @if(request('month'))
                                <a href="{{ route('leave.index', ['status' => $currentStatus]) }}" class="btn btn-sm btn-danger text-nowrap" title="{{ __('Reset Filter') }}">
                                    <i class="ti ti-refresh"></i> {{ __('Reset') }}
                                </a>
                            @endif
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    @if ((\Auth::user()->type == 'company' || \Auth::user()->type == 'hr' || (isset($isManager) && $isManager)) && $currentStatus == 'Pending')
                                        <th width="50px" data-sortable="false">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                    @endif
                                    @if (\Auth::user()->type != 'employee' || (isset($isManager) && $isManager))
                                        <th>{{ __('Employee') }}</th>
                                    @endif
                                    @if ($currentStatus != 'Pending')
                                        <th>{{ ($currentStatus == 'Reject' || $currentStatus == 'Rejected') ? __('Rejected By') : __('Approved By') }}</th>
                                    @endif
                                    <th>{{ __('Applied On') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Total Days') }}</th>
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (\Auth::user()->type != 'employee' || (isset($isManager) && $isManager))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaves as $leave)
                                    <tr>
                                        @if ((\Auth::user()->type == 'company' || \Auth::user()->type == 'hr' || (isset($isManager) && $isManager)) && $currentStatus == 'Pending')
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input request-checkbox" type="checkbox" value="{{ $leave->id }}">
                                                </div>
                                            </td>
                                        @endif
                                        @if (\Auth::user()->type != 'employee' || (isset($isManager) && $isManager))
                                            <td>{{ !empty($leave->employee_id) ? $leave->employees->name : '' }}
                                            </td>
                                        @endif
                                        @if ($currentStatus != 'Pending')
                                            @php
                                                if (($leave->status == 'Approved' || $leave->status == 'Reject' || $leave->status == 'Rejected') && $leave->approver) {
                                                    $displayUser = $leave->approver->name;
                                                } else {
                                                    $appliedUser = \App\Models\User::find($leave->created_by);
                                                    $displayUser = $appliedUser ? $appliedUser->name : (!empty($leave->employees->name) ? $leave->employees->name : '-');
                                                }
                                            @endphp
                                            <td>{{ $displayUser }}</td>
                                        @endif
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
                                        <!-- <td>{{ $leave->leave_reason }}</td> -->
                                        <td>
                                            @if ($leave->status == 'Pending')
                                                <div class="badge bg-warning p-2 px-3 rounded status-badge5">
                                                    {{ $leave->status }}</div>
                                            @elseif($leave->status == 'Approved')
                                                <div class="badge bg-success p-2 px-3 rounded status-badge5">
                                                    {{ $leave->status }}</div>
                                            @elseif($leave->status == 'Reject')
                                                <div class="badge bg-danger p-2 px-3 rounded status-badge5">
                                                    {{ $leave->status }}</div>
                                            @endif
                                        </td>

                                        @if (\Auth::user()->type != 'employee' || (isset($isManager) && $isManager))
                                            <td class="Action">

                                                <span>
                                                    @if (\Auth::user()->type != 'employee' || (isset($isManager) && $isManager))
                                                        @if (\Auth::user()->type != 'employee' || $leave->employee_id != $employee->id)
                                                            <div class="action-btn bg-success ms-2">
                                                                <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                    data-size="lg"
                                                                    data-url="{{ URL::to('leave/' . $leave->id . '/action') }}"
                                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                    title="" data-title="{{ __('Leave Action') }}"
                                                                    data-bs-original-title="{{ __('Manage Leave') }}">
                                                                    <i class="ti ti-caret-right text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endif
                                                        @can('Edit Leave')
                                                            @if (\Auth::user()->type != 'employee')
                                                                <div class="action-btn bg-info ms-2">
                                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                        data-size="lg"
                                                                        data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                        title="" data-title="{{ __('Edit Leave') }}"
                                                                        data-bs-original-title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @endcan
                                                        @can('Delete Leave')
                                                            @if (\Auth::user()->type != 'employee')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['leave.destroy', $leave->id],
                                                                        'id' => 'delete-form-' . $leave->id,
                                                                    ]) !!}
                                                                    <a href="#"
                                                                        class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-bs-original-title="Delete" aria-label="Delete"><i
                                                                            class="ti ti-trash text-white text-white"></i></a>
                                                                    </form>
                                                                </div>
                                                            @endif
                                                        @endcan
                                                    @else
                                                        <div class="action-btn bg-success ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                data-size="lg"
                                                                data-url="{{ URL::to('leave/' . $leave->id . '/action') }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Leave Action') }}"
                                                                data-bs-original-title="{{ __('Manage Leave') }}">
                                                                <i class="ti ti-caret-right text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endif

                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Bulk Approve Confirmation Modal -->
    <div class="modal fade" id="bulkApproveModal" tabindex="-1" aria-labelledby="bulkApproveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkApproveModalLabel">{{ __('Bulk Approve Leaves') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to approve all selected leave requests?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success" id="confirmBulkApproveBtn">{{ __('Approve') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            var selectedIds = new Set();

            function savePageState() {
                if (typeof dataTable !== 'undefined') {
                    sessionStorage.setItem('leave_page', dataTable.currentPage);
                }
            }

            const savedPage = sessionStorage.getItem('leave_page');
            if (savedPage && typeof dataTable !== 'undefined') {
                setTimeout(function() {
                    dataTable.page(parseInt(savedPage));
                    sessionStorage.removeItem('leave_page');
                    updateVisibleCheckboxes();
                }, 100);
            }

            function updateBulkButtonVisibility() {
                if (selectedIds.size > 0) {
                    $('#bulkApproveBtn').removeClass('d-none');
                } else {
                    $('#bulkApproveBtn').addClass('d-none');
                }
            }

            function updateVisibleCheckboxes() {
                $('.request-checkbox').each(function() {
                    var id = $(this).val();
                    if (selectedIds.has(id)) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
                
                var visiblePendingCount = $('.request-checkbox').length;
                var visibleCheckedCount = $('.request-checkbox:checked').length;
                $('#selectAll').prop('checked', visiblePendingCount > 0 && visiblePendingCount === visibleCheckedCount);
            }

            if (typeof dataTable !== 'undefined') {
                dataTable.on('datatable.page', function(page) {
                    setTimeout(function() {
                        updateVisibleCheckboxes();
                    }, 50);
                });
            }

            $(document).on('change', '.request-checkbox', function() {
                var id = $(this).val();
                if ($(this).is(':checked')) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
                updateBulkButtonVisibility();
                
                var visiblePendingCount = $('.request-checkbox').length;
                var visibleCheckedCount = $('.request-checkbox:checked').length;
                $('#selectAll').prop('checked', visiblePendingCount > 0 && visiblePendingCount === visibleCheckedCount);
            });

            $(document).on('change', '#selectAll', function() {
                var isChecked = $(this).is(':checked');
                $('.request-checkbox').each(function() {
                    $(this).prop('checked', isChecked);
                    var id = $(this).val();
                    if (isChecked) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                });
                updateBulkButtonVisibility();
            });

            $('#bulkApproveBtn').on('click', function(e) {
                e.preventDefault();
                $('#bulkApproveModal').modal('show');
            });

            $('#confirmBulkApproveBtn').on('click', function() {
                if (selectedIds.size === 0) {
                    return;
                }

                var btn = $(this);
                var originalText = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("Processing...") }}');

                $.ajax({
                    url: '{{ route("leave.bulk-approve") }}',
                    type: 'POST',
                    data: {
                        ids: Array.from(selectedIds),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#bulkApproveModal').modal('hide');
                        if (response.success || response.redirect) {
                            savePageState();
                            location.reload();
                        } else {
                            alert(response.message || '{{ __("Error approving requests") }}');
                            btn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        $('#bulkApproveModal').modal('hide');
                        var errorMsg = '{{ __("Error approving requests") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        alert(errorMsg);
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });

        $(document).on('change', '#employee_id', function() {
            var employee_id = $(this).val();

            $.ajax({
                url: '{{ route('leave.jsoncount') }}',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    var oldval = $('#leave_type_id').val();
                    $('#leave_type_id').empty();
                    $('#leave_type_id').append(
                        '<option value="">{{ __('Select Leave Type') }}</option>');

                    $.each(data, function(key, value) {

                        if (value.total_leave == value.days) {
                            $('#leave_type_id').append('<option value="' + value.id +
                                '" disabled>' + value.title + '&nbsp(' + value.total_leave +
                                '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type_id').append('<option value="' + value.id + '">' +
                                value.title + '&nbsp(' + value.total_leave + '/' + value
                                .days + ')</option>');
                        }
                        if (oldval) {
                            if (oldval == value.id) {
                                $("#leave_type_id option[value=" + oldval + "]").attr(
                                    "selected", "selected");
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
