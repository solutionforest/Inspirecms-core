<x-filament-panels::page
    @class([
        'fi-resource-view-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>

    <x-inspirecms-support::model-explorer 
        :items="$this->getGroupedNodeItems()"
        expandedItemsStateKey="expandedModelExplorerItems"
        :actions="$this->getModelExplorer()->getActions()"
    >
        @php
            $relationManagers = $this->getRelationManagers();
            $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
        @endphp

        @if ((! $hasCombinedRelationManagerTabsWithContent) || (! count($relationManagers)))
            @if ($this->hasInfolist())
                {{ $this->infolist }}
            @else
                <div
                    wire:key="{{ $this->getId() }}.forms.{{ $this->getFormStatePath() }}"
                    class="pb-6"
                >
                    {{ $this->form }}
                </div>
            @endif
        @endif

        @if (count($relationManagers))
            <x-filament-panels::resources.relation-managers
                :active-locale="isset($activeLocale) ? $activeLocale : null"
                :active-manager="$this->activeRelationManager ?? ($hasCombinedRelationManagerTabsWithContent ? null : array_key_first($relationManagers))"
                :content-tab-label="$this->getContentTabLabel()"
                :content-tab-icon="$this->getContentTabIcon()"
                :content-tab-position="$this->getContentTabPosition()"
                :managers="$relationManagers"
                :owner-record="$record"
                :page-class="static::class"
            >
                @if ($hasCombinedRelationManagerTabsWithContent)
                    <x-slot name="content">
                        @if ($this->hasInfolist())
                            {{ $this->infolist }}
                        @else
                            {{ $this->form }}
                        @endif
                    </x-slot>
                @endif
            </x-filament-panels::resources.relation-managers>
        @endif
    </x-inspirecms-support::model-explorer>
</x-filament-panels::page>