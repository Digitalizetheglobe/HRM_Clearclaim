{{ Form::open(['route' => ['onboarding.update-step', $process->id, 5], 'method' => 'POST', 'id' => 'asset-issuance-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <h6 class="mb-3">{{ __('Asset Issuance Checklist') }}</h6>
            @php
                $defaultAssetItems = [
                    ['name' => 'Laptop', 'key' => 'laptop'],
                    ['name' => 'Chargers', 'key' => 'chargers'],
                    ['name' => 'Mobile', 'key' => 'mobile'],
                    ['name' => 'Mouse', 'key' => 'mouse'],
                    ['name' => 'SIM card', 'key' => 'sim_card'],
                    ['name' => 'ID card', 'key' => 'id_card'],
                    ['name' => 'Other assets', 'key' => 'other'],
                ];
                $currentChecklist = is_array($process->asset_issuance_checklist) ? $process->asset_issuance_checklist : [];
            @endphp
            
            <div class="checklist-items">
                @foreach($defaultAssetItems as $item)
                    @php
                        $existingItem = collect($currentChecklist)->firstWhere('key', $item['key']);
                        $isIssued = $existingItem ? ($existingItem['issued'] ?? false) : false;
                    @endphp
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" 
                            name="checklist[{{ $item['key'] }}][issued]" 
                            value="1" 
                            id="asset_{{ $item['key'] }}"
                            {{ $isIssued ? 'checked' : '' }}>
                        <label class="form-check-label" for="asset_{{ $item['key'] }}">
                            {{ $item['name'] }}
                        </label>
                        <input type="hidden" name="checklist[{{ $item['key'] }}][key]" value="{{ $item['key'] }}">
                        <input type="hidden" name="checklist[{{ $item['key'] }}][name]" value="{{ $item['name'] }}">
                    </div>
                @endforeach
            </div>
            <div class="alert alert-warning mt-3">
                <small><i class="ti ti-info-circle me-1"></i>{{ __('All assets must be marked as Issued before proceeding to the next step.') }}</small>
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
    $('#asset-issuance-form').on('submit', function(e) {
        e.preventDefault();
        var checklist = {};
        $(this).find('input[type="checkbox"]').each(function() {
            var key = $(this).attr('name').match(/\[([^\]]+)\]\[issued\]/)[1];
            checklist[key] = {
                'key': $(this).siblings('input[type="hidden"][name*="[key]"]').val(),
                'name': $(this).siblings('input[type="hidden"][name*="[name]"]').val(),
                'issued': $(this).is(':checked') ? true : false
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








