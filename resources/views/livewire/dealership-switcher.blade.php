<div class="crm-dealership-switcher">
    @if ($dealerships->count() > 0)
        <select
            wire:model.live="selectedDealershipId"
            class="crm-dealership-switcher__select"
        >
            @foreach ($dealerships as $dealership)
                <option value="{{ $dealership->id }}">
                    {{ $dealership->name }}
                </option>
            @endforeach
        </select>
    @endif
</div>
