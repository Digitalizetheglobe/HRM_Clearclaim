@extends('layouts.admin')

@section('page-title')
    {{ __('To Do List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee') }}</li>
@endsection

@section('action-button')
    <a href="#" data-url="{{ route('todo.create') }}" data-ajax-popup="true"
        data-title="{{ __('Create New ToDo') }}" data-size="lg" data-bs-toggle="tooltip" title="Create"
        class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
    </a>
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
                                <th>{{ __('Task Title') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th width="130px">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tasks as $task)
                                <tr>
                                    <td>{{ $task->task }}</td>
                                    <td>
                                        @if ($task->priority == 'high')
                                            <span class="badge bg-danger">{{ __('High') }}</span>
                                        @elseif ($task->priority == 'medium')
                                            <span class="badge bg-warning">{{ __('Medium') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('Low') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $task->expires_at ? \Carbon\Carbon::parse($task->expires_at)->format('Y-m-d') : __('No Deadline') }}</td>
                                    <td>
                                        @if ($task->is_completed)
                                            <span class="badge bg-success">{{ __('Completed') }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                    <div class="action-btn bg-info ms-2">
                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
                                            data-url="{{ route('todo.edit', $task->id) }}"
                                            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
                                            data-title="{{ __('Edit ToDo') }}" data-bs-original-title="{{ __('Edit') }}">
                                            <i class="ti ti-pencil text-white"></i>
                                        </a>
                                    </div>
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['todo.destroy', $task->id],
                                            
                                        ]) !!}
                                        <a href="#"
                                            class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                            data-bs-toggle="tooltip" title="{{ __('Delete Task') }}"
                                            data-bs-original-title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"
                                            onclick="event.preventDefault(); document.getElementById('delete-form-{{ $task->id }}').submit();">
                                            <i class="ti ti-trash text-white"></i>
                                        </a>
                                        {!! Form::close() !!}
                                    </div>
  
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
@endsection

@push('scripts')
<!-- Include DataTables JS -->
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

<!-- <script type="text/javascript">
    $(document).ready(function() {
        $('#pc-dt-simple').DataTable({
            "language": {
                "emptyTable": "No entries found" // This will show when there are no tasks in the table
            },
            "lengthMenu": [10, 25, 50, 100],  // Controls the number of entries per page
        });
    });
</script> -->
@endpush
