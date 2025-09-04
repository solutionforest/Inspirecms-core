<div class="flex flex-col space-y-2"
    @if (isset($modelable) && filled($modelable))
        x-modelable="selected" x-model="{{ $modelable }}"
    @endif
    x-data="TreeNode({
        selected: $wire.entangle('selectedModelItemKeys').live,
        expanded: $wire.entangle('expandedModelItemKeys').live,
    })"
    x-on:content-tree-node:reset-selected.window="
        if ($event?.detail?.key === @js($this->customId)) {
            selected = [];
        }
    "
>
    @if (! $isDisabled)
        
        <x-filament::input.wrapper>
            <x-filament::input
                type="text"
                wire:model.live="search"
                :placeholder="@trans('inspirecms::inspirecms.search.placeholder')"
            />
        </x-filament::input.wrapper>

        <div wire:loading wire:target="search"> 
            {{ __('inspirecms::inspirecms.search.message') }}
        </div>
    @endif

    @if (count($items) > 0)
        @if ($this->isFilteringBySearch())
            <x-inspirecms-support::tree-node.model-explorer.groups
                :items="$items" 
                :model-explorer="$this->getModelExplorer()"
                :is-disabled="$isDisabled"
            />
            <x-filament::pagination 
                class="pt-2"
                :paginator="$items" 
                :page-options="$pageOptions"
                current-page-option-property="perPage"
                extreme-links
            />
        @else
            <x-inspirecms-support::tree-node.model-explorer
                skip-alpine="true"
                :items="$items" 
                :model-explorer="$this->getModelExplorer()"
                :is-disabled="$isDisabled"
            />
        @endif
    @else
        <p class="text-gray-500 dark:text-gray-400">
            {{ __('inspirecms::inspirecms.search.no_results') }}
        </p>
    @endif

</div>