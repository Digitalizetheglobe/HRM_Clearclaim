@extends('layouts.admin')
@section('page-title')
    {{ __('On-Boarding Steps') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('On-Boarding Steps') }}</li>
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
        @if(\Auth::user()->type == 'company' || Gate::check('Manage Employee'))
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
                                url: '{{ route('onboarding.order') }}',
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
                        {{ Form::open(['route' => ['onboarding.index'], 'method' => 'get', 'id' => 'onboarding_filter']) }}
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
                                            onclick="document.getElementById('onboarding_filter').submit(); return false;"
                                            data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('onboarding.index') }}" class="btn btn-sm btn-danger"
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
                    $json[] = 'kanban-onboarding-' . $stage->id;
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
                                                        <a href="{{ route('employee.show', \Crypt::encrypt($process->employee->id)) }}" 
                                                           class="process-link onboarding-step-1-link"
                                                           data-process-id="{{ $process->id }}"
                                                           data-employee-id="{{ $process->employee->id }}"
                                                           data-step="1">
                                                            {{ $process->employee->name }}
                                                        </a>
                                                    @elseif($stage->order == 2)
                                                        <a href="{{ route('employee.edit', \Crypt::encrypt($process->employee->id)) }}" 
                                                           class="process-link onboarding-step-2-link"
                                                           data-process-id="{{ $process->id }}"
                                                           data-employee-id="{{ $process->employee->id }}"
                                                           data-step="2">
                                                            {{ $process->employee->name }}
                                                        </a>
                                                    @else
                                                        <a href="#" 
                                                            data-url="{{ route('onboarding.step', ['id' => $process->id, 'step' => $stage->order]) }}"
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
                                                            <a href="{{ route('employee.show', \Crypt::encrypt($process->employee->id)) }}"
                                                                class="dropdown-item onboarding-step-1-link"
                                                                data-process-id="{{ $process->id }}"
                                                                data-employee-id="{{ $process->employee->id }}"
                                                                data-step="1"
                                                                target="_blank"><i
                                                                    class="ti ti-eye "></i><span
                                                                    class="ms-2">{{ __('View Employee Details') }}</span></a>
                                                        @elseif($stage->order == 2)
                                                            <a href="{{ route('employee.edit', \Crypt::encrypt($process->employee->id)) }}"
                                                                class="dropdown-item onboarding-step-2-link"
                                                                data-process-id="{{ $process->id }}"
                                                                data-employee-id="{{ $process->employee->id }}"
                                                                data-step="2"
                                                                target="_blank"><i
                                                                    class="ti ti-edit "></i><span
                                                                    class="ms-2">{{ __('Edit Employee') }}</span></a>
                                                        @else
                                                            <a href="#" 
                                                                data-url="{{ route('onboarding.step', ['id' => $process->id, 'step' => $stage->order]) }}"
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

                                                    @if($stage->order == 4 && $process->system_access_checklist)
                                                        @php
                                                            $accessItems = is_array($process->system_access_checklist) ? $process->system_access_checklist : [];
                                                            $accessDone = count(array_filter($accessItems, function($item) { return isset($item['done']) && $item['done']; }));
                                                            $accessTotal = count($accessItems);
                                                        @endphp
                                                        <li class="list-inline-item">
                                                            <small>{{ $accessDone }}/{{ $accessTotal }} Done</small>
                                                        </li>
                                                    @endif

                                                    @if($stage->order == 5 && $process->asset_issuance_checklist)
                                                        @php
                                                            $assetItems = is_array($process->asset_issuance_checklist) ? $process->asset_issuance_checklist : [];
                                                            $assetIssued = count(array_filter($assetItems, function($item) { return isset($item['issued']) && $item['issued']; }));
                                                            $assetTotal = count($assetItems);
                                                        @endphp
                                                        <li class="list-inline-item">
                                                            <small>{{ $assetIssued }}/{{ $assetTotal }} Issued</small>
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

            // Track when user clicks on employee link in step 1
            $(document).on('click', 'a.onboarding-step-1-link', function(e) {
                var processId = $(this).data('process-id');
                var employeeId = $(this).data('employee-id');
                
                if (processId && employeeId) {
                    sessionStorage.setItem('onboarding_employee_visit_step1', JSON.stringify({
                        processId: processId,
                        employeeId: employeeId,
                        step: 1,
                        timestamp: new Date().getTime()
                    }));
                }
            });

            // Track when user clicks on employee link in step 2
            $(document).on('click', 'a.onboarding-step-2-link', function(e) {
                var processId = $(this).data('process-id');
                var employeeId = $(this).data('employee-id');
                
                if (processId && employeeId) {
                    sessionStorage.setItem('onboarding_employee_visit_step2', JSON.stringify({
                        processId: processId,
                        employeeId: employeeId,
                        step: 2,
                        timestamp: new Date().getTime()
                    }));
                }
            });

            // Check if user returned from employee page (Step 1)
            function checkEmployeePageReturnStep1() {
                var visitData = sessionStorage.getItem('onboarding_employee_visit_step1');
                
                if (visitData) {
                    try {
                        var data = JSON.parse(visitData);
                        var processId = data.processId;
                        var timestamp = data.timestamp;
                        var timeDiff = new Date().getTime() - timestamp;
                        
                        if (timeDiff < 600000) { // 10 minutes
                            setTimeout(function() {
                                var confirmModalHtml = '<div class="modal fade" id="employee-confirm-modal-step1" tabindex="-1" role="dialog">' +
                                    '<div class="modal-dialog modal-dialog-centered" role="document">' +
                                    '<div class="modal-content">' +
                                    '<div class="modal-header">' +
                                    '<h5 class="modal-title">{{ __("Employee Creation Verification") }}</h5>' +
                                    '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                                    '</div>' +
                                    '<div class="modal-body">' +
                                    '<p class="mb-0">{{ __("Employee created properly. Please confirm.") }}</p>' +
                                    '</div>' +
                                    '<div class="modal-footer">' +
                                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</button>' +
                                    '<button type="button" class="btn btn-primary" id="employee-confirm-ok-step1">{{ __("Confirm") }}</button>' +
                                    '</div>' +
                                    '</div></div></div>';
                                
                                $('body').append(confirmModalHtml);
                                $('#employee-confirm-modal-step1').modal('show');
                                
                                var userClickedYes = false;
                                
                                $('#employee-confirm-ok-step1').on('click', function() {
                                    userClickedYes = true;
                                    $('#employee-confirm-modal-step1').modal('hide');
                                });
                                
                                $('#employee-confirm-modal-step1').on('hidden.bs.modal', function() {
                                    $(this).remove();
                                    
                                    if (userClickedYes) {
                                        $.ajax({
                                            url: '{{ url("onboarding") }}/' + processId + '/step/1',
                                            type: 'POST',
                                            data: {
                                                confirmed: 'yes',
                                                _token: $('meta[name="csrf-token"]').attr('content')
                                            },
                                            success: function(data) {
                                                show_toastr('Success', data.message || '{{ __("Step completed successfully") }}', 'success');
                                                location.reload();
                                            },
                                            error: function(data) {
                                                show_toastr('Error', data.responseJSON.error || '{{ __("An error occurred") }}', 'error');
                                            }
                                        });
                                    }
                                    
                                    sessionStorage.removeItem('onboarding_employee_visit_step1');
                                });
                            }, 500);
                        } else {
                            sessionStorage.removeItem('onboarding_employee_visit_step1');
                        }
                    } catch (e) {
                        console.error('Error parsing visit data:', e);
                        sessionStorage.removeItem('onboarding_employee_visit_step1');
                    }
                }
            }

            // Check if user returned from employee page (Step 2)
            function checkEmployeePageReturnStep2() {
                var visitData = sessionStorage.getItem('onboarding_employee_visit_step2');
                
                if (visitData) {
                    try {
                        var data = JSON.parse(visitData);
                        var processId = data.processId;
                        var timestamp = data.timestamp;
                        var timeDiff = new Date().getTime() - timestamp;
                        
                        if (timeDiff < 600000) { // 10 minutes
                            setTimeout(function() {
                                var confirmModalHtml = '<div class="modal fade" id="employee-confirm-modal-step2" tabindex="-1" role="dialog">' +
                                    '<div class="modal-dialog modal-dialog-centered" role="document">' +
                                    '<div class="modal-content">' +
                                    '<div class="modal-header">' +
                                    '<h5 class="modal-title">{{ __("Document Upload Verification") }}</h5>' +
                                    '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                                    '</div>' +
                                    '<div class="modal-body">' +
                                    '<p class="mb-0">{{ __("Documents uploaded and verified successfully") }}</p>' +
                                    '</div>' +
                                    '<div class="modal-footer">' +
                                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</button>' +
                                    '<button type="button" class="btn btn-primary" id="employee-confirm-ok-step2">{{ __("Confirm") }}</button>' +
                                    '</div>' +
                                    '</div></div></div>';
                                
                                $('body').append(confirmModalHtml);
                                $('#employee-confirm-modal-step2').modal('show');
                                
                                var userClickedYes = false;
                                
                                $('#employee-confirm-ok-step2').on('click', function() {
                                    userClickedYes = true;
                                    $('#employee-confirm-modal-step2').modal('hide');
                                });
                                
                                $('#employee-confirm-modal-step2').on('hidden.bs.modal', function() {
                                    $(this).remove();
                                    
                                    if (userClickedYes) {
                                        $.ajax({
                                            url: '{{ url("onboarding") }}/' + processId + '/step/2',
                                            type: 'POST',
                                            data: {
                                                confirmed: 'yes',
                                                _token: $('meta[name="csrf-token"]').attr('content')
                                            },
                                            success: function(data) {
                                                show_toastr('Success', data.message || '{{ __("Step completed successfully") }}', 'success');
                                                location.reload();
                                            },
                                            error: function(data) {
                                                show_toastr('Error', data.responseJSON.error || '{{ __("An error occurred") }}', 'error');
                                            }
                                        });
                                    }
                                    
                                    sessionStorage.removeItem('onboarding_employee_visit_step2');
                                });
                            }, 500);
                        } else {
                            sessionStorage.removeItem('onboarding_employee_visit_step2');
                        }
                    } catch (e) {
                        console.error('Error parsing visit data:', e);
                        sessionStorage.removeItem('onboarding_employee_visit_step2');
                    }
                }
            }

            // Check on page load
            checkEmployeePageReturnStep1();
            checkEmployeePageReturnStep2();

            // Also check when page becomes visible
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    setTimeout(function() {
                        checkEmployeePageReturnStep1();
                        checkEmployeePageReturnStep2();
                    }, 500);
                }
            });

            // Check on focus
            $(window).on('focus', function() {
                setTimeout(function() {
                    checkEmployeePageReturnStep1();
                    checkEmployeePageReturnStep2();
                }, 500);
            });
        });
    </script>
@endpush


