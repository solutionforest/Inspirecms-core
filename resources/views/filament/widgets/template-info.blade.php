<x-filament-widgets::widget class="fi-wi-template-info">
    
    <div class="flex flex-col gap-4">

        <x-filament::section :header-actions="[
            $this->createThemeAction,
            $this->cloneThemeAction,
        ]">
            <x-slot name="heading">
                Theme info
            </x-slot>
    
            {{ $this->themeInfolist }}
    
        </x-filament::section>
    
        
        <x-filament::section :header-actions="[
            $this->exportContentTemplatesAction,
        ]">
            <x-slot name="heading">
                Template info
            </x-slot>
    
            {{ $this->templateInfolist }}
    
        </x-filament::section>

    </div>

    <x-filament-actions::modals />

</x-filament-widgets::widget>