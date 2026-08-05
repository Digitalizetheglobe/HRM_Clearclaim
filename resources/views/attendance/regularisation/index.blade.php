@extends('layouts.admin')
@section('page-title')
    {{ __('Attendance Regularisation') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Attendance Regularisation') }}</li>
@endsection

@section('action-button')
    @if (\Auth::user()->type == 'employee')
        <div class="float-end">
            <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createRegularisationModal">
                <i class="ti ti-plus"></i> {{ __('Create') }}
            </a>
        </div>
    @elseif (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
        <div class="float-end">
            <button type="button" class="btn btn-sm btn-success d-none" id="bulkApproveBtn">
                <i class="ti ti-check"></i> {{ __('Bulk Approve') }}
            </button>
        </div>
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <!-- Status Tabs & Monthly Filter -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">
                        <ul class="nav nav-tabs mb-0" id="statusTabs">
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'Approved' ? 'active' : '' }}" 
                                   href="{{ route('attendance-regularisation.index', ['status' => 'Approved', 'month' => request('month')]) }}">
                                    {{ __('Approved') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'Pending' ? 'active' : '' }}" 
                                   href="{{ route('attendance-regularisation.index', ['status' => 'Pending', 'month' => request('month')]) }}">
                                    {{ __('Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ ($status == 'Rejected' || $status == 'Reject') ? 'active' : '' }}" 
                                   href="{{ route('attendance-regularisation.index', ['status' => 'Rejected', 'month' => request('month')]) }}">
                                    {{ __('Rejected') }}
                                </a>
                            </li>
                        </ul>

                        <form method="GET" action="{{ route('attendance-regularisation.index') }}" class="d-flex align-items-center gap-2" id="regularisationMonthFilterForm">
                            <input type="hidden" name="status" value="{{ $status }}">
                            <label for="month_filter" class="form-label mb-0 fw-bold text-nowrap">{{ __('Select Month:') }}</label>
                            <input type="month" name="month" id="month_filter" class="form-control form-control-sm" 
                                   value="{{ request('month') }}" onchange="this.form.submit()">
                            @if(request('month'))
                                <a href="{{ route('attendance-regularisation.index', ['status' => $status]) }}" class="btn btn-sm btn-danger text-nowrap" title="{{ __('Reset Filter') }}">
                                    <i class="ti ti-refresh"></i> {{ __('Reset') }}
                                </a>
                            @endif
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    @if ((\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') && $status == 'Pending')
                                        <th width="50px" data-sortable="false">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                    @endif
                                    @if (\Auth::user()->type != 'employee')
                                        <th>{{ __('Employee') }}</th>
                                    @endif
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Punch In') }}</th>
                                    <th>{{ __('Punch Out') }}</th>
                                    <th>{{ __('Reason') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ ($status == 'Rejected' || $status == 'Reject') ? __('Rejected By') : __('Approved By') }}</th>
                                    <th>{{ __('Remarks') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($regularisations as $regularisation)
                                    <tr>
                                        @if ((\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') && $status == 'Pending')
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input request-checkbox" type="checkbox" value="{{ $regularisation->id }}">
                                                </div>
                                            </td>
                                        @endif
                                        @if (\Auth::user()->type != 'employee')
                                            <td>{{ $regularisation->employee->name ?? '-' }}</td>
                                        @endif
                                        <td>{{ \Auth::user()->dateFormat($regularisation->date) }}</td>
                                        <td>{{ date('h:i A', strtotime($regularisation->punch_in_time)) }}</td>
                                        <td>{{ date('h:i A', strtotime($regularisation->punch_out_time)) }}</td>
                                        <td>{{ $regularisation->reason }}</td>
                                        <td>
                                            @if ($regularisation->status == 'Pending')
                                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                                            @elseif ($regularisation->status == 'Approved')
                                                <span class="badge bg-success">{{ __('Approved') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $regularisation->approver->name ?? '-' }}</td>
                                        <td>{{ Str::limit($regularisation->remarks, 30) }}</td>
                                        <td>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('attendance-regularisation.show', $regularisation->id) }}" 
                                                   class="mx-3 btn btn-sm align-items-center" 
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            
                                            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
                                                @if ($regularisation->status == 'Pending')
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" 
                                                           class="mx-3 btn btn-sm align-items-center approve-regularisation" 
                                                           data-id="{{ $regularisation->id }}"
                                                           data-bs-toggle="tooltip" 
                                                           title="{{ __('Approve') }}">
                                                            <i class="ti ti-check text-white"></i>
                                                        </a>
                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        <a href="#" 
                                                           class="mx-3 btn btn-sm align-items-center reject-regularisation" 
                                                           data-id="{{ $regularisation->id }}"
                                                           data-bs-toggle="tooltip" 
                                                           title="{{ __('Reject') }}">
                                                            <i class="ti ti-x text-white"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif
                                            
                                            @if (\Auth::user()->type == 'employee')
                                                @if ($regularisation->status == 'Pending')
                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="#" 
                                                           class="mx-3 btn btn-sm align-items-center edit-regularisation" 
                                                           data-id="{{ $regularisation->id }}"
                                                           data-date="{{ $regularisation->date ? date('Y-m-d', strtotime($regularisation->date)) : '' }}"
                                                           data-punch-in="{{ date('H:i', strtotime($regularisation->punch_in_time)) }}"
                                                           data-punch-out="{{ date('H:i', strtotime($regularisation->punch_out_time)) }}"
                                                           data-reason="{{ $regularisation->reason }}"
                                                           data-remarks="{{ $regularisation->remarks }}"
                                                           data-bs-toggle="tooltip" 
                                                           title="{{ __('Edit') }}">
                                                            <i class="ti ti-edit text-white"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                         <td colspan="{{ (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') ? ($status == 'Pending' ? '10' : '9') : (\Auth::user()->type != 'employee' ? '9' : '8') }}" class="text-center">
                                            {{ __('No regularisation requests found.') }}
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

    <!-- Create Regularisation Modal -->
    <div class="modal fade" id="createRegularisationModal" tabindex="-1" aria-labelledby="createRegularisationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createRegularisationModalLabel">{{ __('Create Attendance Regularisation') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('attendance-regularisation.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="date" class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date" name="date" required max="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="punch_in_time" class="form-label">{{ __('Punch In Time') }} <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="punch_in_time" name="punch_in_time" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="punch_out_time" class="form-label">{{ __('Punch Out Time') }} <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="punch_out_time" name="punch_out_time" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="reason" class="form-label">{{ __('Reason') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" id="reason" name="reason" required>
                                        <option value="Missed Punch">{{ __('Missed Punch') }}</option>
                                        <option value="Technical Error">{{ __('Technical Error') }}</option>
                                        <option value="Other">{{ __('Other') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="remarks" class="form-label">{{ __('Remarks') }}</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="3" maxlength="1000"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">{{ __('Approve Regularisation Request') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to approve this regularisation request?') }}</p>
                    <p class="text-muted"><small>{{ __('This will create or update the attendance record for the selected date with the approved times.') }}</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success" id="confirmApproveBtn">{{ __('Approve') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">{{ __('Reject Regularisation Request') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3">{{ __('Are you sure you want to reject this regularisation request?') }}</p>
                        <div class="form-group">
                            <label for="rejection_reason" class="form-label">{{ __('Rejection Reason') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required maxlength="1000" placeholder="{{ __('Please provide a reason for rejection...') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('Reject') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Regularisation Modal -->
    <div class="modal fade" id="editRegularisationModal" tabindex="-1" aria-labelledby="editRegularisationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRegularisationModalLabel">{{ __('Edit Attendance Regularisation') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editRegularisationForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_date" class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="edit_date" name="date" required max="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_punch_in_time" class="form-label">{{ __('Punch In Time') }} <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="edit_punch_in_time" name="punch_in_time" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_punch_out_time" class="form-label">{{ __('Punch Out Time') }} <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="edit_punch_out_time" name="punch_out_time" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_reason" class="form-label">{{ __('Reason') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" id="edit_reason" name="reason" required>
                                        <option value="Missed Punch">{{ __('Missed Punch') }}</option>
                                        <option value="Technical Error">{{ __('Technical Error') }}</option>
                                        <option value="Other">{{ __('Other') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_remarks" class="form-label">{{ __('Remarks') }}</label>
                                    <textarea class="form-control" id="edit_remarks" name="remarks" rows="3" maxlength="1000"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Approve Confirmation Modal -->
    <div class="modal fade" id="bulkApproveModal" tabindex="-1" aria-labelledby="bulkApproveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkApproveModalLabel">{{ __('Bulk Approve Requests') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to approve all selected regularization requests?') }}</p>
                    <p class="text-muted"><small>{{ __('This will create or update the attendance records for all selected dates and employees.') }}</small></p>
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
            var currentRegularisationId = null;
            var selectedIds = new Set();

            // Function to save page state
            function savePageState() {
                if (typeof dataTable !== 'undefined') {
                    sessionStorage.setItem('attendance_regularisation_page', dataTable.currentPage);
                }
            }

            // Restore saved page state on load
            const savedPage = sessionStorage.getItem('attendance_regularisation_page');
            if (savedPage && typeof dataTable !== 'undefined') {
                setTimeout(function() {
                    dataTable.page(parseInt(savedPage));
                    sessionStorage.removeItem('attendance_regularisation_page');
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

            // Handle page change events in Datatable
            if (typeof dataTable !== 'undefined') {
                dataTable.on('datatable.page', function(page) {
                    setTimeout(function() {
                        updateVisibleCheckboxes();
                    }, 50);
                });
            }

            // Individual checkbox change
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

            // Select All checkbox change
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

            // Bulk Approve click - Show confirmation modal
            $('#bulkApproveBtn').on('click', function(e) {
                e.preventDefault();
                $('#bulkApproveModal').modal('show');
            });

            // Confirm bulk approve action
            $('#confirmBulkApproveBtn').on('click', function() {
                if (selectedIds.size === 0) {
                    return;
                }

                var btn = $(this);
                var originalText = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("Processing...") }}');

                $.ajax({
                    url: '{{ route("attendance-regularisation.bulk-approve") }}',
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

            // Fetch attendance data when date is selected
            $('#date').on('change', function() {
                var selectedDate = $(this).val();
                if (!selectedDate) {
                    // Clear fields if no date selected
                    $('#punch_in_time').val('');
                    $('#punch_out_time').val('');
                    return;
                }

                // Show loading indicator
                $('#punch_in_time').prop('disabled', true);
                $('#punch_out_time').prop('disabled', true);

                $.ajax({
                    url: '{{ route("attendance-regularisation.fetch-attendance") }}',
                    type: 'POST',
                    data: {
                        date: selectedDate,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('.date-regularisation-alert').remove();
                        if (response.has_regularisation) {
                            // Clear fields and disable submit button if regularisation already exists for date
                            $('#punch_in_time').val('');
                            $('#punch_out_time').val('');
                            $('#createRegularisationModal button[type="submit"]').prop('disabled', true);
                            showInfoMessage(response.message, 'danger');
                        } else if (response.success && response.has_attendance) {
                            $('#createRegularisationModal button[type="submit"]').prop('disabled', false);
                            // Populate fields with existing attendance data
                            $('#punch_in_time').val(response.data.punch_in_time);
                            $('#punch_out_time').val(response.data.punch_out_time);
                            
                            // Show info message about existing attendance
                            var infoMsg = '{{ __("Existing attendance found for this date. Times have been auto-populated. You can edit them if needed.") }}';
                            showInfoMessage(infoMsg, 'info');
                        } else {
                            $('#createRegularisationModal button[type="submit"]').prop('disabled', false);
                            // Clear fields for new attendance
                            $('#punch_in_time').val('');
                            $('#punch_out_time').val('');
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = '{{ __("Error fetching attendance data") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        console.error(errorMsg);
                    },
                    complete: function() {
                        // Re-enable time input fields
                        $('#punch_in_time').prop('disabled', false);
                        $('#punch_out_time').prop('disabled', false);
                    }
                });
            });

            // Function to show info message (similar to alert but styled)
            function showInfoMessage(message, type) {
                type = type || 'info';
                $('.date-regularisation-alert').remove();
                var alertClass = type === 'danger' ? 'alert-danger' : 'alert-info';
                var iconClass = type === 'danger' ? 'ti-alert-circle' : 'ti-info-circle';
                var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show date-regularisation-alert" role="alert">' +
                               '<i class="ti ' + iconClass + '"></i> ' + message +
                               '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                               '</div>';
                
                // Insert into modal body
                $('#createRegularisationModal .modal-body').prepend(alertHtml);
            }

            // Approve regularisation - Show confirmation modal
            $(document).on('click', '.approve-regularisation', function(e) {
                e.preventDefault();
                currentRegularisationId = $(this).data('id');
                $('#approveModal').modal('show');
            });

            // Confirm approve action
            $('#confirmApproveBtn').on('click', function() {
                if (!currentRegularisationId) {
                    return;
                }

                var btn = $(this);
                var originalText = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("Processing...") }}');

                $.ajax({
                    url: '{{ url("attendance-regularisation") }}/' + currentRegularisationId + '/approve',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#approveModal').modal('hide');
                        if (response.success || response.redirect) {
                            savePageState();
                            location.reload();
                        } else {
                            alert(response.message || '{{ __("Error approving request") }}');
                            btn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        $('#approveModal').modal('hide');
                        var errorMsg = '{{ __("Error approving request") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        alert(errorMsg);
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Reset approve button when modal is closed
            $('#approveModal').on('hidden.bs.modal', function() {
                $('#confirmApproveBtn').prop('disabled', false).html('{{ __("Approve") }}');
                currentRegularisationId = null;
            });

            // Reject regularisation
            $(document).on('click', '.reject-regularisation', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                $('#rejectForm').attr('action', '{{ url("attendance-regularisation") }}/' + id + '/reject');
                $('#rejection_reason').val('');
                $('#rejectModal').modal('show');
            });

            // Save state on reject form submission
            $('#rejectForm').on('submit', function() {
                savePageState();
            });

            // Edit regularisation - Show edit modal with existing data
            $(document).on('click', '.edit-regularisation', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var date = $(this).data('date');
                var punchIn = $(this).data('punch-in');
                var punchOut = $(this).data('punch-out');
                var reason = $(this).data('reason');
                var remarks = $(this).data('remarks');

                // Populate form fields with existing data
                $('#edit_date').val(date);
                $('#edit_punch_in_time').val(punchIn);
                $('#edit_punch_out_time').val(punchOut);
                $('#edit_reason').val(reason);
                $('#edit_remarks').val(remarks);

                // Set form action
                $('#editRegularisationForm').attr('action', '{{ url("attendance-regularisation") }}/' + id);

                // Show modal
                $('#editRegularisationModal').modal('show');
            });

            // Handle edit form submission
            $('#editRegularisationForm').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("Updating...") }}');

                $.ajax({
                    url: form.attr('action'),
                    type: 'PUT',
                    data: form.serialize(),
                    success: function(response) {
                        $('#editRegularisationModal').modal('hide');
                        if (response.success || response.redirect) {
                            savePageState();
                            location.reload();
                        } else {
                            alert(response.message || '{{ __("Error updating request") }}');
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        $('#editRegularisationModal').modal('hide');
                        var errorMsg = '{{ __("Error updating request") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        alert(errorMsg);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

// Reset edit form when modal is closed
$('#editRegularisationModal').on('hidden.bs.modal', function() {
    $('#editRegularisationForm')[0].reset();
    $('#editRegularisationForm').find('button[type="submit"]').prop('disabled', false).html('{{ __("Update") }}');
});

// Tab filtering removed because we now use server-side status filtering via URL
        });
    </script>
@endpush


