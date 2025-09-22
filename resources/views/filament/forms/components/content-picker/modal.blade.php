@php
    $modalId = 'content-tree-picker-modal';
@endphp
<x-filament::modal 
    id="{{ $modalId }}"
    slide-over
    sticky-header
    sticky-footer
    footer-actions-alignment="end"
    width="4xl"
    class="content-tree-picker-modal-content"
    display-classes="block"
    x-init="() => {
        this.selectedContent = [];
        this.formKey = false;
    }"
    x-on:x-content-picker-modal-setup.window="
        if ($event?.detail?.modalId == '{{ $modalId }}') {
            
            this.selectedContent = $event?.detail?.selected ?? [];
            this.formKey = $event?.detail?.key ?? null;

            console.log('Content Picker Modal Setup', this.formKey, this.selectedContent);

            // Update setting on livewire component
            const livewireModalConfig = {
                ...$event?.detail?.config || [],
                modelable: {
                    selected: 'selectedContent',
                },
            };
            $dispatch('content-tree-node:modal-setup', { 
                key: this.formKey, 
                selected: this.selectedContent, 
                config: livewireModalConfig || [], 
            });

            if ($event.detail?.openModal ?? false) {
                open();
            }
        } 
    "
>
    <livewire:inspirecms::content-tree-node 
        lazy
        :isDisabled="false"
        :isModalPicker="true"
    />

    <x-slot name="footerActions">
        <x-filament::button type="button" x-on:click="() => {
            $dispatch(
                'update-content-picker-selection',
                { 
                    id: '{{ $modalId }}', 
                    key: this.formKey,
                    data: this.selectedContent || [] 
                }
            );
            close();
        }">
            {{ __('filament-actions::modal.actions.submit.label') }}
        </x-filament::button>
        <x-filament::button color="gray" x-on:click="close()">
            {{ __('filament-actions::modal.actions.cancel.label') }}
        </x-filament::button>
    </x-slot>
    
</x-filament::modal>