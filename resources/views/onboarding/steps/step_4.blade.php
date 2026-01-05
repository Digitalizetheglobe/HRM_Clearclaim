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
            @endphp
            
            <div class="checklist-items">
                @foreach($defaultAccessItems as $item)
                    @php
                        $existingItem = collect($currentChecklist)->firstWhere('key', $item['key']);
                        $isDone = $existingItem ? ($existingItem['done'] ?? false) : false;
                    @endphp
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" 
                            name="checklist[{{ $item['key'] }}][done]" 
                            value="1" 
                            id="access_{{ $item['key'] }}"
                            {{ $isDone ? 'checked' : '' }}>
                        <label class="form-check-label" for="access_{{ $item['key'] }}">
                            {{ $item['name'] }}
                        </label>
                        <input type="hidden" name="checklist[{{ $item['key'] }}][key]" value="{{ $item['key'] }}">
                        <input type="hidden" name="checklist[{{ $item['key'] }}][name]" value="{{ $item['name'] }}">
                    </div>
                @endforeach
            </div>
            <div class="alert alert-warning mt-3">
                <small><i class="ti ti-info-circle me-1"></i>{{ __('All items must be marked as Done before proceeding to the next step.') }}</small>
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
    $('#system-access-form').on('submit', function(e) {
        e.preventDefault();
        var checklist = {};
        $(this).find('input[type="checkbox"]').each(function() {
            var key = $(this).attr('name').match(/\[([^\]]+)\]\[done\]/)[1];
            checklist[key] = {
                'key': $(this).siblings('input[type="hidden"][name*="[key]"]').val(),
                'name': $(this).siblings('input[type="hidden"][name*="[name]"]').val(),
                'done': $(this).is(':checked') ? true : false
            };
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




