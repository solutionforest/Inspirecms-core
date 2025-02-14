@php
    $widgetData = $this->getWidgetData();
@endphp
<x-inspirecms::page.extra-sub-navigation
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    @switch($navigationPageType)
        @case('tree')
            
            <x-filament-panels::resources.tabs />
            
            @if ($widgets = $this->getVisibleWidgets())
                <x-filament-widgets::widgets
                    :columns="$this->getWidgetsColumns()"
                    :data="$widgetData"
                    :widgets="$widgets"
                />
            @endif

            @break
        @default
            
        <div class="flex flex-col gap-y-6">
            <x-filament-panels::resources.tabs />

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

            {{ $this->table }}

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
        </div>

    @endswitch
    
    <x-filament-actions::modals />
</x-inspirecms::page.extra-sub-navigation>