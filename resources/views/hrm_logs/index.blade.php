@extends('layouts.admin')

@section('page-title')
    {{ __('Action Logs') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Logs') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Activity Timeline') }}</h5>
                    <small class="text-muted">{{ __('All HR actions with user name, module, and time') }}</small>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('hrm.logs.index') }}" class="row g-2 align-items-end mb-4">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Module') }}</label>
                            <select name="module" class="form-control">
                                <option value="">{{ __('All modules') }}</option>
                                @foreach ($modules as $key => $label)
                                    <option value="{{ $key }}" @selected(request('module') == $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Action') }}</label>
                            <select name="action" class="form-control">
                                <option value="">{{ __('All actions') }}</option>
                                @foreach ($actions as $action)
                                    <option value="{{ $action }}" @selected(request('action') == $action)>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('From') }}</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('To') }}</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Search') }}</label>
                            <input type="text" name="q" class="form-control" placeholder="{{ __('User / employee') }}" value="{{ request('q') }}">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                        </div>
                    </form>

                    @if ($logs->count() == 0)
                        <div class="text-center py-5 text-muted">
                            <i class="ti ti-history" style="font-size: 48px;"></i>
                            <p class="mt-3 mb-0">{{ __('No action logs yet. New activity will appear here as a timeline.') }}</p>
                        </div>
                    @else
                        <div class="hrm-timeline">
                            @foreach ($logs as $log)
                                @php
                                    $initial = strtoupper(substr($log->actor_name ?: 'U', 0, 1));
                                    $actionClass = [
                                        'applied' => 'bg-info',
                                        'created' => 'bg-info',
                                        'updated' => 'bg-primary',
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'deleted' => 'bg-danger',
                                        'paid' => 'bg-success',
                                        'generated' => 'bg-warning',
                                        'exported' => 'bg-secondary',
                                        'incremented' => 'bg-success',
                                    ][$log->action] ?? 'bg-dark';
                                @endphp
                                <div class="hrm-timeline-item">
                                    <div class="hrm-timeline-dot {{ $actionClass }}"></div>
                                    <div class="hrm-timeline-card">
                                        <div class="d-flex justify-content-between flex-wrap gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="hrm-avatar">{{ $initial }}</span>
                                                <div>
                                                    <strong>{{ $log->actor_name ?: __('Unknown user') }}</strong>
                                                    <div class="text-muted small">
                                                        {{ ucfirst($log->actor_type ?: 'user') }}
                                                        @if ($log->ip_address)
                                                            · IP {{ $log->ip_address }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-light text-dark border">{{ $log->moduleLabel() }}</span>
                                                <span class="badge {{ $actionClass }}">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                                <div class="text-muted small mt-1">
                                                    {{ $log->created_at->format('d M Y, h:i A') }}
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mb-1 mt-3">{{ $log->description }}</p>
                                        @if ($log->employee_name)
                                            <div class="small text-muted">
                                                {{ __('Employee') }}: <strong>{{ $log->employee_name }}</strong>
                                            </div>
                                        @endif
                                        @if (!empty($log->properties) && is_array($log->properties))
                                            <div class="small mt-2 hrm-props">
                                                @foreach ($log->properties as $key => $value)
                                                    @if (!is_array($value) && $value !== null && $value !== '')
                                                        <span class="me-3"><span class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> {{ $value }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css-page')
<style>
    .hrm-timeline { position: relative; padding-left: 28px; }
    .hrm-timeline:before {
        content: '';
        position: absolute;
        left: 8px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: #e5e7eb;
    }
    .hrm-timeline-item { position: relative; margin-bottom: 18px; }
    .hrm-timeline-dot {
        position: absolute;
        left: -24px;
        top: 18px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #e5e7eb;
    }
    .hrm-timeline-card {
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 10px;
        padding: 16px 18px;
    }
    .hrm-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #0f172a;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
</style>
@endpush
