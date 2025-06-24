@php
    $maxDepth = 10;
    $actions = $this->getAvailableActions();
@endphp
<div x-data="TreeView({
    data: $wire.entangle('nodes'),
    maxDepth: @js($maxDepth),
    maxVisibleDepth: @js($maxDepth),
})">
    <div class="tree-view-container">

        <x-filament::input.wrapper
            prefix-icon="heroicon-m-magnifying-glass"
            prefix-icon-alias="panels::global-search.field"
            inline-prefix
            class="search-container mb-4"
        >
            <x-filament::input
                autocomplete="off"
                inline-prefix
                maxlength="1000"
                type="search"
                x-model="searchQuery" 
                placeholder="Search nodes..."
                class="search-input" 
            />
        </x-filament::input.wrapper>

        <div class="flex items-center gap-x-2 mb-4">
            <x-filament::button
                color="gray"
                size="sm"
                @click="expandAll()"
            >
                Expand All
            </x-filament::button>
            <x-filament::button
                color="gray"
                size="sm"
                @click="collapseAll()"
            >
                Collapse All
            </x-filament::button>
            <x-filament::button
                color="primary"
                size="sm"
                wire:click="save"
            >
                Save Changes
            </x-filament::button>
            <x-filament::icon-button
                title="Reset"
                icon="heroicon-m-arrow-path"
                color="gray"
                size="sm"
                wire:click="resetTree"
            ></x-filament::icon-button>
        </div>
    
        <div class="min-h-[200px]">
            <div class="relative tree-container">
                <template x-for="(node, index) in treeData" :key="node.id + '-' + index">
                    <x-inspirecms-support::tree-node.recursive-node 
                        :level="1" 
                        nodeVariable="node"
                        indexVariable="index"
                        parentId="null"
                        :maxDepth="$maxDepth"
                        :actions="$actions"
                    />
                </template>
            </div>
        </div>
    </div>
    <x-filament-actions::modals />
</div>