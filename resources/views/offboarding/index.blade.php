@extends('layouts.admin')
@section('page-title')
    {{ __('Off-Boarding Steps') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Off-Boarding Steps') }}</li>
@endsection

@php
    $stageColors = ['#00be5d','#00a852','#00d468','#009e4d','#00c962','#007a3d','#00e673','#005a2d','#00d468','#00be5d'];
    $stageIcons  = ['ti-file-text','ti-door-exit','ti-help','ti-lock-off','ti-package','ti-receipt','ti-circle-x','ti-upload','ti-message-report','ti-circle-check'];
@endphp

@push('css-page')
<link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}">
<style>
    /* ── Base ── */
    .ob-wrapper { padding: 0 4px; }

    /* ── Filter bar ── */
    .ob-filter-card {
        background: linear-gradient(135deg,#007a3d 0%,#005a2d 100%);
        border-radius: 16px;
        padding: 22px 28px;
        margin-bottom: 28px;
        box-shadow: 0 8px 32px rgba(0,190,93,.22);
        border: none;
        margin-top: 16px;
    }
    .ob-filter-card .form-label {
        color: rgba(255,255,255,.75);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .8px;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .ob-filter-card .form-control,
    .ob-filter-card .form-select {
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 10px;
        color: #fff;
        font-size: 13px;
        height: 40px;
    }
    .ob-filter-card .form-control:focus,
    .ob-filter-card .form-select:focus {
        background: rgba(255,255,255,.14);
        border-color: #00be5d;
        box-shadow: 0 0 0 3px rgba(0,190,93,.3);
        color: #fff;
    }
    .ob-filter-card option { background:#005a2d; color:#fff; }
    .ob-filter-card .btn-apply {
        background: linear-gradient(135deg,#00be5d,#007a3d);
        border: none;
        border-radius: 10px;
        color: #fff;
        width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 12px rgba(0,190,93,.35);
        transition: transform .2s, box-shadow .2s;
    }
    .ob-filter-card .btn-apply:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,190,93,.5); }
    .ob-filter-card .btn-reset {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 10px;
        color: #fff;
        width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        transition: background .2s;
    }
    .ob-filter-card .btn-reset:hover { background: rgba(255,59,59,.25); }

    /* ── Kanban wrapper ── */
    .ob-kanban-scroll {
        display: flex;
        gap: 18px;
        overflow-x: auto;
        padding-bottom: 16px;
        align-items: flex-start;
        min-height: 70vh;
    }
    .ob-kanban-scroll::-webkit-scrollbar { height: 6px; }
    .ob-kanban-scroll::-webkit-scrollbar-track { background: #f0f2f5; border-radius: 3px; }
    .ob-kanban-scroll::-webkit-scrollbar-thumb { background: #cbd0e0; border-radius: 3px; }

    /* ── Stage column ── */
    .ob-stage {
        flex: 0 0 270px;
        max-width: 270px;
        display: flex;
        flex-direction: column;
        border-radius: 16px;
        background: #f7f8fc;
        border: 1px solid #e8eaf2;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,.06);
    }
    .ob-stage-header {
        padding: 14px 16px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid #e8eaf2;
        position: relative;
    }
    .ob-stage-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: var(--stage-color, #00be5d);
        border-radius: 0;
    }
    .ob-stage-icon {
        width: 34px; height: 34px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: var(--stage-color-light, rgba(0,190,93,.12));
        color: var(--stage-color, #00be5d);
        font-size: 15px;
        flex-shrink: 0;
    }
    .ob-stage-title {
        font-size: 13px;
        font-weight: 700;
        color: #004d26;
        flex: 1;
        margin: 0;
        line-height: 1.3;
    }
    .ob-stage-count {
        background: var(--stage-color, #00be5d);
        color: #fff;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        min-width: 24px;
        text-align: center;
    }
    .ob-stage-body {
        padding: 12px 10px;
        min-height: 200px;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .ob-stage-body .empty-container {
        flex: 1;
        min-height: 100px;
    }

    /* ── Process card ── */
    .ob-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e8eaf2;
        box-shadow: 0 2px 8px rgba(0,0,0,.05);
        transition: transform .2s, box-shadow .2s, border-color .2s;
        cursor: grab;
        overflow: hidden;
    }
    .ob-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,190,93,.18);
        border-color: #b3f0d0;
    }
    .ob-card:active { cursor: grabbing; }
    .ob-card-top {
        height: 4px;
        background: var(--stage-color, #00be5d);
    }
    .ob-card-body { padding: 12px 14px 10px; }
    .ob-card-header-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 6px;
        margin-bottom: 8px;
    }
    .ob-card-name {
        font-size: 13px;
        font-weight: 700;
        color: #004d26;
        line-height: 1.3;
        flex: 1;
    }
    .ob-card-name a {
        color: inherit;
        text-decoration: none;
    }
    .ob-card-name a:hover { color: #00be5d; }
    .ob-card-menu .btn {
        padding: 2px 5px;
        color: #9aa0b4;
        background: none;
        border: none;
        font-size: 15px;
        line-height: 1;
    }
    .ob-card-menu .btn:hover { color: #00be5d; }
    .ob-card-email {
        font-size: 11px;
        color: #7b8299;
        margin-bottom: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ob-card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11px;
        color: #9aa0b4;
    }
    .ob-card-date { display: flex; align-items: center; gap: 4px; }
    .ob-card-progress {
        font-size: 10px;
        font-weight: 600;
        color: #fff;
        background: var(--stage-color, #00be5d);
        border-radius: 20px;
        padding: 2px 8px;
    }
    .ob-avatar {
        width: 26px; height: 26px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.15);
    }

    /* ── Drag ghost ── */
    .gu-mirror { opacity: .85; transform: rotate(2deg); }
</style>
@endpush

@push('script-page')
<script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
<script>
    @if(\Auth::user()->hasCompanyAccess() || Gate::check('Manage Resignation'))
    !function(a){"use strict";var t=function(){this.$body=a("body")};t.prototype.init=function(){a('[data-plugin="dragula"]').each(function(){var t=a(this).data("containers"),n=[];if(t)for(var i=0;i<t.length;i++)n.push(a("#"+t[i])[0]);else n=[a(this)[0]];var r=a(this).data("handleclass");r?dragula(n,{moves:function(a,t,n){return n.classList.contains(r)}}):dragula(n).on('drop',function(el,target,source,sibling){var order=[];$("#"+target.id+"> div").each(function(){order[$(this).index()]=$(this).attr('data-id')});var id=$(el).attr('data-id');var old_status=$("#"+source.id).data('status');var new_status=$("#"+target.id).data('status');var stage_id=$(target).attr('data-id');var sourceVisibleCount=$("#"+source.id+"> div:not(.d-none):not([style*='display: none'])").length;var targetVisibleCount=$("#"+target.id+"> div:not(.d-none):not([style*='display: none'])").length;$("#"+source.id).parent().find('.ob-stage-count').text(sourceVisibleCount);$("#"+target.id).parent().find('.ob-stage-count').text(targetVisibleCount);$.ajax({url:'{{ route('offboarding.order') }}',type:'POST',data:{order:order,stage_id:stage_id,"_token":$('meta[name="csrf-token"]').attr('content')},success:function(data){show_toastr('Success','Process successfully updated','success')},error:function(data){data=data.responseJSON;show_toastr('Error',data.error,'error')}})})})},a.Dragula=new t,a.Dragula.Constructor=t}(window.jQuery),function(a){"use strict";a.Dragula.init()}(window.jQuery);
    @endif

    window.showOffboardingCompletedPopup = function() {
        var modalHtml = '<div class="modal fade" id="offboarding-completed-modal" tabindex="-1" aria-labelledby="offboardingCompletedModalLabel" aria-hidden="true">' +
            '<div class="modal-dialog modal-dialog-centered">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title" id="offboardingCompletedModalLabel">{{ __("Offboarding Completed") }}</h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
            '</div>' +
            '<div class="modal-body text-center">' +
            '<div class="mb-3">' +
            '<i class="ti ti-circle-check" style="font-size: 48px; color: #00be5d;"></i>' +
            '</div>' +
            '<p class="mb-0 fs-5">{{ __("This employee has been properly offboarded.") }}</p>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Close") }}</button>' +
            '</div>' +
            '</div></div></div>';
        
        $('body').append(modalHtml);
        $('#offboarding-completed-modal').modal('show');
        
        $('#offboarding-completed-modal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    };
</script>
<script>
$(document).ready(function(){
    var now=new Date();var month=(now.getMonth()+1);var day=now.getDate();
    if(month<10)month="0" + month;if(day<10)day="0" + day;
    var today=now.getFullYear()+'-'+month+'-'+day;
    $('.current_date').val(today);

    $(document).on('click','a.hr-uploads-link',function(e){
        var processId=$(this).data('process-id');
        var href=$(this).attr('href');
        var employeeId=null;
        if(href&&href.includes('/employee/')){
            var parts=href.split('/');
            employeeId=parts[parts.length-1];
        }
        if(processId&&employeeId){
            sessionStorage.setItem('offboarding_employee_visit',JSON.stringify({
                processId:processId,employeeId:employeeId,step:8,timestamp:new Date().getTime()
            }));
        }
    });

    function checkEmployeePageReturn() {
        var visitData = sessionStorage.getItem('offboarding_employee_visit');
        if (visitData) {
            try {
                var data = JSON.parse(visitData);
                var processId = data.processId;
                if (new Date().getTime() - data.timestamp < 600000) {
                    setTimeout(function() {
                        var confirmModalHtml = '<div class="modal fade" id="download-confirm-modal" tabindex="-1" role="dialog">' +
                            '<div class="modal-dialog modal-dialog-centered">' +
                            '<div class="modal-content">' +
                            '<div class="modal-header">' +
                            '<h5 class="modal-title">{{ __("Download Confirmation") }}</h5>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                            '</div>' +
                            '<div class="modal-body">' +
                            '<p class="mb-0">{{ __("Did you download Experience Certificate from the employee details page?") }}</p>' +
                            '</div>' +
                            '<div class="modal-footer">' +
                            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="download-confirm-cancel">{{ __("No") }}</button>' +
                            '<button type="button" class="btn btn-primary" id="download-confirm-ok">{{ __("Yes") }}</button>' +
                            '</div>' +
                            '</div></div></div>';
                        
                        $('body').append(confirmModalHtml);
                        $('#download-confirm-modal').modal('show');
                        
                        var userClickedYes = false;
                        $('#download-confirm-ok').on('click', function() {
                            userClickedYes = true;
                            $('#download-confirm-modal').modal('hide');
                        });
                        $('#download-confirm-cancel, .btn-close').on('click', function() {
                            sessionStorage.removeItem('offboarding_employee_visit');
                        });
                        
                        $('#download-confirm-modal').on('hidden.bs.modal', function() {
                            $(this).remove();
                            if (userClickedYes) {
                                $.ajax({
                                    url: '{{ route("offboarding.update-step", ["id" => ":id", "step" => 9]) }}'.replace(':id', processId),
                                    type: 'POST',
                                    data: {
                                        confirmed: 'yes',
                                        document_type: 'experience_certificate',
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(r) {
                                        show_toastr('Success', r.message || 'Document confirmed successfully', 'success');
                                        location.reload();
                                    },
                                    error: function(xhr) {
                                        var errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'An error occurred';
                                        show_toastr('Error', errorMsg, 'error');
                                    }
                                });
                            }
                        });
                    }, 500);
                } else {
                    sessionStorage.removeItem('offboarding_employee_visit');
                }
            } catch (e) {
                sessionStorage.removeItem('offboarding_employee_visit');
            }
        }
    }

    checkEmployeePageReturn();
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(checkEmployeePageReturn, 500);
        }
    });
    $(window).on('focus', function() {
        setTimeout(checkEmployeePageReturn, 500);
    });
});
</script>
@endpush

@section('content')
<div class="ob-wrapper">

    {{-- ── Filter Bar ── --}}
    <div class="ob-filter-card">
        {{ Form::open(['route' => ['offboarding.index'], 'method' => 'get', 'id' => 'offboarding_filter']) }}
        <div class="row g-3 align-items-end">
            <div class="col-xl-3 col-md-4 col-sm-6">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                {{ Form::date('start_date', $filter['start_date'], ['class' => 'form-control current_date']) }}
            </div>
            <div class="col-xl-3 col-md-4 col-sm-6">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'form-control current_date', 'autocomplete' => 'off']) }}
            </div>
            <div class="col-xl-4 col-md-4 col-sm-8">
                {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                {{ Form::select('employee', $employees, $filter['employee'], ['class' => 'form-control', 'id' => 'employee_id']) }}
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-apply" data-bs-toggle="tooltip" title="{{ __('Apply Filter') }}">
                        <i class="ti ti-search" style="font-size:16px;"></i>
                    </button>
                    <a href="{{ route('offboarding.index') }}" class="btn-reset" data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                        <i class="ti ti-refresh" style="font-size:16px;"></i>
                    </a>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>

    {{-- ── Kanban Board ── --}}
    @php
        $json = [];
        foreach ($stages as $stage) {
            $json[] = 'kanban-offboarding-' . $stage->id;
        }
    @endphp

    <div class="ob-kanban-scroll" data-plugin="dragula" data-containers='{!! json_encode($json) !!}'>
        @foreach ($stages as $key => $stage)
            @php
                try { $processes = $stage->processes($filter); } catch (\Exception $e) { $processes = collect([]); }
                
                // Calculate initial visible cards count
                $visibleCount = 0;
                foreach ($processes as $process) {
                    if ($stage->order == 10 && $process->updated_at) {
                        $daysSinceCompletion = now()->diffInDays($process->updated_at);
                        if ($daysSinceCompletion <= 5) { $visibleCount++; }
                    } else {
                        $visibleCount++;
                    }
                }

                $color      = $stageColors[$key % count($stageColors)];
                $icon       = $stageIcons[$key % count($stageIcons)];
                $colorRgb   = implode(',', sscanf($color, '#%02x%02x%02x'));
            @endphp

            <div class="ob-stage" style="--stage-color:{{ $color }}; --stage-color-light:rgba({{ $colorRgb }},.12);">
                {{-- Stage Header --}}
                <div class="ob-stage-header">
                    <div class="ob-stage-icon"><i class="ti {{ $icon }}"></i></div>
                    <h4 class="ob-stage-title">{{ $stage->title }}</h4>
                    <span class="ob-stage-count count">{{ $visibleCount }}</span>
                </div>

                {{-- Stage Body / Kanban Cards --}}
                <div class="ob-stage-body kanban-box" id="{{ $json[$key] }}" data-id="{{ $stage->id }}" data-status="{{ $stage->id }}">
                    @foreach ($processes as $process)
                        @php
                            $shouldHideCard = false;
                            if ($stage->order == 10 && $process->updated_at) {
                                $daysSinceCompletion = now()->diffInDays($process->updated_at);
                                $shouldHideCard = $daysSinceCompletion > 5;
                            }
                        @endphp

                        <div class="ob-card {{ $shouldHideCard ? 'd-none' : '' }}" data-id="{{ $process->id }}" @if($shouldHideCard) style="display: none;" @endif>
                            <div class="ob-card-top"></div>
                            <div class="ob-card-body">
                                <div class="ob-card-header-row">
                                    <div class="ob-card-name">
                                        @if($process->employee)
                                            @if($stage->order == 1)
                                                <a href="#"
                                                   data-url="{{ route('offboarding.step', ['id' => $process->id, 'step' => $stage->order]) }}"
                                                   data-ajax-popup="true"
                                                   data-size="lg"
                                                   data-title="{{ $stage->title }} — {{ $process->employee->name }}"
                                                   class="process-link">
                                                    {{ $process->employee->name }}
                                                </a>
                                            @elseif($stage->order == 3)
                                                <a href="#"
                                                   data-url="{{ route('offboarding.step', ['id' => $process->id, 'step' => $stage->order]) }}"
                                                   data-ajax-popup="true"
                                                   data-size="lg"
                                                   data-title="{{ $stage->title }} — {{ $process->employee->name }}"
                                                   class="process-link">
                                                    {{ $process->employee->name }}
                                                </a>
                                            @elseif($stage->order == 2)
                                                <a href="{{ route('resignation.index') }}" class="process-link">
                                                    {{ $process->employee->name }}
                                                </a>
                                            @elseif($stage->order == 7)
                                                <a href="{{ route('termination.index') }}" class="process-link">
                                                    {{ $process->employee->name }}
                                                </a>
                                            @elseif($stage->order == 8)
                                                <a href="{{ route('employee.show', \Crypt::encrypt($process->employee->id)) }}"
                                                   class="process-link hr-uploads-link"
                                                   data-process-id="{{ $process->id }}"
                                                   data-employee-id="{{ $process->employee->id }}"
                                                   data-step="8">
                                                    {{ $process->employee->name }}
                                                </a>
                                            @elseif($stage->order == 10)
                                                <a href="#"
                                                   onclick="showOffboardingCompletedPopup(); return false;"
                                                   class="process-link">
                                                    {{ $process->employee->name }}
                                                </a>
                                            @else
                                                <a href="#"
                                                   data-url="{{ route('offboarding.step', ['id' => $process->id, 'step' => $stage->order]) }}"
                                                   data-ajax-popup="true"
                                                   data-size="lg"
                                                   data-title="{{ $stage->title }} — {{ $process->employee->name }}"
                                                   class="process-link">
                                                    {{ $process->employee->name }}
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">{{ __('N/A') }}</span>
                                        @endif
                                    </div>

                                    {{-- Dropdown menu --}}
                                    <div class="ob-card-menu dropdown">
                                        <button class="btn" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:13px; min-width:180px;">
                                            @if($stage->order == 1 && $process->employee)
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                       data-url="{{ route('offboarding.step', ['id' => $process->id, 'step' => $stage->order]) }}"
                                                       data-ajax-popup="true"
                                                       data-size="lg"
                                                       data-title="{{ $stage->title }} — {{ $process->employee->name }}">
                                                        <i class="ti ti-eye me-2 text-info"></i>{{ __('View Details') }}
                                                    </a>
                                                </li>
                                            @elseif($stage->order == 3 && $process->employee)
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                       data-url="{{ route('offboarding.step', ['id' => $process->id, 'step' => $stage->order]) }}"
                                                       data-ajax-popup="true"
                                                       data-size="lg"
                                                       data-title="{{ $stage->title }} — {{ $process->employee->name }}">
                                                        <i class="ti ti-eye me-2 text-info"></i>{{ __('View Details') }}
                                                    </a>
                                                </li>
                                            @elseif($stage->order == 2 && $process->employee)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('resignation.index') }}">
                                                        <i class="ti ti-eye me-2 text-primary"></i>{{ __('View Resignation') }}
                                                    </a>
                                                </li>
                                            @elseif($stage->order == 7 && $process->employee)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('termination.index') }}">
                                                        <i class="ti ti-eye me-2 text-danger"></i>{{ __('View Termination') }}
                                                    </a>
                                                </li>
                                            @elseif($stage->order == 8 && $process->employee)
                                                <li>
                                                    <a class="dropdown-item hr-uploads-link"
                                                       href="{{ route('employee.show', \Crypt::encrypt($process->employee->id)) }}"
                                                       data-process-id="{{ $process->id }}"
                                                       data-employee-id="{{ $process->employee->id }}"
                                                       data-step="8" target="_blank">
                                                        <i class="ti ti-eye me-2 text-primary"></i>{{ __('View Employee Details') }}
                                                    </a>
                                                </li>
                                            @elseif($stage->order == 10 && $process->employee)
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="showOffboardingCompletedPopup(); return false;">
                                                        <i class="ti ti-eye me-2 text-success"></i>{{ __('View Details') }}
                                                    </a>
                                                </li>
                                            @elseif($process->employee)
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                       data-url="{{ route('offboarding.step', ['id' => $process->id, 'step' => $stage->order]) }}"
                                                       data-ajax-popup="true"
                                                       data-size="lg"
                                                       data-title="{{ $stage->title }} — {{ $process->employee->name }}">
                                                        <i class="ti ti-eye me-2 text-info"></i>{{ __('View Details') }}
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>

                                {{-- Email --}}
                                @if($process->employee)
                                    <div class="ob-card-email">
                                        <i class="ti ti-mail me-1" style="font-size:11px;"></i>{{ $process->employee->email ?? '' }}
                                    </div>
                                @endif

                                {{-- Meta: date + progress + avatar --}}
                                <div class="ob-card-meta">
                                    <div class="ob-card-date">
                                        <i class="ti ti-calendar-event"></i>
                                        {{ \Auth::user()->dateFormat($process->created_at) }}
                                    </div>

                                    @if($stage->order == 4 && $process->access_removal_checklist)
                                        @php
                                            $items = is_array($process->access_removal_checklist) ? $process->access_removal_checklist : [];
                                            $done  = count(array_filter($items, fn($i) => !empty($i['done'])));
                                        @endphp
                                        <span class="ob-card-progress">{{ $done }}/{{ count($items) }} Done</span>
                                    @elseif($stage->order == 5 && $process->asset_collection_checklist)
                                        @php
                                            $assets = is_array($process->asset_collection_checklist) ? $process->asset_collection_checklist : [];
                                            $collected = count(array_filter($assets, fn($i) => !empty($i['collected'])));
                                        @endphp
                                        <span class="ob-card-progress">{{ $collected }}/{{ count($assets) }} Collected</span>
                                    @elseif($stage->order == 6 && $process->settlement_status)
                                        <span class="badge bg-{{ $process->settlement_status == 'completed' ? 'success' : 'warning' }}" style="font-size: 9px; padding: 3px 7px;">
                                            {{ ucfirst($process->settlement_status) }}
                                        </span>
                                    @endif

                                    @if($process->employee)
                                        @php
                                            $empAvatar = $process->employee->user->avatar ?? '';
                                            $hasRealAvatar = !empty($empAvatar) && $empAvatar !== 'avatar.png';
                                            $empName = $process->employee->name ?? 'N A';
                                            $parts = explode(' ', trim($empName));
                                            $initials = strtoupper(substr($parts[0],0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : ''));
                                        @endphp
                                        @if($hasRealAvatar)
                                            <img src="{{ asset('storage/uploads/avatar/' . $empAvatar) }}"
                                                 class="ob-avatar" alt="{{ $empName }}"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="ob-avatar ob-avatar-initials" style="display:none; background:#00be5d; color:#fff; font-size:10px; font-weight:700; align-items:center; justify-content:center;">{{ $initials }}</div>
                                        @else
                                            <div class="ob-avatar ob-avatar-initials" style="background:#00be5d; color:#fff; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center;">{{ $initials }}</div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <span class="empty-container" data-placeholder="Drop here"></span>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
