<x-inspirecms::page.split-image-form class="w-full px-6 py-8 md:px-24 lg:px-44 lg:py-12">
    <x-filament-panels::form id="form" wire:submit="register">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

</x-inspirecms::page.split-image-form>