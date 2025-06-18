<x-filament-widgets::widget class="fi-wi-cms-version-info">
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-x-2">
                <h1 class="text-xl font-semibold text-gray-900">InspireCMS</h1>
                <p class="text-sm text-gray-500">{{ $this->getVersionDisplayText() }}</p>
            </div>
            @if ($this->canUpgrade())
                <div>
                    {{ $this->upgradeAction }}
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>