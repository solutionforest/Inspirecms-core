@php
    $selectedItemPath = $this->getSelectedFileItemPath();
@endphp
<x-filament-panels::page>
    <x-inspirecms-support::file-explorer 
        :items="$this->getGroupedNodeItems()"
    >
        <x-filament-actions::modals />
    </x-inspirecms-support::file-explorer>
</x-filament-panels::page>
