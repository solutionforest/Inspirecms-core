@php
    $selectedItemPath = $this->getSelectedFileItemPath();
@endphp
<x-filament-panels::page>
    <div>
        {{ $this->table }}
    </div>
    <x-filament::section compact>
        <x-slot name="heading">
            View components
        </x-slot>
        <x-inspirecms-support::file-explorer 
            class="list-view-components"
            :items="$this->getGroupedNodeItems()"
        >
            <x-filament-actions::modals />
        </x-inspirecms-support::file-explorer>
    </x-filament::section>
</x-filament-panels::page>
