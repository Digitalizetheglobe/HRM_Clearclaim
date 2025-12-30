{{ Form::open(['route' => ['offboarding.update-step', $process->id, 2], 'method' => 'POST', 'id' => 'access-removal-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <h6 class="mb-3">{{ __('Access Removal Checklist') }}</h6>
            @php
                $defaultAccessItems = [
                    ['name' => 'Biometric access', 'key' => 'biometric'],
                    ['name' => 'Official Email', 'key' => 'email'],
                    ['name' => 'CRM access', 'key' => 'crm'],
                    ['name' => 'WhatsApp', 'key' => 'whatsapp'],
                    ['name' => 'Other system accounts', 'key' => 'other'],
                ];
                $currentChecklist = is_array($process->access_removal_checklist) ? $process->access_removal_checklist : [];
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
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Save Checklist') }}</button>
</div>
{{ Form::close() }}

<script>
    $('#access-removal-form').on('submit', function(e) {
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

