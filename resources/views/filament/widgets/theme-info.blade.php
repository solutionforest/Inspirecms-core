<x-filament-widgets::widget class="fi-wi-theme-info">
    
    <x-filament::section
        :heading="__('inspirecms::widgets.theme_info.title')"
        heading-tag="h3"
    >
        <x-slot name="afterHeader">
            <div class="fi-header-actions-ctn">
                {{ $this->createThemeAction }}
                {{ $this->cloneThemeAction }}
            </div>
        </x-slot>

        {{ $this->infolist }}

    </x-filament::section>
    

    <x-filament-actions::modals />

</x-filament-widgets::widget>