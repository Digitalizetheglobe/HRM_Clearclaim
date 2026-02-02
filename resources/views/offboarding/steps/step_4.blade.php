{{ Form::open(['route' => ['offboarding.update-step', $process->id, 3], 'method' => 'POST', 'id' => 'asset-collection-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <h6 class="mb-3">{{ __('Asset Collection Checklist') }}</h6>
            @php
                $defaultAssetItems = [
                    ['name' => 'Laptop', 'key' => 'laptop'],
                    ['name' => 'Charger', 'key' => 'charger'],
                    ['name' => 'Mobile', 'key' => 'mobile'],
                    ['name' => 'Mouse', 'key' => 'mouse'],
                    ['name' => 'SIM card', 'key' => 'sim'],
                    ['name' => 'ID card', 'key' => 'id_card'],
                    ['name' => 'Other assets', 'key' => 'other'],
                ];
                $currentChecklist = is_array($process->asset_collection_checklist) ? $process->asset_collection_checklist : [];
            @endphp
            
            <div class="checklist-items">
                @foreach($defaultAssetItems as $item)
                    @php
                        $existingItem = collect($currentChecklist)->firstWhere('key', $item['key']);
                        $isCollected = $existingItem ? ($existingItem['collected'] ?? false) : false;
                    @endphp
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" 
                            name="checklist[{{ $item['key'] }}][collected]" 
                            value="1" 
                            id="asset_{{ $item['key'] }}"
                            {{ $isCollected ? 'checked' : '' }}>
                        <label class="form-check-label" for="asset_{{ $item['key'] }}">
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
    $('#asset-collection-form').on('submit', function(e) {
        e.preventDefault();
        var checklist = {};
        $(this).find('input[type="checkbox"]').each(function() {
            var key = $(this).attr('name').match(/\[([^\]]+)\]\[collected\]/)[1];
            checklist[key] = {
                'key': $(this).siblings('input[type="hidden"][name*="[key]"]').val(),
                'name': $(this).siblings('input[type="hidden"][name*="[name]"]').val(),
                'collected': $(this).is(':checked') ? true : false
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

