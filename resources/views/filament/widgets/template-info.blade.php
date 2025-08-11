<x-filament-widgets::widget class="fi-wi-template-info">
        
    <x-filament::section 
        :heading="__('inspirecms::widgets.template_info.title')"
        heading-tag="h3"
    >
        <x-slot name="afterHeader">
            <div class="fi-header-actions-ctn">
                {{ $this->exportContentTemplatesAction }}
            </div>
        </x-slot>

        {{ $this->infolist }}

    </x-filament::section>

    <x-filament-actions::modals />

</x-filament-widgets::widget>