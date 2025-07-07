<x-filament-widgets::widget class="fi-wi-theme-info">
    
    <x-filament::section :header-actions="[
        $this->createThemeAction,
        $this->cloneThemeAction,
    ]">
        <x-slot name="heading">
            {{ __('inspirecms::widgets.theme_info.title') }}
        </x-slot>

        {{ $this->infolist }}

    </x-filament::section>
    

    <x-filament-actions::modals />

</x-filament-widgets::widget>