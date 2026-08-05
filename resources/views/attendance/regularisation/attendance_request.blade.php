@extends('layouts.admin')
@section('page-title')
    {{ __('Attendance Requests') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Attendance Requests') }}</li>
@endsection

@section('action-button')
    <div class="float-end">
        <button type="button" class="btn btn-sm btn-success d-none" id="bulkApproveBtn">
            <i class="ti ti-check"></i> {{ __('Bulk Approve') }}
        </button>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <!-- Status Tabs -->
                    <ul class="nav nav-tabs mb-3" id="statusTabs">
                        <li class="nav-item"><a class="nav-link {{ $status == 'Approved' ? 'active' : '' }}" href="{{ route('attendance.request', ['status' => 'Approved']) }}">{{ __('Approved') }}</a></li>
                        <li class="nav-item"><a class="nav-link {{ $status == 'Pending' ? 'active' : '' }}" href="{{ route('attendance.request', ['status' => 'Pending']) }}">{{ __('Pending') }}</a></li>
                    </ul>
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    @if ($status == 'Pending')
                                        <th width="50px" data-sortable="false">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                    @endif
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Punch In') }}</th>
                                    <th>{{ __('Punch Out') }}</th>
                                    <th>{{ __('Reason') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Approved By') }}</th>
                                    <th>{{ __('Remarks') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($regularisations as $regularisation)
                                    <tr>
                                        @if ($status == 'Pending')
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input request-checkbox" type="checkbox" value="{{ $regularisation->id }}">
                                                </div>
                                            </td>
                                        @endif
                                        <td>{{ $regularisation->employee->name ?? '-' }}</td>
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
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $status == 'Pending' ? '9' : '8' }}" class="text-center">
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
        });
    </script>
@endpush
