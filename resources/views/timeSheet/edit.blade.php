@php
    $isSiteHead = false;
    if ($timeSheet->project && !empty($timeSheet->project->site_heads)) {
        $currentEmployeeId = Auth::user()->employee->id ?? null;
        $isSiteHead = $currentEmployeeId && in_array((string)$currentEmployeeId, $timeSheet->project->site_heads, true);
    }
@endphp

@if($isSiteHead)
    <div class="alert alert-danger">
        <i class="ti ti-alert-circle"></i> You cannot edit this enquiry as you are a Site Head for this project
    </div>
@else
    {{-- Rest of your edit form --}}
@endif

{{ Form::model($timeSheet, ['route' => ['timesheet.update', $timeSheet->id], 'method' => 'PUT', 'id' => 'timesheet-form', 'class' => 'needs-validation', 'novalidate' => 'novalidate']) }}
@csrf

<div class="modal-body px-50">
    <div class="row">
        @if (\Auth::user()->type != 'employee')
            <div class="form-group col-md-12">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                {!! Form::select('employee_id', $employees, null, [
                    'class' => 'form-control select2',
                    'id' => 'choices-multiple',
                ]) !!}
            </div>
        @endif

        <div class="form-group col-md-6">
            {{ Form::label('project_id', __('Project Name'), ['class' => 'col-form-label']) }}
            <select name="project_id" id="projectDropdown" class="form-control select2" required>
                <option value="">Select Project</option>
                @foreach($projects as $id => $name)
                    <option value="{{ $id }}" {{ $timeSheet->project_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            @error('project_id')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group col-md-6 mt-3">
            <label for="assignEmployeeDropdown" class="form-label">Assign To</label>
            <select name="assigned_to" id="assignEmployeeDropdown" class="form-control">
                <option value="">Select Employee to Assign (optional)</option>
                @if($timeSheet->assigned_to)
                    @php
                        $assignedEmployee = \App\Models\Employee::where('user_id', $timeSheet->assigned_to)->first();
                    @endphp
                    <option value="{{ $timeSheet->assigned_to }}" selected>
                        {{ $assignedEmployee ? $assignedEmployee->name : 'Unknown Employee' }}
                    </option>
                @endif
            </select>
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}
            {{ Form::text('date', $timeSheet->date, ['class' => 'form-control d_week current_date', 'autocomplete' => 'off', 'required' => 'required', 'placeholder' => 'Select date']) }}
        </div>

        <!-- Client Information Fields -->
        <div class="form-group col-md-4">
            {{ Form::label('full_name', __('Client Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('full_name', $timeSheet->full_name, [
                'class' => 'form-control', 
                'required' => 'required', 
                'placeholder' => 'Enter full name',
            ]) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('mobile_no', __('Mobile Number'), ['class' => 'col-form-label']) }}
            {{ Form::text('mobile_no', $timeSheet->mobile_no, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter mobile number']) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('email_id', __('Email ID'), ['class' => 'col-form-label']) }}
            {{ Form::email('email_id', $timeSheet->email_id, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter email ID']) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('address', __('Address'), ['class' => 'col-form-label']) }}
            {{ Form::text('address', $timeSheet->address, ['class' => 'form-control', 'placeholder' => 'Enter address']) }}
        </div>

        <!-- Client Source with Dynamic Fields -->
        <div class="form-group col-md-4">
            {{ Form::label('recommended_by', __('Client Source'), ['class' => 'col-form-label']) }}
            {{ Form::select('recommended_by', [
                '' => 'Select Client Source',
                'CP' => 'CP',
                'Digital' => 'Digital',
                'Hoarding' => 'Hoarding',
                'WayBoard' => 'WayBoard',
                'Walking' => 'Walking',
                'LeafLet' => 'LeafLet',
                'Expo' => 'Expo',
                'Refrence' => 'Refrence',
                'Others' => 'Others'
            ], $timeSheet->recommended_by, ['class' => 'form-control select2', 'id' => 'recommended_by']) }}
        </div>

        <!-- Dynamic Fields for Client Source -->
        <div id="cpBox" class="form-group col-md-4" style="display: {{ $timeSheet->recommended_by == 'CP' ? 'block' : 'none' }};">
            {{ Form::label('cp_data', __('CP Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('cp_data', $timeSheet->cp_data, ['class' => 'form-control', 'placeholder' => 'Enter CP Name']) }}
        </div>

        <div id="referenceBox" class="form-group col-md-4" style="display: {{ $timeSheet->recommended_by == 'Refrence' ? 'block' : 'none' }};">
            {{ Form::label('refrence_data', __('Reference Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('refrence_data', $timeSheet->refrence_data, ['class' => 'form-control', 'placeholder' => 'Enter Reference Name']) }}
        </div>

        <div id="otherBox" class="form-group col-md-4" style="display: {{ $timeSheet->recommended_by == 'Others' ? 'block' : 'none' }};">
            {{ Form::label('other_data', __('Other Data'), ['class' => 'col-form-label']) }}
            {{ Form::text('other_data', $timeSheet->other_data, ['class' => 'form-control', 'placeholder' => 'Enter Other Data']) }}
        </div>

        <!-- In your edit form -->
        <div class="form-group col-md-4">
            {{ Form::label('presale_employee_id', __('Presale Employee'), ['class' => 'col-form-label']) }}
            {!! Form::select('presale_employee_id', $allEmployees, $timeSheet->presale_employee_id, [
                'class' => 'form-control select2',
                'placeholder' => 'Select Presale Employee'
            ]) !!}
        </div>

        <!-- Other Fields -->
        <div class="form-group col-md-4">
            {{ Form::label('primary_reason', __('Primary Reason'), ['class' => 'col-form-label']) }}
            {{ Form::select('primary_reason', ['Investment' => 'Investment', 'Construction' => 'Construction'], $timeSheet->primary_reason, ['class' => 'form-control select2']) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('square_feet_range', __('Area Requirement'), ['class' => 'col-form-label']) }}
            {{ Form::text('square_feet_range', $timeSheet->square_feet_range, ['class' => 'form-control', 'placeholder' => 'Enter Area Requirement']) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('price_range', __('Price Range'), ['class' => 'col-form-label']) }}
            {{ Form::select('price_range', [
                '' => 'Select Price Range',
                '10 To 15 lakh' => '10 To 15 lakh',
                '15 To 20 lakh' => '15 To 20 lakh',
                '20 To 30 lakh' => '20 To 30 lakh',
                '30 To 40 lakh' => '30 To 40 lakh',
                '40 To 50 lakh' => '40 To 50 lakh',
                '50 Lakh Above' => '50 Lakh Above'
            ], $timeSheet->price_range, ['class' => 'form-control select2']) }}
        </div>

        <!-- Client Status with Booked Option -->
        <div class="form-group col-md-4">
            {{ Form::label('client_status', __('Client Status'), ['class' => 'col-form-label']) }}
            {{ Form::select('client_status', [
                '' => 'Select Client Status',
                'Intrested' => 'Intrested', 
                'Not-Intrested' => 'Not-Intrested', 
                'Call-Back' => 'Call-Back', 
                'Hold' => 'Hold',
                'Booked' => 'Booked'
            ], $timeSheet->client_status, ['class' => 'form-control select2', 'id' => 'client_status']) }}
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('executive_remark', __('Executive Remark'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('executive_remark', $timeSheet->executive_remark, ['class' => 'form-control', 'placeholder' => 'Enter executive remark', 'rows' => 3]) }}
        </div>

        <div class="form-group col-md-12">
            <div id="feedbackSection">
                <a href="#" id="addFeedback" class="btn btn-sm btn-primary">+ Add Feedback</a>
                <div id="feedbackContainer">
                    @if(!empty($feedbacks))
                        @foreach($feedbacks as $index => $feedback)
                        <div class="feedback-group mb-3 p-3 border rounded" id="feedback_{{ $index }}">
                            <h5>Customer Feedback {{ $index + 1 }}</h5>
                            <div class="row">
                                <div class="form-group col-md-8">
                                    <label class="col-form-label">Feedback Details</label>
                                    <textarea name="feedback_information[{{ $index }}]" class="form-control" placeholder="Enter feedback details" rows="2" {{ Auth::user()->type != 'employee' ? 'readonly' : '' }}>{{ $feedback['description'] ?? '' }}</textarea>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="col-form-label">Follow-up Date</label>
                                    <input type="date" name="feedback_followup_date[{{ $index }}]" class="form-control followup-date" value="{{ $feedback['followup_date'] ?? '' }}" {{ Auth::user()->type != 'employee' ? 'readonly' : '' }}>
                                </div>
                                <div class="form-group col-md-12">
                                    <small class="text-muted">
                                        Added by: {{ $feedback['added_by'] ?? 'N/A' }} on 
                                        {{ $feedback['created_at'] ?? 'N/A' }}
                                        @if(isset($feedback['updated_by']))
                                        <br>Last updated by: {{ $feedback['updated_by'] }} on {{ $feedback['updated_at'] }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            @if(Auth::user()->type == 'employee')
                                <button type="button" class="btn btn-danger btn-sm removeFeedback" data-target="feedback_{{ $index }}">Remove</button>
                            @endif
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <!-- <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button> -->
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // When editing, trigger the employee change event on load if employee is selected
    const initialEmployeeId = $('#choices-multiple').val();
    if (initialEmployeeId) {
        $('#choices-multiple').trigger('change');
    }
$(document).ready(function() {
    // Debugging: Log when document is ready
    console.log('Document ready - initializing timesheet form');
    
    try {
        // Initialize date picker
        if(typeof $.fn.datepicker === 'function') {
            $('.current_date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });
        } else {
            console.error('Datepicker plugin not loaded');
        }

        // Handle recommended_by changes
        $('#recommended_by').on('change', function() {
            const value = $(this).val();
            $('#cpBox, #referenceBox, #otherBox').hide();
            if (value === 'CP') $('#cpBox').show();
            else if (value === 'Refrence') $('#referenceBox').show();
            else if (value === 'Others') $('#otherBox').show();
        });

        // Initialize feedback counter
        let feedbackCounter = {{ !empty($feedbacks) ? count($feedbacks) + 1 : 1 }};
        console.log('Initial feedback counter:', feedbackCounter);

        // Add feedback button handler with robust error checking
        $(document).on('click', '#addFeedback', function(e) {
            e.preventDefault();
            console.log('Add Feedback button clicked');
            
            try {
                let feedbackId = 'feedback_new_' + Date.now(); // Use timestamp for unique ID
                
               let feedbackField = `
                    <div class="feedback-group mb-3 p-3 border rounded" id="${feedbackId}">
                        <h5>Customer Feedback ${feedbackCounter}</h5>
                        <div class="row">
                            <div class="form-group col-md-8">
                                <label class="col-form-label">Feedback Details</label>
                                <textarea name="feedback_information[]" class="form-control" placeholder="Enter feedback details" rows="2" required></textarea>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="col-form-label">Follow-up Date</label>
                                <input type="date" name="feedback_followup_date[]" class="form-control followup-date" required>
                            </div>
                            <div class="form-group col-md-12">
                                <small class="text-muted">
                                    Will be added by: {{ Auth::user()->employee->name ?? Auth::user()->name }} on save
                                </small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm removeFeedback" data-target="${feedbackId}">Remove</button>
                    </div>
                `;

                $('#feedbackContainer').append(feedbackField);
                console.log('Added new feedback field with ID:', feedbackId);
                feedbackCounter++;
            } catch (error) {
                console.error('Error adding feedback:', error);
                alert('Error adding feedback. Please check console for details.');
            }
        });

        // Remove feedback field with error handling
        $(document).on('click', '.removeFeedback', function() {
            try {
                const targetId = $(this).data('target');
                console.log('Attempting to remove feedback:', targetId);
                $('#' + targetId).remove();
                console.log('Successfully removed feedback:', targetId);
                renumberFeedbacks();
            } catch (error) {
                console.error('Error removing feedback:', error);
                alert('Error removing feedback. Please check console for details.');
            }
        });

        // Function to renumber feedbacks
        function renumberFeedbacks() {
            try {
                let counter = 1;
                $('#feedbackContainer .feedback-group').each(function(index) {
                    $(this).find('h5').text('Customer Feedback ' + counter);
                    counter++;
                });
                console.log('Renumbered feedbacks. Total now:', counter - 1);
            } catch (error) {
                console.error('Error renumbering feedbacks:', error);
            }
        }

        // Date validation for follow-up dates
        $(document).on('change', '.followup-date', function() {
            try {
                const dateValue = $(this).val();
                if (dateValue) {
                    const selectedDate = new Date(dateValue);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (selectedDate < today) {
                        console.warn('Invalid follow-up date selected (past date):', dateValue);
                        alert('Follow-up date cannot be in the past');
                        $(this).val('');
                    }
                }
            } catch (error) {
                console.error('Error validating follow-up date:', error);
            }
        });

    } catch (mainError) {
        console.error('Main timesheet form initialization error:', mainError);
        alert('Error initializing form. Please check console for details.');
    }
});

// Log all JavaScript errors to console
window.onerror = function(message, source, lineno, colno, error) {
    console.error('Global error:', message, 'at', source, 'line', lineno, 'column', colno, 'Error object:', error);
    return true; // Prevent default error handling
};
</script>


<script>
    // Project dropdown change handler for edit form
$('#projectDropdown').on('change', function() {
    const projectId = $(this).val();
    const currentEmployeeId = "{{ Auth::id() }}"; // Current logged in user
    
    if (!projectId) {
        $('#assignEmployeeDropdown').empty().append(
            $('<option>', {
                value: '',
                text: 'Select Project First'
            })
        );
        return;
    }

    // Show loading state
    $('#assignEmployeeDropdown').empty().append(
        $('<option>', {
            value: '',
            text: 'Loading employees...',
            disabled: true
        })
    );

    $.ajax({
        url: "/get-project-employees/" + projectId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            const $dropdown = $('#assignEmployeeDropdown');
            $dropdown.empty();
            
            // Add default option
            $dropdown.append(
                $('<option>', {
                    value: '',
                    text: 'Select Employee to Assign'
                })
            );
            
            // Add employees, excluding the current employee
            if (response.employees && response.employees.length) {
                $.each(response.employees, function(i, employee) {
                    if (employee.id.toString() !== currentEmployeeId.toString()) {
                        $dropdown.append(
                            $('<option>', {
                                value: employee.id,
                                text: employee.name
                            })
                        );
                    }
                });
            } else {
                $dropdown.append(
                    $('<option>', {
                        value: '',
                        text: 'No employees assigned',
                        disabled: true
                    })
                );
            }
            
            // Select the currently assigned employee if it exists
            const currentAssigned = "{{ $timeSheet->assigned_to }}";
            if (currentAssigned) {
                $dropdown.val(currentAssigned);
            }
            
            // Refresh Select2 if used
            if ($.fn.select2) {
                $dropdown.select2('destroy').select2({
                    placeholder: "Select Employee to Assign",
                    width: '100%'
                });
            }
        },
        error: function() {
            $('#assignEmployeeDropdown').empty().append(
                $('<option>', {
                    value: '',
                    text: 'Error loading employees',
                    disabled: true
                })
            );
        }
    });
});

// Trigger the change event on page load if project is already selected
$(document).ready(function() {
    if ($('#projectDropdown').val()) {
        $('#projectDropdown').trigger('change');
    }
});
</script>