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
                if (empty($currentChecklist)) {
                    $dbAsset = \App\Models\EmployeeAsset::where('employee_id', $process->employee_id)->first();
                    if ($dbAsset) {
                        $currentChecklist = [
                            ['key' => 'laptop', 'issued' => $dbAsset->laptop],
                            ['key' => 'chargers', 'issued' => $dbAsset->chargers],
                            ['key' => 'mobile', 'issued' => $dbAsset->mobile],
                            ['key' => 'mouse', 'issued' => $dbAsset->mouse],
                            ['key' => 'sim_card', 'issued' => $dbAsset->sim_card],
                            ['key' => 'id_card', 'issued' => $dbAsset->id_card],
                            ['key' => 'other', 'issued' => $dbAsset->other],
                        ];
                    }
                }
            @endphp
            
            <div class="alert alert-info mb-4">
                <small><i class="ti ti-info-circle me-1"></i>{{ __('Select the physical assets to be issued for this employee. Only the selected assets will be saved and remain active.') }}</small>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Asset Name') }}</th>
                            <th class="text-center">{{ __('Onboarding Status') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($defaultAssetItems as $item)
                            @php
                                $existingItem = collect($currentChecklist)->firstWhere('key', $item['key']);
                                $isIssued = $existingItem ? ($existingItem['issued'] ?? false) : false;
                            @endphp
                            <tr class="{{ $isIssued ? 'table-light' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title rounded-circle {{ $isIssued ? 'bg-success text-white' : 'bg-light text-muted' }}" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="ti {{ $item['key'] === 'laptop' ? 'ti-device-laptop' : ($item['key'] === 'chargers' ? 'ti-plug' : ($item['key'] === 'mobile' ? 'ti-device-mobile' : ($item['key'] === 'mouse' ? 'ti-mouse' : ($item['key'] === 'sim_card' ? 'ti-credit-card' : ($item['key'] === 'id_card' ? 'ti-id' : 'ti-package'))))) }}"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $item['name'] }}</h6>
                                            <small class="text-muted">
                                                @if($isIssued)
                                                    {{ __('Asset has been issued') }}
                                                @else
                                                    {{ __('Asset is not issued') }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($isIssued)
                                        <span class="badge bg-success rounded-pill"><i class="ti ti-circle-check me-1"></i>{{ __('Issued') }}</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill"><i class="ti ti-clock me-1"></i>{{ __('Pending') }}</span>
                                    @endif
                                </td>
                                <td class="text-end text-right">
                                    <div class="form-check form-switch d-inline-block text-start">
                                        <input class="form-check-input asset-checkbox" type="checkbox" 
                                            name="checklist[{{ $item['key'] }}][issued]" 
                                            value="1" 
                                            id="asset_{{ $item['key'] }}"
                                            {{ $isIssued ? 'checked' : '' }}>
                                        <label class="form-check-label text-muted ms-2" for="asset_{{ $item['key'] }}">
                                            {{ $isIssued ? __('Issued') : __('Pending') }}
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
        $('.asset-checkbox').on('change', function() {
            var label = $(this).siblings('label');
            if ($(this).is(':checked')) {
                label.text('{{ __("Issued") }}').addClass('text-success').removeClass('text-muted');
            } else {
                label.text('{{ __("Pending") }}').addClass('text-muted').removeClass('text-success');
            }
        });

        // Run once on load to color-code already checked items
        $('.asset-checkbox').each(function() {
            var label = $(this).siblings('label');
            if ($(this).is(':checked')) {
                label.text('{{ __("Issued") }}').addClass('text-success').removeClass('text-muted');
            }
        });
    });

    $('#asset-issuance-form').on('submit', function(e) {
        e.preventDefault();
        var checklist = {};
        $(this).find('input[type="checkbox"]').each(function() {
            var nameAttr = $(this).attr('name');
            var match = nameAttr.match(/\[([^\]]+)\]\[issued\]/);
            if (match) {
                var key = match[1];
                checklist[key] = {
                    'key': $(this).siblings('input[type="hidden"][name*="[key]"]').val(),
                    'name': $(this).siblings('input[type="hidden"][name*="[name]"]').val(),
                    'issued': $(this).is(':checked') ? true : false
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









