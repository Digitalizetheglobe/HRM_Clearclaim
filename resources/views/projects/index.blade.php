@extends('layouts.admin')

@section('page-title')
    {{ __('Project List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Project List') }}</li>
@endsection

@section('action-button')
    @if(Auth::user()->type != 'hr') {{-- Only show export and create for non-HR users --}}
        @can('Create Employee')
            <a href="#" data-url="{{ route('projects.create') }}" data-ajax-popup="true"
                data-title="{{ __('Add New Project') }}" data-size="lg" data-bs-toggle="tooltip" title="Create"
                class="btn btn-sm btn-primary d-flex align-items-center justify-content-center p-0"
                style="height: 30px; width: 30px;">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
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
                                <th>{{ __('Project Type') }}</th>
                                <th>{{ __('Project Name') }}</th>
                                <th>{{ __('Site Heads') }}</th>
                                <th>{{ __('Department') }}</th>
                                @if(Auth::user()->type != 'employee')
                                    <th>{{ __('Assigned Employees') }}</th>
                                @endif
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                @if (Auth::user()->type != 'hr' && (Gate::check('Edit Meeting') || Gate::check('Delete Meeting')))
                                <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                <tr>
                                    <td>{{ $project->project_type_name }}</td>
                                    <td>{{ $project->project_name }}</td>
                                    <td>
                                        @if(is_array($project->site_heads))
                                            @php $deptCount = 0; @endphp
                                            @foreach($project->site_heads as $siteHeadId)
                                                @if(isset($siteHeads[$siteHeadId]))
                                                    <span class="badge bg-secondary me-1 mb-1">
                                                        {{ $siteHeads[$siteHeadId]->user->name ?? __('Unknown') }}
                                                    </span>
                                                    @php $deptCount++; @endphp
                                                    @if($deptCount % 3 == 0)
                                                        <br>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @if(is_array($project->assigned_data))
                                            @php $deptCount = 0; @endphp
                                            @foreach($project->assigned_data as $assignment)
                                                @if(isset($departments[$assignment['department_id']]))
                                                    <span class="badge bg-primary me-1 mb-1">
                                                        {{ $departments[$assignment['department_id']]->name }}
                                                    </span>
                                                    @php $deptCount++; @endphp
                                                    @if($deptCount % 3 == 0)
                                                        <br>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
                                    
                                    @if(Auth::user()->type != 'employee')
                                        <td>
                                            @if(is_array($project->assigned_data))
                                                @php $empCount = 0; @endphp
                                                @foreach($project->assigned_data as $assignment)
                                                    @foreach($assignment['employee_ids'] ?? [] as $employeeId)
                                                        @if(isset($employees[$employeeId]))
                                                            <span class="badge bg-success me-1 mb-1">
                                                                {{ $employees[$employeeId]->user->name ?? __('Unknown') }}
                                                            </span>
                                                            @php $empCount++; @endphp
                                                            @if($empCount % 3 == 0)
                                                                <br>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            @endif
                                        </td>
                                    @endif
                                    
                                    <td>
                                        @if($project->project_startdate)
                                            {{ \Carbon\Carbon::parse($project->project_startdate)->format('d M Y') }}
                                        @else
                                            {{ __('Not set') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->project_enddate)
                                            {{ \Carbon\Carbon::parse($project->project_enddate)->format('d M Y') }}
                                        @else
                                            {{ __('Not set') }}
                                        @endif
                                    </td>                                    
                                    @if (Auth::user()->type != 'hr' && (Gate::check('Edit Meeting') || Gate::check('Delete Meeting')))
                                        <td class="Action" style="white-space: nowrap;">
                                            <!-- Edit Button -->
                                            @can('Edit Meeting')
                                                <a href="#" 
                                                class="btn btn-sm btn-icon-only bg-info ms-2" 
                                                data-url="{{ route('projects.edit', $project->id) }}" 
                                                data-ajax-popup="true" 
                                                data-size="lg" 
                                                data-bs-toggle="tooltip" 
                                                data-title="{{ __('Edit Project') }}" 
                                                title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            @endcan

                                            <!-- Delete Button -->
                                            @can('Delete Meeting')
                                                {!! Form::open([
                                                    'method' => 'DELETE', 
                                                    'route' => ['projects.destroy', $project->id], 
                                                    'style' => 'display:inline'
                                                ]) !!}
                                                <a href="#" 
                                                class="btn btn-sm btn-icon-only bg-danger ms-2 bs-pass-para" 
                                                data-bs-toggle="tooltip" 
                                                title="{{ __('Delete Project') }}" 
                                                onclick="event.preventDefault(); document.getElementById('delete-form-{{ $project->id }}').submit();">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->type != 'employee' ? (Gate::check('Edit Meeting') || Gate::check('Delete Meeting') ? '6' : '5') : (Gate::check('Edit Meeting') || Gate::check('Delete Meeting') ? '5' : '4') }}" class="text-center">{{ __('No projects found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Delete functionality with proper error handling
        $(document).on('click', '.bs-pass-para', function(e) {
            e.preventDefault();
            const button = $(this);
            const form = button.closest('form');
            const projectId = form.attr('action').split('/').pop();
            const row = form.closest('tr');

            if (!confirm('Are you sure you want to delete this project?')) {
                return;
            }

            // Show loading state
            button.prop('disabled', true);
            button.html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: form.attr('action'),
                type: 'POST', // Laravel needs POST for DELETE method
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the row with animation
                        row.fadeOut(400, function() {
                            $(this).remove();
                            
                            // Show success message
                            showToast('success', response.message);
                            
                            // Handle empty table state
                            if ($('#pc-dt-simple tbody tr').length === 0) {
                                $('#pc-dt-simple tbody').append(
                                    '<tr><td colspan="6" class="text-center">No projects found</td></tr>'
                                );
                            }
                        });
                    } else {
                        showToast('error', response.message);
                        button.prop('disabled', false).html('<i class="ti ti-trash"></i>');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Server error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.status === 403) {
                        errorMsg = 'Unauthorized action';
                    }
                    
                    showToast('error', errorMsg);
                    button.prop('disabled', false).html('<i class="ti ti-trash"></i>');
                    
                    // For debugging
                    console.error('Delete error:', xhr.responseJSON || xhr.statusText);
                }
            });
        });

        // Toast notification function
        function showToast(type, message) {
            const toast = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            
            $('.toast-container').html(toast);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
    });
</script>
@endsection