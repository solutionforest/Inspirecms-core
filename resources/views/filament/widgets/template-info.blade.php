<x-filament-widgets::widget class="fi-wi-template-info">
        
    <x-filament::section :header-actions="[
        $this->exportContentTemplatesAction,
    ]">
        <x-slot name="heading">
            {{ __('inspirecms::widgets.template_info.title') }}
        </x-slot>

        {{ $this->infolist }}

    </x-filament::section>

    <x-filament-actions::modals />

</x-filament-widgets::widget>