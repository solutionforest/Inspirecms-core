<div class="flex flex-col space-y-2"
    x-data="TreeNode({
        selected: $wire.entangle('selectedNodes').live,
        expanded: $wire.entangle('expandedNodes').live,
    })"
    {{ $this->getExtraAlpineAttributeBag() }}
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

    @if (count($nodes) > 0)
        {{-- @if ($this->isFilteringBySearch())
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
        @endif --}}
        
        <x-inspirecms-support::tree-node.service-side-tree
            :nodes="$nodes"
            :livewire="$this"
            :hasNodeActions="$showNodeActions"
            :toolbarActions="$toolbarActions"
            :navigationHeaderActions="$navigationHeaderActions"
            :showNavigationHeader="$showNavigationHeader"
            :enableSelection="$enableSelection"
            :multipleSelection="$multipleSelection"
            :enableNodeUrls="$enableNodeUrls"
            :maxSelections="$maxSelections"
            :homeButtonText="$homeButtonText"
            :indexUrl="$indexUrl"
        />
    @else
        <p class="text-gray-500 dark:text-gray-400">
            {{ __('inspirecms::inspirecms.search.no_results') }}
        </p>
    @endif

</div>