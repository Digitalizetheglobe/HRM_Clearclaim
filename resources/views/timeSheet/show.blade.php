@php
    $isSiteHead = false;
    if ($timeSheet->project && !empty($timeSheet->project->site_heads)) {
        $currentEmployeeId = Auth::user()->employee->id ?? null;
        $isSiteHead = $currentEmployeeId && in_array((string)$currentEmployeeId, $timeSheet->project->site_heads, true);
    }
@endphp

@if($isSiteHead)
    <div class="alert alert-info">
        <i class="ti ti-info-circle"></i> You are viewing this enquiry as a Site Head (read-only access)
    </div>
@endif
<div class="modal-body">
    <div class="card border-0 shadow-none">
        <div class="card-body">
            @if(!$timeSheet->isVisibleTo(Auth::user()->id) && $timeSheet->employee_id != Auth::user()->id)

            <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Employee Name ') }}</label>
                        <p>{{ $timeSheet->assignedEmployee->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Date') }}</label>
                        <p>{{ \Auth::user()->dateFormat($timeSheet->date) }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Project') }}</label>
                        <p>{{ $timeSheet->project->project_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Client Name') }}</label>
                        <p>{{ $timeSheet->full_name }}</p>
                    </div>
                </div>



                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Mobile No') }}</label>
                        <p>{{ $timeSheet->mobile_no }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Email') }}</label>
                        <p>{{ $timeSheet->email_id ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">{{ __('Address') }}</label>
                        <p>{{ $timeSheet->address ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Client Source') }}</label>
                        <p>{{ $timeSheet->recommended_by ?? 'N/A' }}</p>
                    </div>
                    @if($timeSheet->recommended_by == 'CP')
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('CP Name') }}</label>
                        <p>{{ $timeSheet->cp_data ?? 'N/A' }}</p>
                    </div>
                    @endif
                    @if($timeSheet->recommended_by == 'Refrence')
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Refrence Name') }}</label>
                        <p>{{ $timeSheet->refrence_data ?? 'N/A' }}</p>
                    </div>
                    @endif
                    @if($timeSheet->recommended_by == 'Others')
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Other Data') }}</label>
                        <p>{{ $timeSheet->other_data ?? 'N/A' }}</p>
                    </div>
                    @endif
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Presale Employee') }}</label>
                        <p>{{ $timeSheet->presaleEmployee->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Primary Reason') }}</label>
                        <p>{{ $timeSheet->primary_reason ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Area Requirement') }}</label>
                        <p>{{ $timeSheet->square_feet_range ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Price Range') }}</label>
                        <p>{{ $timeSheet->price_range ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Client Status') }}</label>
                        <p>{{ $timeSheet->client_status ?? 'N/A' }}</p>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">{{ __('Executive Remark') }}</label>
                        <p>{{ $timeSheet->executive_remark ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Feedback Section -->
                @if(is_array($feedbacks) && count($feedbacks) > 0)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ __('Feedbacks') }}</label>
                            @foreach($feedbacks as $feedback)
                                @if(is_array($feedback) && isset($feedback['description']))
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <p>{{ $feedback['description'] ?? 'N/A' }}</p>
                                        <small class="text-muted">
                                            @if(isset($feedback['followup_date']))
                                            Follow-up: {{ $feedback['followup_date'] ? \Auth::user()->dateFormat($feedback['followup_date']) : 'N/A' }}<br>
                                            @endif
                                            Added by: {{ $feedback['added_by'] ?? 'N/A' }} on 
                                            {{ $feedback['created_at'] ?? 'N/A' }}
                                            @if(isset($feedback['updated_by']))
                                            <br>Last updated by: {{ $feedback['updated_by'] }} on {{ $feedback['updated_at'] }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ __('Feedbacks') }}</label>
                            <p>No feedback available</p>
                        </div>
                    </div>
                @endif

            @else
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Employee Name ') }}</label>
                        <p>{{ $timeSheet->assignedEmployee->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Date') }}</label>
                        <p>{{ \Auth::user()->dateFormat($timeSheet->date) }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Project') }}</label>
                        <p>{{ $timeSheet->project->project_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Client Name') }}</label>
                        <p>{{ $timeSheet->full_name }}</p>
                    </div>
                </div>



                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Mobile No') }}</label>
                        <p>{{ $timeSheet->mobile_no }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Email') }}</label>
                        <p>{{ $timeSheet->email_id ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">{{ __('Address') }}</label>
                        <p>{{ $timeSheet->address ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Client Source') }}</label>
                        <p>{{ $timeSheet->recommended_by ?? 'N/A' }}</p>
                    </div>
                    @if($timeSheet->recommended_by == 'CP')
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('CP Name') }}</label>
                        <p>{{ $timeSheet->cp_data ?? 'N/A' }}</p>
                    </div>
                    @endif
                    @if($timeSheet->recommended_by == 'Refrence')
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Refrence Name') }}</label>
                        <p>{{ $timeSheet->refrence_data ?? 'N/A' }}</p>
                    </div>
                    @endif
                    @if($timeSheet->recommended_by == 'Others')
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Other Data') }}</label>
                        <p>{{ $timeSheet->other_data ?? 'N/A' }}</p>
                    </div>
                    @endif
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Presale Employee') }}</label>
                        <p>{{ $timeSheet->presaleEmployee->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Primary Reason') }}</label>
                        <p>{{ $timeSheet->primary_reason ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Area Requirement') }}</label>
                        <p>{{ $timeSheet->square_feet_range ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Price Range') }}</label>
                        <p>{{ $timeSheet->price_range ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Client Status') }}</label>
                        <p>{{ $timeSheet->client_status ?? 'N/A' }}</p>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">{{ __('Executive Remark') }}</label>
                        <p>{{ $timeSheet->executive_remark ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Feedback Section -->
                @if(is_array($feedbacks) && count($feedbacks) > 0)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ __('Feedbacks') }}</label>
                            @foreach($feedbacks as $feedback)
                                @if(is_array($feedback) && isset($feedback['description']))
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <p>{{ $feedback['description'] ?? 'N/A' }}</p>
                                        <small class="text-muted">
                                            @if(isset($feedback['followup_date']))
                                            Follow-up: {{ $feedback['followup_date'] ? \Auth::user()->dateFormat($feedback['followup_date']) : 'N/A' }}<br>
                                            @endif
                                            Added by: {{ $feedback['added_by'] ?? 'N/A' }} on 
                                            {{ $feedback['created_at'] ?? 'N/A' }}
                                            @if(isset($feedback['updated_by']))
                                            <br>Last updated by: {{ $feedback['updated_by'] }} on {{ $feedback['updated_at'] }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ __('Feedbacks') }}</label>
                            <p>No feedback available</p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.view-btn', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var title = $(this).data('title');
        var size = $(this).data('size') || 'lg'; // Default to large if not specified
        
        // Set modal title and size
        $('#commonModal .modal-title').text(title);
        $('#commonModal .modal-dialog').removeClass('modal-lg modal-xl').addClass('modal-' + size);
        
        // Show loading spinner
        $('#commonModal .modal-body').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading enquiry details...</p>
            </div>
        `);
        
        // Show modal immediately with loading state
        $('#commonModal').modal('show');
        
        // Load modal content via AJAX
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#commonModal .modal-body').html(response);
            },
            error: function() {
                $('#commonModal .modal-body').html(`
                    <div class="alert alert-danger">
                        Failed to load enquiry details. Please try again.
                    </div>
                `);
            }
        });
    });

    function unlockTimesheet(element) {
        if (confirm('Are you sure you want to unlock this timesheet entry? You will be able to view it.')) {
            $.ajax({
                url: $(element).attr('href'),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        show_toastr('Success', response.message, 'success');
                        // Reload the modal content
                        $('#commonModal .modal-body').load(window.location.href + ' #commonModal .modal-body > *');
                    }
                },
                error: function(xhr) {
                    show_toastr('Error', xhr.responseJSON.message || 'Error unlocking entry', 'error');
                }
            });
        }
    }
</script>

<script>
    // Function to load assignable employees based on project
function loadAssignableEmployees(projectId) {
    if (!projectId) {
        $('#assignEmployeeDropdown').html('<option value="">Select Project First</option>');
        return;
    }

    $.ajax({
        url: '/get-project-employees/' + projectId,
        type: 'GET',
        success: function(response) {
            let options = '<option value="">Select Employee to Assign</option>';
            
            response.employees.forEach(employee => {
                // Exclude current user from assignable employees
                if (employee.id != {{ Auth::id() }}) {
                    options += `<option value="${employee.id}">${employee.name}</option>`;
                }
            });
            
            $('#assignEmployeeDropdown').html(options);
        },
        error: function(xhr) {
            console.error('Error loading employees:', xhr.responseText);
            $('#assignEmployeeDropdown').html('<option value="">Error loading employees</option>');
        }
    });
}

// Call this when project changes
$('#projectDropdown').on('change', function() {
    loadAssignableEmployees($(this).val());
});

// Initialize if editing
@if(isset($timeSheet) && $timeSheet->project_id)
loadAssignableEmployees({{ $timeSheet->project_id }});
@endif    
</script>