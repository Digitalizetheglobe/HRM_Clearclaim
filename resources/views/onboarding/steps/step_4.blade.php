{{ Form::open(['route' => ['onboarding.update-step', $process->id, 4], 'method' => 'POST', 'id' => 'system-access-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <h6 class="mb-3">{{ __('System & Access Provisioning Checklist') }}</h6>
            @php
                $defaultAccessItems = [
                    ['name' => 'Biometric access', 'key' => 'biometric'],
                    ['name' => 'Official Email', 'key' => 'email'],
                    ['name' => 'CRM access', 'key' => 'crm'],
                    ['name' => 'WhatsApp Group', 'key' => 'whatsapp'],
                    ['name' => 'Internal Tools', 'key' => 'internal_tools'],
                    ['name' => 'Other systems', 'key' => 'other'],
                ];
                
                $currentChecklist = is_array($process->system_access_checklist) ? $process->system_access_checklist : [];
                if (empty($currentChecklist)) {
                    $dbAccess = \App\Models\EmployeeSystemAccess::where('employee_id', $process->employee_id)->first();
                    if ($dbAccess) {
                        $currentChecklist = [
                            ['key' => 'biometric', 'done' => $dbAccess->biometric],
                            ['key' => 'email', 'done' => $dbAccess->email],
                            ['key' => 'crm', 'done' => $dbAccess->crm],
                            ['key' => 'whatsapp', 'done' => $dbAccess->whatsapp],
                            ['key' => 'internal_tools', 'done' => $dbAccess->internal_tools],
                            ['key' => 'other', 'done' => $dbAccess->other],
                        ];
                    }
                }
            @endphp
            
            <div class="alert alert-info mb-4">
                <small><i class="ti ti-info-circle me-1"></i>{{ __('Select the systems and accesses to be provisioned for this employee. Only the selected options will be saved and remain active.') }}</small>
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
                            @endphp
                            <tr class="{{ $isDone ? 'table-light' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title rounded-circle {{ $isDone ? 'bg-success text-white' : 'bg-light text-muted' }}" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="ti {{ $item['key'] === 'biometric' ? 'ti-fingerprint' : ($item['key'] === 'email' ? 'ti-mail' : ($item['key'] === 'crm' ? 'ti-users' : ($item['key'] === 'internal_tools' ? 'ti-folder' : ($item['key'] === 'whatsapp' ? 'ti-brand-whatsapp' : 'ti-settings')))) }}"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $item['name'] }}</h6>
                                            <small class="text-muted">
                                                @if($isDone)
                                                    {{ __('Access is currently active') }}
                                                @else
                                                    {{ __('Access is not provisioned') }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($isDone)
                                        <span class="badge bg-success rounded-pill"><i class="ti ti-circle-check me-1"></i>{{ __('Provisioned') }}</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill"><i class="ti ti-clock me-1"></i>{{ __('Pending') }}</span>
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
                                            {{ $isDone ? __('Active') : __('Inactive') }}
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
                label.text('{{ __("Active") }}').addClass('text-success').removeClass('text-muted');
            } else {
                label.text('{{ __("Inactive") }}').addClass('text-muted').removeClass('text-success');
            }
        });

        // Run once on load to color-code already checked items
        $('.access-checkbox').each(function() {
            var label = $(this).siblings('label');
            if ($(this).is(':checked')) {
                label.text('{{ __("Active") }}').addClass('text-success').removeClass('text-muted');
            }
        });
    });

    $('#system-access-form').on('submit', function(e) {
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
                show_toastr('Success', data.message || '{{ __("Checklist updated successfully") }}', 'success');
                location.reload();
            },
            error: function(data) {
                var errorMsg = data.responseJSON && data.responseJSON.error ? data.responseJSON.error : '{{ __("An error occurred") }}';
                show_toastr('Error', errorMsg, 'error');
            }
        });
    });
</script>









