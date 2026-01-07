@extends('layouts.admin')
@section('page-title')
    {{ __('Attendance Regularisation Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('attendance-regularisation.index') }}">{{ __('Attendance Regularisation') }}</a></li>
    <li class="breadcrumb-item">{{ __('Details') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Regularisation Request Details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Employee') }}</label>
                                <p class="form-control-static">{{ $regularisation->employee->name ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Date') }}</label>
                                <p class="form-control-static">{{ \Auth::user()->dateFormat($regularisation->date) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Punch In Time') }}</label>
                                <p class="form-control-static">{{ date('h:i A', strtotime($regularisation->punch_in_time)) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Punch Out Time') }}</label>
                                <p class="form-control-static">{{ date('h:i A', strtotime($regularisation->punch_out_time)) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Reason') }}</label>
                                <p class="form-control-static">{{ $regularisation->reason }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Status') }}</label>
                                <p class="form-control-static">
                                    @if ($regularisation->status == 'Pending')
                                        <span class="badge bg-warning">{{ __('Pending') }}</span>
                                    @elseif ($regularisation->status == 'Approved')
                                        <span class="badge bg-success">{{ __('Approved') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">{{ __('Remarks') }}</label>
                                <p class="form-control-static">{{ $regularisation->remarks ?? '-' }}</p>
                            </div>
                        </div>
                        @if ($regularisation->status == 'Rejected' && $regularisation->rejection_reason)
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Rejection Reason') }}</label>
                                    <p class="form-control-static text-danger">{{ $regularisation->rejection_reason }}</p>
                                </div>
                            </div>
                        @endif
                        @if ($regularisation->status == 'Approved' && $regularisation->approver)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Approved By') }}</label>
                                    <p class="form-control-static">{{ $regularisation->approver->name ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Approved At') }}</label>
                                    <p class="form-control-static">{{ $regularisation->approved_at ? \Auth::user()->dateFormat($regularisation->approved_at) . ' ' . date('h:i A', strtotime($regularisation->approved_at)) : '-' }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('attendance-regularisation.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
                    
                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
                        @if ($regularisation->status == 'Pending')
                            <a href="#" class="btn btn-success approve-regularisation" data-id="{{ $regularisation->id }}">
                                <i class="ti ti-check"></i> {{ __('Approve') }}
                            </a>
                            <a href="#" class="btn btn-danger reject-regularisation" data-id="{{ $regularisation->id }}">
                                <i class="ti ti-x"></i> {{ __('Reject') }}
                            </a>
                        @endif
                    @endif
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
                        <div class="form-group">
                            <label for="rejection_reason" class="form-label">{{ __('Rejection Reason') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required maxlength="1000"></textarea>
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
            // Approve regularisation
            $(document).on('click', '.approve-regularisation', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                
                if (confirm('{{ __("Are you sure you want to approve this regularisation request?") }}')) {
                    $.ajax({
                        url: '{{ url("attendance-regularisation") }}/' + id + '/approve',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success || response.redirect) {
                                location.reload();
                            } else {
                                alert(response.message || '{{ __("Error approving request") }}');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                alert(xhr.responseJSON.message);
                            } else {
                                alert('{{ __("Error approving request") }}');
                            }
                        }
                    });
                }
            });

            // Reject regularisation
            $(document).on('click', '.reject-regularisation', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                $('#rejectForm').attr('action', '{{ url("attendance-regularisation") }}/' + id + '/reject');
                $('#rejectModal').modal('show');
            });
        });
    </script>
@endpush


