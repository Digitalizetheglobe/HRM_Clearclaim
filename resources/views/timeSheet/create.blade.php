<!-- Updated create timesheet form -->
{{ Form::open(['route' => ['timesheet.store'], 'id' => 'timesheet-form', 'class' => 'needs-validation', 'novalidate' => 'novalidate']) }}
@csrf

<div class="modal-body px-50">
    <div class="row">
        @if (\Auth::user()->type != 'employee')
            <div class="form-group col-md-12">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                {!! Form::select('employee_id', $employees, null, [
                    'class' => 'form-control select2',
                    'id' => 'choices-multiple',
                    'required' => 'required',
                ]) !!}
            </div>
        @endif

        <div class="form-group col-md-6">
            {{ Form::label('project_id', __('Project Name'), ['class' => 'col-form-label']) }}
            <select name="project_id" id="projectDropdown" class="form-control select2" required>
                <option value="">Select Project</option>
                @foreach($projects as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>


        <div class="form-group col-md-6 mt-3">
            <label for="assignEmployeeDropdown" class="form-label">Assign To</label>
            <select name="assigned_to" id="assignEmployeeDropdown" class="form-control">
                <option value="">Select Employee to Assign (optional)</option>
            </select>
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}
            {{ Form::text('date', '', ['class' => 'form-control d_week current_date', 'autocomplete' => 'off', 'required' => 'required', 'placeholder' => 'Select date']) }}
        </div>

        <!-- Client Information Fields -->
        <div class="form-group col-md-4">
            {{ Form::label('full_name', __('Client Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('full_name', '', [
                'class' => 'form-control', 
                'required' => 'required', 
                'placeholder' => 'Enter full name',
            ]) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('mobile_no', __('Mobile Number'), ['class' => 'col-form-label']) }}
            {{ Form::text('mobile_no', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter mobile number']) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('email_id', __('Email ID'), ['class' => 'col-form-label']) }}
            {{ Form::email('email_id', '', ['class' => 'form-control', 'placeholder' => 'Enter email ID']) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('address', __('Address'), ['class' => 'col-form-label']) }}
            {{ Form::text('address', '', ['class' => 'form-control', 'placeholder' => 'Enter address']) }}
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
            ], '', ['class' => 'form-control select2', 'id' => 'recommended_by']) }}
        </div>

        <!-- Dynamic Fields for Client Source -->
        <div id="cpBox" class="form-group col-md-4" style="display: none;">
            {{ Form::label('cp_data', __('CP Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('cp_data', '', ['class' => 'form-control', 'placeholder' => 'Enter CP Name']) }}
        </div>

        <div id="referenceBox" class="form-group col-md-4" style="display: none;">
            {{ Form::label('refrence_data', __('Reference Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('refrence_data', '', ['class' => 'form-control', 'placeholder' => 'Enter Reference Name']) }}
        </div>

        <div id="otherBox" class="form-group col-md-4" style="display: none;">
            {{ Form::label('other_data', __('Other Data'), ['class' => 'col-form-label']) }}
            {{ Form::text('other_data', '', ['class' => 'form-control', 'placeholder' => 'Enter Other Data']) }}
        </div>


        <!-- Presale Employee Field -->
        <div class="form-group col-md-4">
            {{ Form::label('presale_employee_id', __('Presale Employee'), ['class' => 'col-form-label']) }}
            {!! Form::select('presale_employee_id', $allEmployees, null, [
                'class' => 'form-control select2',
                'placeholder' => 'Select Presale Employee'
            ]) !!}
        </div>

        <!-- Other Fields -->
        <div class="form-group col-md-4">
            {{ Form::label('primary_reason', __('Primary Reason'), ['class' => 'col-form-label']) }}
            {{ Form::select('primary_reason', ['Investment' => 'Investment', 'Construction' => 'Construction'], '', ['class' => 'form-control select2']) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('square_feet_range', __('Area Requirement'), ['class' => 'col-form-label']) }}
            {{ Form::text('square_feet_range', '', ['class' => 'form-control', 'placeholder' => 'Enter Area Requirement']) }}
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
            ], '', ['class' => 'form-control select2']) }}
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
            ], '', ['class' => 'form-control select2', 'id' => 'client_status']) }}
        </div>

       

        <div class="form-group col-md-12">
            {{ Form::label('executive_remark', __('Executive Remark'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('executive_remark', '', ['class' => 'form-control', 'placeholder' => 'Enter executive remark', 'rows' => 3]) }}
        </div>

        <div class="form-group col-md-12">
            <a href="#" id="addFeedback" class="btn btn-sm btn-primary">+ Add Feedback</a>
            <div id="feedbackContainer">
                <!-- Feedback fields will be added here dynamically -->
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
$(document).ready(function() {
    // Initialize date
    var now = new Date();
    var month = (now.getMonth() + 1).toString().padStart(2, '0');
    var day = now.getDate().toString().padStart(2, '0');
    var today = now.getFullYear() + '-' + month + '-' + day;
    $('.current_date').val(today);

    // Handle recommended_by changes
    $('#recommended_by').on('change', function() {
        const value = $(this).val();
        
        // Hide all boxes first
        $('#cpBox, #referenceBox, #otherBox').hide();
        
        // Show the appropriate box
        if (value === 'CP') {
            $('#cpBox').show();
        } else if (value === 'Refrence') {
            $('#referenceBox').show();
        } else if (value === 'Others') {
            $('#otherBox').show();
        }
    });

    

    let feedbackCounter = 1;
    
    $('#addFeedback').click(function(e) {
        e.preventDefault();
        
        let feedbackId = 'feedback_' + feedbackCounter;
        
        let feedbackField = `
            <div class="feedback-group mb-3 p-3 border rounded" id="${feedbackId}">
                <h5>Customer Feedback ${feedbackCounter}</h5>
                <div class="row">
                    <div class="form-group col-md-8">
                        <label class="col-form-label">Feedback Details</label>
                        <textarea name="feedback_information[]" class="form-control" placeholder="Enter feedback details" rows="2"></textarea>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="col-form-label">Follow-up Date</label>
                        <input type="date" name="feedback_followup_date[]" class="form-control">
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm removeFeedback" data-target="${feedbackId}">Remove</button>
            </div>
        `;

        $('#feedbackContainer').append(feedbackField);
        feedbackCounter++;

        // In your create.blade.php script
        $('#feedbackContainer').on('change', 'input[type="date"]', function() {
            const dateValue = $(this).val();
            if (dateValue) {
                const selectedDate = new Date(dateValue);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    alert('Follow-up date cannot be in the past');
                    $(this).val('');
                }
            }
        });
    });

    // Remove feedback field
    $('#feedbackContainer').on('click', '.removeFeedback', function() {
        const targetId = $(this).data('target');
        $('#' + targetId).remove();
        // After removal, we should renumber the remaining feedbacks
        renumberFeedbacks();
    });

    // Function to renumber feedbacks after one is removed
    function renumberFeedbacks() {
        feedbackCounter = 1;
        $('#feedbackContainer .feedback-group').each(function(index) {
            $(this).find('h5').text('Customer Feedback ' + feedbackCounter);
            feedbackCounter++;
        });
        // Reset counter to the next available number
        feedbackCounter = $('#feedbackContainer .feedback-group').length + 1;
    }





    // Handle employee selection change
$('#choices-multiple').on('change', function() {
    const userId = $(this).val();
    
    if (!userId) {
        // If no employee selected, show all projects
        refreshProjectDropdown(@json($projects));
        return;
    }

    // Show loading state
    $('#projectDropdown').html('<option value="">Loading projects...</option>');

    // Fetch projects assigned to this employee
    $.ajax({
        url: '/hrm/get-employee-projects/' + userId,
        type: 'GET',
        success: function(response) {
            if (response.projects && response.projects.length > 0) {
                const projects = {};
                response.projects.forEach(project => {
                    projects[project.id] = project.project_name;
                });
                refreshProjectDropdown(projects);
            } else {
                $('#projectDropdown').html('<option value="">No projects assigned to this employee</option>');
            }
        },
        error: function(xhr) {
            console.error('Error fetching projects:', xhr.responseText);
            $('#projectDropdown').html('<option value="">Error loading projects</option>');
            // Fallback to showing all projects
            refreshProjectDropdown(@json($projects));
        }
    });
});

// Helper function to refresh project dropdown
function refreshProjectDropdown(projects) {
    let options = '<option value="">Select Project</option>';
    
    if (typeof projects === 'object' && !Array.isArray(projects)) {
        // Handle when projects is an object (key-value pairs)
        for (const [id, name] of Object.entries(projects)) {
            options += `<option value="${id}">${name}</option>`;
        }
    } else if (Array.isArray(projects)) {
        // Handle when projects is an array of objects
        projects.forEach(project => {
            options += `<option value="${project.id}">${project.project_name}</option>`;
        });
    }
    
    $('#projectDropdown').html(options);
}
});
</script>

<script>
$(document).ready(function() {
    // Initialize Select2 if you're using it
    if ($.fn.select2) {
        $('#assignEmployeeDropdown').select2({
            placeholder: "Select Employee to Assign",
            width: '100%'
        });
    }

    // Project dropdown change handler
    $('#projectDropdown').on('change', function() {
        const projectId = $(this).val();
        const currentUserId = "{{ Auth::id() }}"; // Get current user ID
        
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
            url: "/hrm/get-project-employees/" + projectId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                const $dropdown = $('#assignEmployeeDropdown');
                $dropdown.empty();
                
                // Add default option
                $dropdown.append(
                    $('<option>', {
                        value: '',
                        text: 'Select Employee to Assign (optional)'
                    })
                );
                
                // Add employees, excluding the current user
                if (response.employees && response.employees.length) {
                    $.each(response.employees, function(i, employee) {
                        if (employee.id.toString() !== currentUserId.toString()) {
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
                            text: 'No other employees assigned',
                            disabled: true
                        })
                    );
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
});
</script>