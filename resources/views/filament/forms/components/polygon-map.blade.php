<div class="space-y-2">
    @if ($label = $getLabel())
        <label class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <div wire:ignore>
        <livewire:polygon-map-input
            :coordinates="$getState() ?? []"
            :height="$getHeight()"
            wire:model="{{ $getStatePath() }}"
        />
    </div>

    @if ($hint = $getHint())
        <p class="text-sm text-gray-500">{{ $hint }}</p>
    @endif
</div>
