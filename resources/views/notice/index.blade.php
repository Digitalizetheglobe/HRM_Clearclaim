@extends('layouts.admin')

@section('page-title')
    {{ __('Notice List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Notice List') }}</li>
@endsection

@section('action-button')
    @if(Auth::user()->type != 'hr') {{-- Only show export and create for non-HR users --}}
        <div class="row align-items-center m-1">
            @can('Create Employee')
                <a href="#" data-size="lg" data-url="{{ route('notices.create') }}" data-ajax-popup="true"
                    data-bs-toggle="tooltip" title="{{ __('Create New Notice') }}" data-title="{{ __('Add New Notice') }}"
                    class="btn btn-sm btn-primary">
                    <i class="ti ti-plus"></i>
                </a>
            @endcan
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
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Description') }}</th>
                                @if(Auth::user()->hasCompanyAccess())
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                @endif
                                <th width="130px">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notices as $notice)
                                <tr>
                                    <td>{{ $notice->title }}</td>
                                    <td>{{ Str::limit($notice->description, 50) }}</td>
                                    @if(Auth::user()->hasCompanyAccess())
                                        <td>{{ $notice->notice_startdate ? \Carbon\Carbon::parse($notice->notice_startdate)->format('d M Y') : '-' }}</td>
                                        <td>{{ $notice->notice_enddate ? \Carbon\Carbon::parse($notice->notice_enddate)->format('d M Y') : '-' }}</td>
                                    @endif
                                    <td class="d-flex gap-2">
                                        <!-- Show Button -->
                                        <a href="#" 
                                            class="btn btn-sm btn-primary text-white" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#noticeModal{{ $notice->id }}"
                                            data-bs-toggle="tooltip" 
                                            title="{{ __('Show Notice') }}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        
                                        @if (Auth::user()->type != 'hr' && (Gate::check('Edit Meeting') || Gate::check('Delete Meeting')))
                                            @can('Edit Meeting')
                                                <!-- Edit Button -->
                                                <a href="#" 
                                                    class="btn btn-sm btn-info text-white" 
                                                    data-url="{{ route('notices.edit', $notice->id) }}" 
                                                    data-ajax-popup="true" 
                                                    data-size="lg" 
                                                    data-bs-toggle="tooltip" 
                                                    data-title="{{ __('Edit Notice') }}" 
                                                    data-bs-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil"></i>
                                                </a>
                                            @endcan

                                            @can('Delete Meeting')
                                                <!-- Delete Button with Form -->
                                                <form id="delete-form-{{ $notice->id }}" method="POST" action="{{ route('notices.destroy', $notice->id) }}" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>

                                                <a href="#" class="btn btn-sm btn-danger text-white"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Delete Notice') }}"
                                                    onclick="event.preventDefault(); document.getElementById('delete-form-{{ $notice->id }}').submit();">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                            @endcan
                                        @endif
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

<!-- Notice Modal Popups -->
@foreach ($notices as $notice)
<div class="modal fade" id="noticeModal{{ $notice->id }}" tabindex="-1" aria-labelledby="noticeModalLabel{{ $notice->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noticeModalLabel{{ $notice->id }}">{{ __('Notice Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="fw-bold">{{ __('Title') }}</h6>
                        <p>{{ $notice->title }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="fw-bold">{{ __('Description') }}</h6>
                        <p>{{ $notice->description }}</p>
                    </div>
                </div>
                @if(Auth::user()->hasCompanyAccess())
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold">{{ __('Start Date') }}</h6>
                            <p>{{ $notice->notice_startdate ? \Carbon\Carbon::parse($notice->notice_startdate)->format('d M Y') : '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">{{ __('End Date') }}</h6>
                            <p>{{ $notice->notice_enddate ? \Carbon\Carbon::parse($notice->notice_enddate)->format('d M Y') : '-' }}</p>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('#pc-dt-simple').DataTable({
            "language": {
                "emptyTable": "No notices found"
            },
            "lengthMenu": [10, 25, 50, 100],
        });
    });
</script>
@endpush