@php
    $widgetData = $this->getWidgetData();
@endphp
<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <x-filament-panels::resources.tabs />
    
    @if ($widgets = $this->getVisibleWidgets())
        <x-filament-widgets::widgets
            :columns="$this->getWidgetsColumns()"
            :data="$widgetData"
            :widgets="$widgets"
        />
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>