<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <x-inspirecms-support::model-explorer 
        :items="$this->getGroupedNodeItems()"
        expandedItemsStateKey="expandedModelExplorerItems"
        :model-explorer="$this->getModelExplorer()"
        translatable
        translatable-locale="{{ $this->getActiveActionsLocale() }}"
    >
        @if ($this->isDisplayTable())
            <div class="flex flex-col gap-y-6">
                <x-filament-panels::resources.tabs />

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

                {{ $this->table }}

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
            </div>
        @endif
    </x-inspirecms-support::model-explorer>
</x-filament-panels::page>