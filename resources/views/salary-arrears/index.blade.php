@extends('layouts.admin')

@section('page-title')
    {{ __('Salary Arrears') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Salary Arrears') }}</li>
@endsection

@section('action-button')
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createArrearModal">
        <i class="ti ti-plus"></i>
    </button>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Salary Arrears List') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee Name') }}</th>
                                <th>{{ __('Pending Month') }}</th>
                                <th>{{ __('Payment Month') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($arrears as $arrear)
                                <tr>
                                    <td>{{ $arrear->employee->name ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($arrear->pending_month)->format('M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($arrear->payment_month)->format('M Y') }}</td>
                                    <td><strong>{{ \Auth::user()->priceFormat($arrear->amount) }}</strong></td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger text-white" 
                                                title="{{ __('Delete') }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteArrearModal"
                                                data-arrear-id="{{ $arrear->id }}"
                                                data-employee-name="{{ $arrear->employee->name ?? '-' }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="py-4">
                                            <i class="ti ti-inbox" style="font-size: 48px; color: #ccc;"></i>
                                            <p class="mt-2 text-muted">{{ __('No salary arrears found.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Arrear Modal -->
<div class="modal fade" id="deleteArrearModal" tabindex="-1" aria-labelledby="deleteArrearModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteArrearModalLabel">{{ __('Delete Salary Arrears') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete this salary arrears entry?') }}</p>
                <p class="text-muted"><small id="deleteEmployeeInfo"></small></p>
                <p class="text-danger"><small>{{ __('This action cannot be undone.') }}</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <form id="deleteArrearForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Create Arrear Modal -->
<div class="modal fade" id="createArrearModal" tabindex="-1" aria-labelledby="createArrearModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createArrearModalLabel">{{ __('Add Salary Arrears') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="arrearForm" method="POST" action="{{ route('salary-arrears.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Department') }} <span class="text-danger">*</span></label>
                        <select id="department_id" class="form-control" required>
                            <option value="">{{ __('Select Department') }}</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Employee') }} <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" id="employee_search" class="form-control" placeholder="{{ __('Search employee...') }}" autocomplete="off">
                            <input type="hidden" id="employee_id" name="employee_id" required>
                            <div id="employee_dropdown" class="position-absolute w-100 bg-white border rounded shadow-lg d-none mt-1" style="z-index: 1050; max-height: 200px; overflow-y: auto; top: 100%;">
                            </div>
                        </div>
                        <small class="text-muted">{{ __('Start typing to search employees') }}</small>
                        <div id="selected_employee" class="mt-2 d-none">
                            <span class="badge bg-primary"></span>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Pending Month') }} <span class="text-danger">*</span></label>
                        <input type="month" name="pending_month" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Payment Month') }} <span class="text-danger">*</span></label>
                        <input type="month" name="payment_month" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
$(document).ready(function() {
    let allEmployees = [];
    let selectedEmployeeId = null;

    // Load departments
    $.ajax({
        url: '{{ route("salary-arrears.departments") }}',
        type: 'GET',
        success: function(data) {
            let options = '<option value="">{{ __("Select Department") }}</option>';
            data.forEach(function(dept) {
                options += `<option value="${dept.id}">${dept.name}</option>`;
            });
            $('#department_id').html(options);
        }
    });

    // Department change event
    $('#department_id').change(function() {
        const departmentId = $(this).val();
        if (departmentId) {
            loadEmployees(departmentId);
        } else {
            allEmployees = [];
            $('#employee_search').val('');
            $('#employee_id').val('').addClass('d-none');
            $('#employee_dropdown').addClass('d-none').html('');
        }
    });

    // Load employees by department
    function loadEmployees(departmentId, search = '') {
        $.ajax({
            url: '{{ route("salary-arrears.employees") }}',
            type: 'POST',
            data: {
                department_id: departmentId,
                search: search,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                allEmployees = data;
                updateEmployeeDropdown();
            }
        });
    }

    // Employee search input
    $('#employee_search').on('input', function() {
        const search = $(this).val();
        const departmentId = $('#department_id').val();
        
        if (departmentId) {
            loadEmployees(departmentId, search);
        }
    });

    // Update employee dropdown
    function updateEmployeeDropdown() {
        const search = $('#employee_search').val().toLowerCase();
        const filtered = allEmployees.filter(function(emp) {
            return emp.name.toLowerCase().includes(search) || 
                   (emp.email && emp.email.toLowerCase().includes(search)) ||
                   (emp.employee_id && emp.employee_id.toLowerCase().includes(search));
        });

        let html = '';
        if (filtered.length > 0) {
            filtered.forEach(function(emp) {
                html += `<div class="p-2 border-bottom employee-option" data-id="${emp.id}" data-name="${emp.name}" style="cursor: pointer;">
                    <strong>${emp.name}</strong><br>
                    <small class="text-muted">${emp.email || ''} ${emp.employee_id ? '| ' + emp.employee_id : ''}</small>
                </div>`;
            });
            $('#employee_dropdown').removeClass('d-none').html(html);
        } else {
            $('#employee_dropdown').addClass('d-none').html('');
        }
    }

    // Select employee from dropdown
    $(document).on('click', '.employee-option', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        selectedEmployeeId = id;
        $('#employee_search').val(name);
        $('#employee_id').val(id);
        $('#employee_dropdown').addClass('d-none');
        $('#selected_employee').removeClass('d-none').find('.badge').text(name);
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#employee_search, #employee_dropdown').length) {
            $('#employee_dropdown').addClass('d-none');
        }
    });

    // Form validation
    $('#arrearForm').on('submit', function(e) {
        if (!selectedEmployeeId) {
            e.preventDefault();
            alert('{{ __("Please select an employee") }}');
            return false;
        }
    });

    // Reset form when modal is closed
    $('#createArrearModal').on('hidden.bs.modal', function() {
        $('#arrearForm')[0].reset();
        allEmployees = [];
        selectedEmployeeId = null;
        $('#employee_search').val('');
        $('#employee_id').val('');
        $('#employee_dropdown').addClass('d-none').html('');
        $('#selected_employee').addClass('d-none');
    });

    // Delete modal handler
    $('#deleteArrearModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const arrearId = button.data('arrear-id');
        const employeeName = button.data('employee-name');
        const deleteUrl = '{{ route("salary-arrears.destroy", ":id") }}'.replace(':id', arrearId);
        
        $('#deleteArrearForm').attr('action', deleteUrl);
        $('#deleteEmployeeInfo').text('{{ __("Employee") }}: ' + employeeName);
    });
});
</script>
@endpush

