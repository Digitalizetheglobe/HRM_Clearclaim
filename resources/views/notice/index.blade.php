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
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                @if (Auth::user()->type != 'hr' && (Gate::check('Edit Meeting') || Gate::check('Delete Meeting')))
                                    <th width="130px">{{ __('Actions') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notices as $notice)
                                <tr>
                                    <td>{{ $notice->title }}</td>
                                    <td>{{ Str::limit($notice->description, 50) }}</td>
                                    <td>{{ $notice->notice_startdate ? \Carbon\Carbon::parse($notice->notice_startdate)->format('d M Y') : '-' }}</td>
                                    <td>{{ $notice->notice_enddate ? \Carbon\Carbon::parse($notice->notice_enddate)->format('d M Y') : '-' }}</td>
                                    @if (Auth::user()->type != 'hr' && (Gate::check('Edit Meeting') || Gate::check('Delete Meeting')))
                                        <td class="d-flex gap-2">
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
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('#notice-table').DataTable({
            "language": {
                "emptyTable": "No notices found"
            },
            "lengthMenu": [10, 25, 50, 100],
        });
    });
</script>
@endpush