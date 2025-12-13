@php
    $setting = App\Models\Utility::settings();
@endphp

{{ Form::open(['route' => ['projects.store'], 'id' => 'projectForm', 'class' => 'needs-validation', 'novalidate' => 'novalidate']) }}
@csrf

<div class="modal-body">
    <div class="row">

        <!-- Project Type Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                <label for="project_type" class="form-label">{{ __('Project Type') }}</label>
                <div class="form-icon-user">
                    <select class="form-control" name="project_type" id="project_type" required>
                        <option value="">{{ __('Select Project Type') }}</option>
                        <option value="1">{{ __('Residential Project') }}</option>
                        <option value="2">{{ __('Commercial Project') }}</option>
                        <option value="3">{{ __('Plotting Project') }}</option>
                    </select>
                </div>
                <div class="invalid-feedback project_type-error" style="display: none;"></div>
            </div>
        </div>
        <!-- Project Name Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('project_name', __('Project Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('project_name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Project Name')]) }}
                </div>
                <div class="invalid-feedback project_name-error" style="display: none;"></div>
            </div>
        </div>

        <!-- Site Heads Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                <label for="site_heads" class="form-label">{{ __('Site Heads') }}</label>
                <select class="form-control select2" name="site_heads[]" id="site_heads" multiple>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">
                            {{ $employee->user->name ?? $employee->name }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback site_heads-error" style="display: none;"></div>
            </div>
        </div>

        <!-- Location Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('location', __('Location'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('location', null, ['class' => 'form-control', 'placeholder' => __('Enter Location')]) }}
                </div>
            </div>
        </div>

    <!-- Project Start Date Field -->
    <div class="col-lg-6 col-md-6 col-sm-6">
        <div class="form-group">
            {{ Form::label('project_startdate', __('Project Start Date'), ['class' => 'form-label']) }}
            <div class="form-icon-user">
                <input type="date" class="form-control" name="project_startdate" id="project_startdate" autocomplete="off">
            </div>
        </div>
    </div>

    <!-- Project End Date Field -->
    <div class="col-lg-6 col-md-6 col-sm-6">
        <div class="form-group">
            {{ Form::label('project_enddate', __('Project End Date'), ['class' => 'form-label']) }}
            <div class="form-icon-user">
                <input type="date" class="form-control" name="project_enddate" id="project_enddate" autocomplete="off">
            </div>
        </div>
    </div>

    <!-- Branch Field -->
    <div class="col-lg-6 col-md-6 col-sm-6">
        <div class="form-group">
            <label for="branch_id" class="form-label">{{ __('Branch') }}</label>
            <div class="form-icon-user">
                <select class="form-control" name="branch_id" id="branch_id" required>
                    <option value="">{{ __('Select Branch') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="invalid-feedback branch_id-error" style="display: none;"></div>
        </div>
    </div>

    <!-- Department Field (initially disabled) -->
    <div class="col-lg-6 col-md-6 col-sm-6">
        <div class="form-group">
            {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
            <div class="form-icon-user">
                <select class="form-control" name="department_id" id="department_id" required disabled>
                    <option value="">{{ __('Select Department') }}</option>
                </select>
            </div>
            <div class="invalid-feedback department_id-error" style="display: none;"></div>
        </div>
    </div>

        <!-- Employee Selection -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    <select class="form-control" id="employee_id">
                        <option value="">{{ __('Select Employee') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Hidden field for assigned_data -->
        <input type="hidden" name="assigned_data" id="assignedData">

        <div class="col-md-12 mt-3">
            <div class="form-group">
                <label class="form-label">{{ __('Selected Assignments') }}</label>
                <div id="selectedAssignmentsBox" class="p-2 border rounded bg-light" style="min-height: 100px;">
                    <div class="text-muted">{{ __('No assignments selected.') }}</div>
                </div>
                <div class="invalid-feedback assigned_data-error" style="display: none;"></div>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
$(document).ready(function() {




    // Initialize data structure
    let assignments = []; // Array of { department_id, department_name, employees: [] }
    let allEmployees = {}; // Cache of employees by department: { deptId: [employee1, employee2] }


    // Branch change event
    $('#branch_id').change(function() {
        const branchId = $(this).val();
        const departmentSelect = $('#department_id');
        
        if (!branchId) {
            departmentSelect.html('<option value="">{{ __("Select Department") }}</option>').prop('disabled', true);
            return;
        }

        // Show loading state
        departmentSelect.html('<option value="">{{ __("Loading departments...") }}</option>').prop('disabled', true);

        $.ajax({
            url: '{{ route("get-departments-by-branch", "") }}/' + branchId,
            type: 'GET',
            success: function(data) {
                let options = '<option value="">{{ __("Select Department") }}</option>';
                
                data.forEach(department => {
                    options += `<option value="${department.id}">${department.name}</option>`;
                });
                
                departmentSelect.html(options).prop('disabled', false);
                
                // If editing, try to select the previously selected department
                @if(isset($project) && isset($project->assigned_data[0]['department_id']))
                    const prevDeptId = '{{ $project->assigned_data[0]["department_id"] }}';
                    if (prevDeptId) {
                        departmentSelect.val(prevDeptId).trigger('change');
                    }
                @endif
            },
            error: function(xhr) {
                console.error('Error loading departments:', xhr);
                departmentSelect.html('<option value="">{{ __("Error loading departments") }}</option>').prop('disabled', true);
            }
        });
    });


    $('#department_id').change(function() {
        const departmentId = $(this).val();
        if (!departmentId) return;

        // Check if we already have this department's employees cached
        if (allEmployees[departmentId]) {
            updateEmployeeDropdown(departmentId);
            return;
        }

        $.ajax({
            url: '{{ route("get-employees-by-department", "") }}/' + departmentId,
            type: 'GET',
            success: function(data) {
                // Cache the employees for this department
                allEmployees[departmentId] = data;
                updateEmployeeDropdown(departmentId);
            },
            error: function(xhr) {
                console.error('Error loading employees:', xhr);
                $('#employee_id').html('<option value="">{{ __("Error loading employees") }}</option>');
            }
        });
    });

    // Update the updateEmployeeDropdown function
    function updateEmployeeDropdown(departmentId) {
        const currentEmployees = allEmployees[departmentId] || [];
        console.log('All employees for department', departmentId, ':', currentEmployees);
        
        const selectedInDepartment = assignments
            .filter(a => a.department_id == departmentId)
            .flatMap(a => a.employees.map(e => e.id));
        
        console.log('Already selected in department:', selectedInDepartment);

        let options = '<option value="">{{ __("Select Employee") }}</option>';
        let availableCount = 0;
        
        currentEmployees.forEach(employee => {
            if (!selectedInDepartment.includes(employee.id)) {
                options += `<option value="${employee.id}">${employee.name}</option>`;
                availableCount++;
            }
        });
        
        console.log('Available employees after filtering:', availableCount);
        $('#employee_id').html(options);
        
        if (availableCount === 0) {
            console.log('No employees available - all might be selected or excluded');
        }
    }

    // Add an event listener for site heads changes to refresh the employee dropdown


    // Add employee to assignment
    $('#employee_id').change(function() {
        const employeeId = $(this).val();
        const employeeName = $(this).find('option:selected').text();
        const departmentId = $('#department_id').val();
        const departmentName = $('#department_id option:selected').text();

        if (!employeeId || !departmentId) return;

        // Find or create department assignment
        let assignment = assignments.find(a => a.department_id == departmentId);
        if (!assignment) {
            assignment = {
                department_id: departmentId,
                department_name: departmentName,
                employees: []
            };
            assignments.push(assignment);
        }

        // Add employee if not already exists
        if (!assignment.employees.some(e => e.id == employeeId)) {
            assignment.employees.push({
                id: employeeId,
                name: employeeName
            });
            updateAssignmentsUI();
            updateEmployeeDropdown(departmentId); // Refresh dropdown
        }

        $(this).val('');
    });

    // Update the assignments UI and hidden field
    function updateAssignmentsUI() {
        const container = $('#selectedAssignmentsBox');
        container.empty();

        if (assignments.length === 0) {
            container.html('<div class="text-muted">{{ __("No assignments selected.") }}</div>');
            $('#assignedData').val(JSON.stringify([]));
            return;
        }

        assignments.forEach(assignment => {
            const deptDiv = $(`
                <div class="mb-3 assignment-group" data-dept-id="${assignment.department_id}">
                    <h6 class="mb-1">${assignment.department_name}</h6>
                    <div class="d-flex flex-wrap employees-container"></div>
                </div>
            `);

            const employeesContainer = deptDiv.find('.employees-container');
            
            if (assignment.employees.length === 0) {
                employeesContainer.append('<span class="text-muted small">{{ __("No employees selected") }}</span>');
            } else {
                assignment.employees.forEach(employee => {
                    employeesContainer.append(`
                        <span class="badge bg-primary me-1 mb-1 employee-badge" 
                              data-dept-id="${assignment.department_id}" 
                              data-emp-id="${employee.id}">
                            ${employee.name}
                            <i class="fas fa-times ms-1 remove-employee" style="cursor: pointer;"></i>
                        </span>
                    `);
                });
            }

            container.append(deptDiv);
        });

        // Update hidden field with the current assignments
        const formattedData = assignments.map(assignment => ({
            department_id: assignment.department_id,
            employee_ids: assignment.employees.map(emp => emp.id)
        }));
        
        $('#assignedData').val(JSON.stringify(formattedData));
    }

    // Remove employee from assignment
    $(document).on('click', '.remove-employee', function() {
        const badge = $(this).closest('.employee-badge');
        const departmentId = badge.data('dept-id');
        const employeeId = badge.data('emp-id');

        // Find the assignment
        const assignment = assignments.find(a => a.department_id == departmentId);
        if (assignment) {
            // Remove the employee
            assignment.employees = assignment.employees.filter(e => e.id != employeeId);
            
            // If no more employees, remove the entire assignment
            if (assignment.employees.length === 0) {
                assignments = assignments.filter(a => a.department_id != departmentId);
            }
            
            updateAssignmentsUI();
            updateEmployeeDropdown(departmentId); // Refresh dropdown
        }
    });

    // For edit view - pre-populate assignments if editing
    @if(isset($project) && $project->assigned_data)
        // First load all departments with their employees
        const departmentIds = {!! json_encode(collect($project->assigned_data)->pluck('department_id')->unique()) !!};
        
        // Fetch all needed employees in one query
        $.ajax({
            url: '{{ route("get-employees-by-departments") }}',
            type: 'POST',
            data: {
                department_ids: departmentIds,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                // Cache all employees by department
                data.forEach(dept => {
                    allEmployees[dept.department_id] = dept.employees;
                });
                
                // Now populate assignments
                assignments = {!! json_encode(
                    collect($project->assigned_data)->map(function($item) {
                        $department = Department::find($item['department_id']);
                        return [
                            'department_id' => $item['department_id'],
                            'department_name' => $department ? $department->name : 'Unknown',
                            'employees' => Employee::whereIn('id', $item['employee_ids'])
                                ->get()
                                ->map(function($emp) {
                                    return ['id' => $emp->id, 'name' => $emp->name];
                                })
                                ->toArray()
                        ];
                    })
                ) !!};
                
                updateAssignmentsUI();
                
                // Set the first department as selected
                if (assignments.length > 0) {
                    $('#department_id').val(assignments[0].department_id).trigger('change');
                }
            },
            error: function(xhr) {
                console.error('Error loading employees for edit:', xhr);
            }
        });
    @endif
});


// Initialize select2 for site heads
$('#site_heads').select2({
    placeholder: "Select Site Heads",
    allowClear: true
});

// When site heads change, refresh the employee dropdown if a department is selected


</script>


<script>
$('#projectForm').on('submit', function(e) {
    e.preventDefault();
    
    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').hide();
    
    let isValid = true;
    
    // Check required fields
    if (!$('#project_name').val().trim()) {
        showError('project_name', 'Project name is required');
        isValid = false;
    }

    if (!$('#project_type').val()) {
        showError('project_type', 'Project type is required');
        isValid = false;
    }
    
    if (!$('#department_id').val()) {
        showError('department_id', 'Please select a department');
        isValid = false;
    }
    
    // Check date validation if dates are provided
    const startDate = $('#project_startdate').val();
    const endDate = $('#project_enddate').val();
    
    if (startDate && endDate) {
        if (new Date(endDate) < new Date(startDate)) {
            showError('project_enddate', 'End date must be after start date');
            isValid = false;
        }
    }
    
    // Check assignments
    if (!validateAssignments()) {
        isValid = false;
    }
    
    if (isValid) {
        let formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            method: $(this).attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            },
            error: function(xhr) {
                // Handle errors
            }
        });
    }
});

function showError(fieldName, message) {
    $(`#${fieldName}`).addClass('is-invalid');
    $(`.${fieldName}-error`).text(message).show();
}

// Real-time validation for fields when they lose focus
$('#project_name, #department_id').on('blur', function() {
    const field = $(this);
    const fieldName = field.attr('id');
    
    if (fieldName === 'project_name' && !field.val().trim()) {
        showError(fieldName, 'Project name is required');
    } else if (fieldName === 'department_id' && !field.val()) {
        showError(fieldName, 'Please select a department');
    } else {
        field.removeClass('is-invalid');
        $(`.${fieldName}-error`).hide();
    }
});

$('#project_type').on('blur', function() {
    if (!$(this).val()) {
        showError('project_type', 'Project type is required');
    } else {
        $(this).removeClass('is-invalid');
        $('.project_type-error').hide();
    }
});

// Validate assigned data when adding/removing employees
function validateAssignments() {
    const assignedData = JSON.parse($('#assignedData').val() || '[]');
    const errorElement = $('.assigned_data-error');
    
    if (assignedData.length === 0) {
        errorElement.text('At least one employee assignment is required').show();
        $('#selectedAssignmentsBox').addClass('is-invalid');
        return false;
    }
    
    let hasEmployees = false;
    assignedData.forEach(assignment => {
        if (assignment.employee_ids && assignment.employee_ids.length > 0) {
            hasEmployees = true;
        }
    });
    
    if (!hasEmployees) {
        errorElement.text('At least one employee must be assigned').show();
        $('#selectedAssignmentsBox').addClass('is-invalid');
        return false;
    }
    
    errorElement.hide();
    $('#selectedAssignmentsBox').removeClass('is-invalid');
    return true;
}

// Call validateAssignments when assignments change
function updateAssignmentsUI() {
    // ... your existing code ...
    
    // Add validation check
    validateAssignments();
}
</script>