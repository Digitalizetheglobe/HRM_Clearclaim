{{ Form::open(['route' => ['offboarding.update-step', $process->id, 4], 'method' => 'POST', 'id' => 'access-removal-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <h6 class="mb-3">{{ __('Access Removal Checklist') }}</h6>
            @php
                $defaultAccessItems = [
                    ['name' => 'Biometric access', 'key' => 'biometric', 'onboard_key' => 'biometric'],
                    ['name' => 'Official Email', 'key' => 'email', 'onboard_key' => 'email'],
                    ['name' => 'CRM access', 'key' => 'crm', 'onboard_key' => 'crm'],
                    ['name' => 'WhatsApp Group', 'key' => 'whatsapp', 'onboard_key' => 'whatsapp'],
                    ['name' => 'Internal Tools', 'key' => 'internal_tools', 'onboard_key' => 'internal_tools'],
                    ['name' => 'Other systems', 'key' => 'other', 'onboard_key' => 'other'],
                ];
                
                $dbAccess = \App\Models\EmployeeSystemAccess::where('employee_id', $process->employee_id)->first();
                $currentChecklist = is_array($process->access_removal_checklist) ? $process->access_removal_checklist : [];
            @endphp
            
            <div class="alert alert-info mb-4">
                <small><i class="ti ti-info-circle me-1"></i>{{ __('Below are the system accesses that were configured during Onboarding. Please verify and revoke them as part of the exit clearance.') }}</small>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('System / Tool') }}</th>
                            <th class="text-center">{{ __('Onboarding Status') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($defaultAccessItems as $item)
                            @php
                                $existingItem = collect($currentChecklist)->firstWhere('key', $item['key']);
                                $isDone = $existingItem ? ($existingItem['done'] ?? false) : false;
                                
                                // Determine if the employee had access provisioned during onboarding
                                if ($item['onboard_key'] === 'hrm_login') {
                                    $hasAccess = ($process->employee && $process->employee->user && $process->employee->user->is_active == 1);
                                } else {
                                    $onboardKey = $item['onboard_key'];
                                    $hasAccess = $dbAccess ? ($dbAccess->$onboardKey ?? false) : false;
                                }
                            @endphp
                            <tr class="{{ $hasAccess ? 'table-light' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title rounded-circle {{ $hasAccess ? 'bg-danger text-white' : 'bg-light text-muted' }}" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="ti {{ $item['onboard_key'] === 'hrm_login' ? 'ti-login' : ($item['key'] === 'biometric' ? 'ti-fingerprint' : ($item['key'] === 'email' ? 'ti-mail' : ($item['key'] === 'crm' ? 'ti-users' : ($item['key'] === 'internal_tools' ? 'ti-folder' : ($item['key'] === 'whatsapp' ? 'ti-brand-whatsapp' : 'ti-settings'))))) }}"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $item['name'] }}</h6>
                                            <small class="text-muted">
                                                @if($hasAccess)
                                                    {{ __('Access was active & provisioned') }}
                                                @else
                                                    {{ __('No access assigned') }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($hasAccess)
                                        <span class="badge bg-danger rounded-pill"><i class="ti ti-lock-open me-1"></i>{{ __('Active Access') }}</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill"><i class="ti ti-lock me-1"></i>{{ __('No Access') }}</span>
                                    @endif
                                </td>
                                <td class="text-end text-right">
                                    <div class="form-check form-switch d-inline-block text-start">
                                        <input class="form-check-input access-checkbox" type="checkbox" 
                                            name="checklist[{{ $item['key'] }}][done]" 
                                            value="1" 
                                            id="access_{{ $item['key'] }}"
                                            {{ $isDone ? 'checked' : '' }}>
                                        <label class="form-check-label text-muted ms-2" for="access_{{ $item['key'] }}">
                                            {{ $isDone ? __('Revoked') : __('Active') }}
                                        </label>
                                        <input type="hidden" name="checklist[{{ $item['key'] }}][key]" value="{{ $item['key'] }}">
                                        <input type="hidden" name="checklist[{{ $item['key'] }}][name]" value="{{ $item['name'] }}">
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
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Save & Proceed') }}</button>
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        // Dynamically update label on switch change
        $('.access-checkbox').on('change', function() {
            var label = $(this).siblings('label');
            if ($(this).is(':checked')) {
                label.text('{{ __("Revoked") }}').addClass('text-success').removeClass('text-muted');
            } else {
                label.text('{{ __("Active") }}').addClass('text-muted').removeClass('text-success');
            }
        });

        // Run once on load to color-code already checked items
        $('.access-checkbox').each(function() {
            var label = $(this).siblings('label');
            if ($(this).is(':checked')) {
                label.text('{{ __("Revoked") }}').addClass('text-success').removeClass('text-muted');
            }
        });
    });

    $('#access-removal-form').on('submit', function(e) {
        e.preventDefault();
        var checklist = {};
        $(this).find('input[type="checkbox"]').each(function() {
            var nameAttr = $(this).attr('name');
            var match = nameAttr.match(/\[([^\]]+)\]\[done\]/);
            if (match) {
                var key = match[1];
                checklist[key] = {
                    'key': $(this).siblings('input[type="hidden"][name*="[key]"]').val(),
                    'name': $(this).siblings('input[type="hidden"][name*="[name]"]').val(),
                    'done': $(this).is(':checked') ? true : false
                };
            }
        });
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: {
                checklist: checklist,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                show_toastr('Success', data.message || 'Checklist updated successfully', 'success');
                location.reload();
            },
            error: function(data) {
                var errorMsg = data.responseJSON && data.responseJSON.error ? data.responseJSON.error : 'An error occurred';
                show_toastr('Error', errorMsg, 'error');
            }
        });
    });
</script>


