<x-filament-widgets::widget class="fi-wi-theme-config">
    <x-filament::section>

        <x-slot name="heading">
            Template info
        </x-slot>

        {{ $this->infolist }}

    </x-filament::section>

    <x-filament-actions::modals />

</x-filament-widgets::widget>