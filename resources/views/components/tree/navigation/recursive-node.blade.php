@props([
    'level' => 1,
    'nodeVariable' => 'node',
    'indexVariable' => 'index',
    'parentId' => 'null',
    'actions' => [],
    'maxDepth' => null, // null means unlimited
])

<div>
    <!-- Drop indicator before node -->
    <template x-if="dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }} && dropPosition === 'before'">
        <x-inspirecms::tree.drop-indicator />
    </template>

    <!-- Node -->
    <div :id="'node-' + {{ $nodeVariable }}.id"
        :class="{
            'tree-node': true, 
            'active': selectedNode === {{ $nodeVariable }}.id,
            'search-match': nodeMatchesSearch({{ $nodeVariable }}),
            'dragover': dropPosition === 'inside' && dropTargetParent === {{ $nodeVariable }}.id,
            'dragover-before': dropPosition === 'before' && dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }},
            'dragover-after': dropPosition === 'after' && dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }},
            'beyond-max-depth': !isNodeVisible({{ $nodeVariable }})
        }"
        @click.stop="selectNode({{ $nodeVariable }}.id)"
        draggable="true"
        @dragstart="dragStart($event, {{ $nodeVariable }}.id)"
        @dragover.prevent="dragOver($event, {{ $indexVariable }}, {{ $parentId }})"
        @dragleave="dragLeave($event)"
        @drop.prevent="drop($event, {{ $indexVariable }}, {{ $parentId }})"
        @dragend="dragEnd()"
        tabindex="0"
        role="treeitem"
        :aria-expanded="{{ $nodeVariable }}.children?.length ? {{ $nodeVariable }}.expanded : undefined"
        :aria-selected="selectedNode === {{ $nodeVariable }}.id"
        :aria-level="{{ $level }}"
        @focus="lastFocusedNode = {{ $nodeVariable }}.id"
        {{ $attributes }}>
        
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <!-- Expand/Collapse indicator -->
                <span x-show="{{ $nodeVariable }}.children && {{ $nodeVariable }}.children.length > 0" 
                    :class="{{ $nodeVariable }}.expanded ? 'expanded-indicator' : 'collapsed-indicator'"
                    @click.stop="toggleNode({{ $nodeVariable }}.id)"></span>
                
                <div class="flex-1 flex flex-col">

                    <!-- Node text -->
                    <span class="flex items-center gap-x-1">
                        <x-filament::icon 
                            icon="heroicon-o-eye-slash"
                            alias="inspirecms::invisiable" 
                            class="h-4 w-4" 
                            color="gray" 
                            x-show="!{{ $nodeVariable }}.visible" 
                        />
                        <span x-html="formatNodeText({{ $nodeVariable }})"></span>
                    </span>
                    
                    <!-- Node description -->
                    <span x-show="{{ $nodeVariable }}.description" 
                        class="text-xs text-gray-700 dark:text-gray-500 truncate max-w-[12rem] md:max-w-[7rem] lg:max-w-full"
                        x-html="{{ $nodeVariable }}.description"></span>
                </div>
                
            </div>

            <!-- Node actions -->
            <div class="flex items-center gap-x-2">
                @foreach ($actions as $action)
                    @switch($action)
                        @case('update')
                            <x-filament::icon-button 
                                icon="heroicon-m-pencil" 
                                color="primary"
                                size="xs"
                                @click.stop="$wire.editNode({{ $nodeVariable }}.id)"
                                title="{{ __('Edit') }}"
                            />
                            @break
                        @case('view')
                            <x-filament::icon-button 
                                icon="heroicon-m-eye" 
                                color="gray"
                                size="xs"
                                @click.stop="$wire.viewNode({{ $nodeVariable }}.id)"
                                title="{{ __('View') }}"
                            />
                            @break
                        @case('delete')
                            <x-filament::icon-button 
                                icon="heroicon-m-trash" 
                                color="danger"
                                size="xs"
                                @click.stop="$wire.mountAction('delete', { id: {{ $nodeVariable }}.id })"
                                title="{{ __('Delete') }}"
                            />
                            @break

                    @endswitch
                @endforeach
            </div>
        </div>
    </div>

    <!-- Drop indicator after node -->
    <template x-if="dropTargetIndex === {{ $indexVariable }} && dropTargetParent === {{ $parentId }} && dropPosition === 'after'">
        <x-inspirecms::tree.drop-indicator />
    </template>

    <!-- Children nodes - recursive rendering -->
    <div x-show="{{ $nodeVariable }}.expanded && {{ $nodeVariable }}.children && {{ $nodeVariable }}.children.length > 0" 
        class="tree-children" 
        :key="{{ $nodeVariable }}.id + '-children'">
        
        @php
            $childLevel = $level + 1;
            $shouldRender = $maxDepth === null || $childLevel <= $maxDepth;
            $childNodeVar = "node_level{$childLevel}";
            $childIndexVar = "index_level{$childLevel}";
            $parentIdVar = "{$nodeVariable}.id";
        @endphp
        @if($maxDepth === null || $childLevel <= $maxDepth)
            <template x-for="({{ $childNodeVar }}, {{ $childIndexVar }}) in {{ $nodeVariable }}.children" :key="{{ $childNodeVar }}.id + '-' + {{ $childIndexVar }}">
                <x-inspirecms::tree.navigation.recursive-node 
                    :level="$childLevel"
                    :nodeVariable="$childNodeVar"
                    :indexVariable="$childIndexVar"
                    :parentId="$parentIdVar"
                    :actions="$actions"
                    :maxDepth="$maxDepth"
                />
            </template>
        @endif
    </div>
</div>