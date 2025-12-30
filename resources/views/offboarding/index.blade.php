@extends('layouts.admin')
@section('page-title')
    {{ __('Off-Boarding Steps') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Off-Boarding Steps') }}</li>
@endsection

@php
    $logo = \App\Models\Utility::get_file('uploads/avatar/');
@endphp

@push('css-page')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}">
@endpush

@push('script-page')
    <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>

    <script>
        @if(\Auth::user()->type == 'company' || Gate::check('Manage Resignation'))
            ! function(a) {
                "use strict";

                var t = function() {
                    this.$body = a("body")
                };
                t.prototype.init = function() {

                    a('[data-plugin="dragula"]').each(function() {

                        var t = a(this).data("containers"),
                            n = [];
                        if (t)
                            for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]);
                        else n = [a(this)[0]];
                        var r = a(this).data("handleclass");
                        r ? dragula(n, {
                            moves: function(a, t, n) {
                                return n.classList.contains(r)
                            }
                        }) : dragula(n).on('drop', function(el, target, source, sibling) {
                            var order = [];
                            $("#" + target.id + " > div").each(function() {
                                order[$(this).index()] = $(this).attr('data-id');
                            });

                            var id = $(el).attr('data-id');
                            var old_status = $("#" + source.id).data('status');
                            var new_status = $("#" + target.id).data('status');
                            var stage_id = $(target).attr('data-id');

                            $("#" + source.id).parent().find('.count').text($("#" + source.id +
                                " > div").length);
                            $("#" + target.id).parent().find('.count').text($("#" + target.id +
                                " > div").length);
                            $.ajax({
                                url: '{{ route('offboarding.order') }}',
                                type: 'POST',
                                data: {
                                    order: order,
                                    stage_id: stage_id,
                                    "_token": $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(data) {
                                    show_toastr('Success', 'Process successfully updated',
                                        'success');
                                },
                                error: function(data) {
                                    data = data.responseJSON;
                                    show_toastr('Error', data.error, 'error')
                                }
                            });
                        });
                    })
                }, a.Dragula = new t, a.Dragula.Constructor = t
            }(window.jQuery),
            function(a) {
                "use strict";

                a.Dragula.init()

            }(window.jQuery);
        @endif
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['offboarding.index'], 'method' => 'get', 'id' => 'offboarding_filter']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date', $filter['start_date'], ['class' => 'month-btn form-control current_date']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'month-btn form-control current_date', 'autocomplete' => 'off']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                                            {{ Form::select('employee', $employees, $filter['employee'], ['class' => 'form-control select ', 'id' => 'employee_id']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('offboarding_filter').submit(); return false;"
                                            data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('offboarding.index') }}" class="btn btn-sm btn-danger"
                                            data-bs-toggle="tooltip" title="" data-bs-original-title="Reset">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden mt-0">
        <div class="container-kanban">
            @php
                $json = [];
                foreach ($stages as $stage) {
                    $json[] = 'kanban-offboarding-' . $stage->id;
                }
            @endphp

            <div class="row kanban-wrapper horizontal-scroll-cards" data-plugin="dragula"
                data-containers='{!! json_encode($json) !!}'>
                @foreach ($stages as $key => $stage)
                    @php 
                        try {
                            $processes = $stage->processes($filter);
                        } catch (\Exception $e) {
                            $processes = collect([]);
                        }
                    @endphp

                    <div class="col">
                        <div class="card">
                            <div class="card-header">
                                <div class="float-end">
                                    <span class="btn btn-sm btn-primary btn-icon count">
                                        {{ count($processes) }}
                                    </span>
                                </div>
                                <h4 class="mb-0">{{ $stage->title }}</h4>
                            </div>

                            <div class="card-body kanban-box" id="{{ $json[$key] }}" data-id="{{ $stage->id }}" data-status="{{ $stage->id }}">
                                @foreach ($processes as $process)
                                    <div class="card" data-id="{{ $process->id }}">
                                        <div class="pt-3 ps-3">
                                        </div>
                                        <div class="card-header border-0 pb-0 position-relative">
                                            <h5>
                                                @if($process->employee)
                                                    @if($stage->order == 1)
                                                        <a href="{{ route('resignation.index') }}" class="process-link">
                                                            {{ $process->employee->name }}
                                                        </a>
                                                    @elseif($stage->order == 5)
                                                        <a href="{{ route('termination.index') }}" class="process-link">
                                                            {{ $process->employee->name }}
                                                        </a>
                                                    @elseif($stage->order == 6)
                                                        <a href="{{ route('employee.show', \Crypt::encrypt($process->employee->id)) }}" 
                                                           class="process-link hr-uploads-link"
                                                           data-process-id="{{ $process->id }}"
                                                           data-employee-id="{{ $process->employee->id }}"
                                                           data-step="6">
                                                            {{ $process->employee->name }}
                                                        </a>
                                                    @else
                                                        <a href="#" 
                                                            data-url="{{ route('offboarding.step', ['id' => $process->id, 'step' => $stage->order]) }}"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            data-title="{{ $stage->title }} - {{ $process->employee->name }}"
                                                            class="process-link">
                                                            {{ $process->employee->name }}
                                                        </a>
                                                    @endif
                                                @else
                                                    {{ __('N/A') }}
                                                @endif
                                            </h5>

                                            <div class="card-header-right">
                                                <div class="btn-group card-option">
                                                    <button type="button" class="btn dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="feather icon-more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        @if($stage->order == 1)
                                                            <a href="{{ route('resignation.index') }}"
                                                                class="dropdown-item"><i
                                                                    class="ti ti-eye "></i><span
                                                                    class="ms-2">{{ __('View Resignation') }}</span></a>
                                                        @elseif($stage->order == 5)
                                                            <a href="{{ route('termination.index') }}"
                                                                class="dropdown-item"><i
                                                                    class="ti ti-eye "></i><span
                                                                    class="ms-2">{{ __('View Termination') }}</span></a>
                                                        @elseif($stage->order == 6)
                                                            <a href="{{ route('employee.show', \Crypt::encrypt($process->employee->id)) }}"
                                                                class="dropdown-item hr-uploads-link"
                                                                data-process-id="{{ $process->id }}"
                                                                data-employee-id="{{ $process->employee->id }}"
                                                                data-step="6"
                                                                target="_blank"><i
                                                                    class="ti ti-eye "></i><span
                                                                    class="ms-2">{{ __('View Employee Details') }}</span></a>
                                                        @else
                                                            <a href="#" 
                                                                data-url="{{ route('offboarding.step', ['id' => $process->id, 'step' => $stage->order]) }}"
                                                                data-ajax-popup="true"
                                                                data-size="lg"
                                                                data-title="{{ $stage->title }} - {{ $process->employee ? $process->employee->name : 'N/A' }}"
                                                                class="dropdown-item"><i
                                                                    class="ti ti-eye "></i><span
                                                                    class="ms-2">{{ __('View Details') }}</span></a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <ul class="list-inline mb-0 mt-0">
                                                    @if($process->employee)
                                                        <small class="text-md">{{ $process->employee->email ?? '' }}</small>
                                                    @endif

                                                    <li class="list-inline-item d-inline-flex align-items-center"
                                                        data-bs-toggle="tooltip" title="{{ __('Created at') }}">
                                                        <i class="ti ti-clock me-2"></i>{{ \Auth::user()->dateFormat($process->created_at) }}
                                                    </li>

                                                    @if($stage->order == 2 && $process->access_removal_checklist)
                                                        @php
                                                            $accessItems = is_array($process->access_removal_checklist) ? $process->access_removal_checklist : [];
                                                            $accessDone = count(array_filter($accessItems, function($item) { return isset($item['done']) && $item['done']; }));
                                                            $accessTotal = count($accessItems);
                                                        @endphp
                                                        <li class="list-inline-item">
                                                            <small>{{ $accessDone }}/{{ $accessTotal }} Done</small>
                                                        </li>
                                                    @endif

                                                    @if($stage->order == 3 && $process->asset_collection_checklist)
                                                        @php
                                                            $assetItems = is_array($process->asset_collection_checklist) ? $process->asset_collection_checklist : [];
                                                            $assetCollected = count(array_filter($assetItems, function($item) { return isset($item['collected']) && $item['collected']; }));
                                                            $assetTotal = count($assetItems);
                                                        @endphp
                                                        <li class="list-inline-item">
                                                            <small>{{ $assetCollected }}/{{ $assetTotal }} Collected</small>
                                                        </li>
                                                    @endif

                                                    @if($stage->order == 4 && $process->settlement_status)
                                                        <li class="list-inline-item">
                                                            <span class="badge bg-{{ $process->settlement_status == 'completed' ? 'success' : 'warning' }}">
                                                                {{ ucfirst($process->settlement_status) }}
                                                            </span>
                                                        </li>
                                                    @endif
                                                </ul>
                                                @if($process->employee)
                                                    <div class="avatar-group hover-avatar-ungroup">
                                                        <a href="#" class="user-group">
                                                            @php
                                                                $avatar = ($process->employee->user && !empty($process->employee->user->avatar)) ? $process->employee->user->avatar : 'avatar.png';
                                                            @endphp
                                                            <img src="{{ $logo . $avatar }}"
                                                                class="hweb " style="width: 28px">
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <span class="empty-container" data-placeholder="Empty"></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            var now = new Date();
            var month = (now.getMonth() + 1);
            var day = now.getDate();
            if (month < 10) month = "0" + month;
            if (day < 10) day = "0" + day;
            var today = now.getFullYear() + '-' + month + '-' + day;
            $('.current_date').val(today);

            // Track when user clicks on employee link in step 6 (HR Uploads/Downloads)
            $(document).on('click', 'a.hr-uploads-link', function(e) {
                var processId = $(this).data('process-id');
                var employeeId = $(this).data('employee-id');
                
                // Store in sessionStorage that user is visiting employee page
                if (processId && employeeId) {
                    sessionStorage.setItem('offboarding_employee_visit', JSON.stringify({
                        processId: processId,
                        employeeId: employeeId,
                        step: 6,
                        timestamp: new Date().getTime()
                    }));
                }
            });

            // Check if user returned from employee page
            function checkEmployeePageReturn() {
                var visitData = sessionStorage.getItem('offboarding_employee_visit');
                
                if (visitData) {
                    try {
                        var data = JSON.parse(visitData);
                        var processId = data.processId;
                        var timestamp = data.timestamp;
                        var timeDiff = new Date().getTime() - timestamp;
                        
                        // Only show popup if user returned within last 10 minutes
                        if (timeDiff < 600000) { // 10 minutes in milliseconds
                            // Show confirmation popup as a proper modal
                            setTimeout(function() {
                                // Create and show confirmation modal
                                var confirmModalHtml = '<div class="modal fade" id="download-confirm-modal" tabindex="-1" role="dialog" aria-labelledby="downloadConfirmModalLabel" aria-hidden="true">' +
                                    '<div class="modal-dialog modal-dialog-centered" role="document">' +
                                    '<div class="modal-content">' +
                                    '<div class="modal-header">' +
                                    '<h5 class="modal-title" id="downloadConfirmModalLabel">{{ __("Download Confirmation") }}</h5>' +
                                    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                                    '</div>' +
                                    '<div class="modal-body">' +
                                    '<p class="mb-0">{{ __("Did you download something from the employee details page?") }}</p>' +
                                    '</div>' +
                                    '<div class="modal-footer">' +
                                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="download-confirm-cancel">{{ __("No") }}</button>' +
                                    '<button type="button" class="btn btn-primary" id="download-confirm-ok">{{ __("Yes") }}</button>' +
                                    '</div>' +
                                    '</div></div></div>';
                                
                                $('body').append(confirmModalHtml);
                                
                                // Show modal
                                $('#download-confirm-modal').modal('show');
                                
                                var userClickedYes = false;
                                
                                // Handle Yes button click
                                $('#download-confirm-ok').on('click', function() {
                                    userClickedYes = true;
                                    $('#download-confirm-modal').modal('hide');
                                });
                                
                                // Handle No button or Cancel
                                $('#download-confirm-cancel, .btn-close').on('click', function() {
                                    userClickedYes = false;
                                    sessionStorage.removeItem('offboarding_employee_visit');
                                });
                                
                                // When confirmation modal closes, open step 6 modal if user clicked Yes
                                $('#download-confirm-modal').on('hidden.bs.modal', function() {
                                    $(this).remove();
                                    
                                    if (userClickedYes) {
                                        // Open the step 6 modal for this process
                                        var stepUrl = '{{ url("offboarding") }}/' + processId + '/step/6';
                                        
                                        // Create modal dynamically
                                        $.ajax({
                                            url: stepUrl,
                                            type: 'GET',
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest'
                                            },
                                            success: function(response) {
                                                // Create and show step 6 modal
                                                var modalHtml = '<div class="modal fade" id="offboarding-confirm-modal" tabindex="-1" role="dialog">' +
                                                    '<div class="modal-dialog modal-lg" role="document">' +
                                                    '<div class="modal-content">' +
                                                    response +
                                                    '</div></div></div>';
                                                
                                                $('body').append(modalHtml);
                                                
                                                // Show modal using jQuery (compatible with Bootstrap 4 and 5)
                                                $('#offboarding-confirm-modal').modal('show');
                                                
                                                // Remove from sessionStorage after showing
                                                sessionStorage.removeItem('offboarding_employee_visit');
                                                
                                                // Clean up modals on close
                                                $('#offboarding-confirm-modal').on('hidden.bs.modal', function() {
                                                    $(this).remove();
                                                });
                                            },
                                            error: function() {
                                                show_toastr('Error', '{{ __("Unable to load confirmation form") }}', 'error');
                                                sessionStorage.removeItem('offboarding_employee_visit');
                                            }
                                        });
                                    }
                                });
                                
                            }, 500); // Small delay to ensure page is loaded
                        } else {
                            // Too much time passed, remove from storage
                            sessionStorage.removeItem('offboarding_employee_visit');
                        }
                    } catch (e) {
                        console.error('Error parsing visit data:', e);
                        sessionStorage.removeItem('offboarding_employee_visit');
                    }
                }
            }

            // Check on page load
            checkEmployeePageReturn();

            // Also check when page becomes visible (user switches back to tab)
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    setTimeout(checkEmployeePageReturn, 500); // Small delay to ensure page is fully loaded
                }
            });

            // Check on focus (when user returns to window)
            $(window).on('focus', function() {
                setTimeout(checkEmployeePageReturn, 500);
            });
        });
    </script>
@endpush

