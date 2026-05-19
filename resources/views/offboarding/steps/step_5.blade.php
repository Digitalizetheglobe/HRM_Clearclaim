{{ Form::open(['route' => ['offboarding.update-step', $process->id, 5], 'method' => 'POST', 'id' => 'asset-collection-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <h6 class="mb-3">{{ __('Asset Collection Checklist') }}</h6>
            @php
                $defaultAssetItems = [
                    ['name' => 'Laptop', 'key' => 'laptop', 'onboard_key' => 'laptop'],
                    ['name' => 'Chargers', 'key' => 'chargers', 'onboard_key' => 'chargers'],
                    ['name' => 'Mobile', 'key' => 'mobile', 'onboard_key' => 'mobile'],
                    ['name' => 'Mouse', 'key' => 'mouse', 'onboard_key' => 'mouse'],
                    ['name' => 'SIM card', 'key' => 'sim_card', 'onboard_key' => 'sim_card'],
                    ['name' => 'ID card', 'key' => 'id_card', 'onboard_key' => 'id_card'],
                    ['name' => 'Other assets', 'key' => 'other', 'onboard_key' => 'other'],
                ];
                
                $dbAsset = \App\Models\EmployeeAsset::where('employee_id', $process->employee_id)->first();
                $currentChecklist = is_array($process->asset_collection_checklist) ? $process->asset_collection_checklist : [];
            @endphp
            
            <div class="alert alert-info mb-4">
                <small><i class="ti ti-info-circle me-1"></i>{{ __('Below are the physical assets that were issued during Onboarding. Please verify and collect them as part of the exit clearance.') }}</small>
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
                                $isCollected = $existingItem ? ($existingItem['collected'] ?? false) : false;
                                
                                // Determine if the asset was issued during onboarding
                                if (!empty($item['onboard_key'])) {
                                    $onboardKey = $item['onboard_key'];
                                    $wasIssued = $dbAsset ? ($dbAsset->$onboardKey ?? false) : false;
                                } else {
                                    $wasIssued = false;
                                }
                            @endphp
                            <tr class="{{ $wasIssued ? 'table-light' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title rounded-circle {{ $wasIssued ? 'bg-danger text-white' : 'bg-light text-muted' }}" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="ti {{ $item['key'] === 'laptop' ? 'ti-device-laptop' : ($item['key'] === 'chargers' ? 'ti-plug' : ($item['key'] === 'mobile' ? 'ti-device-mobile' : ($item['key'] === 'mouse' ? 'ti-mouse' : ($item['key'] === 'sim_card' ? 'ti-credit-card' : ($item['key'] === 'id_card' ? 'ti-id' : 'ti-package'))))) }}"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $item['name'] }}</h6>
                                            <small class="text-muted">
                                                @if($wasIssued)
                                                    {{ __('Asset is currently in possession') }}
                                                @else
                                                    {{ __('No issuance record found') }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($wasIssued)
                                        <span class="badge bg-danger rounded-pill"><i class="ti ti-alert-triangle me-1"></i>{{ __('Issued') }}</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill"><i class="ti ti-minus me-1"></i>{{ __('Not Issued') }}</span>
                                    @endif
                                </td>
                                <td class="text-end text-right">
                                    <div class="form-check form-switch d-inline-block text-start">
                                        <input class="form-check-input asset-checkbox" type="checkbox" 
                                            name="checklist[{{ $item['key'] }}][collected]" 
                                            value="1" 
                                            id="asset_{{ $item['key'] }}"
                                            {{ $isCollected ? 'checked' : '' }}>
                                        <label class="form-check-label text-muted ms-2" for="asset_{{ $item['key'] }}">
                                            {{ $isCollected ? __('Collected') : __('Pending') }}
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
                label.text('{{ __("Collected") }}').addClass('text-success').removeClass('text-muted');
            } else {
                label.text('{{ __("Pending") }}').addClass('text-muted').removeClass('text-success');
            }
        });

        // Run once on load to color-code already checked items
        $('.asset-checkbox').each(function() {
            var label = $(this).siblings('label');
            if ($(this).is(':checked')) {
                label.text('{{ __("Collected") }}').addClass('text-success').removeClass('text-muted');
            }
        });
    });

    $('#asset-collection-form').on('submit', function(e) {
        e.preventDefault();
        var checklist = {};
        $(this).find('input[type="checkbox"]').each(function() {
            var nameAttr = $(this).attr('name');
            var match = nameAttr.match(/\[([^\]]+)\]\[collected\]/);
            if (match) {
                var key = match[1];
                checklist[key] = {
                    'key': $(this).siblings('input[type="hidden"][name*="[key]"]').val(),
                    'name': $(this).siblings('input[type="hidden"][name*="[name]"]').val(),
                    'collected': $(this).is(':checked') ? true : false
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


