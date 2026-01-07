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
    @endif
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
                                    @if (\Auth::user()->type != 'employee')
                                        <th>{{ __('Employee') }}</th>
                                    @endif
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Punch In') }}</th>
                                    <th>{{ __('Punch Out') }}</th>
                                    <th>{{ __('Reason') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Remarks') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($regularisations as $regularisation)
                                    <tr>
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
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ \Auth::user()->type != 'employee' ? '8' : '7' }}" class="text-center">
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
                    <p class="text-muted"><small>{{ __('This will create an attendance record for the selected date.') }}</small></p>
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
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            var currentRegularisationId = null;

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
        });
    </script>
@endpush


