@props(['node', 'depth' => 0])

<div
    class="ve-layer-node"
    x-data="{ isSelected: selectedBlockId === '{{ $node['id'] }}', isHovered: hoveredBlockId === '{{ $node['id'] }}' }"
>
    <div
        class="flex items-center gap-1 py-1.5 px-2 rounded-lg cursor-pointer transition-colors"
        :class="{
            'bg-primary-50 dark:bg-primary-900/20': isSelected,
            'bg-gray-100 dark:bg-gray-700/50': isHovered && !isSelected,
            'hover:bg-gray-50 dark:hover:bg-gray-700/30': !isSelected && !isHovered
        }"
        style="padding-left: {{ ($depth * 16) + 8 }}px"
        @click="selectNode('{{ $node['id'] }}')"
        @mouseenter="hoverNode('{{ $node['id'] }}')"
        @mouseleave="leaveNode()"
    >
        {{-- Expand/Collapse Toggle --}}
        @if($node['isContainer'] && !empty($node['children']))
            <button
                type="button"
                @click.stop="toggleExpand('{{ $node['id'] }}')"
                class="p-0.5 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
                <x-heroicon-o-chevron-right
                    class="w-3.5 h-3.5 text-gray-400 transition-transform"
                    x-bind:class="isExpanded('{{ $node['id'] }}') ? 'rotate-90' : ''"
                />
            </button>
        @else
            <div class="w-4.5"></div>
        @endif

        {{-- Icon --}}
        <div class="flex-shrink-0">
            <x-dynamic-component
                :component="$node['icon']"
                class="w-4 h-4"
                x-bind:class="isSelected ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400'"
            />
        </div>

        {{-- Label --}}
        <span
            class="flex-1 text-xs font-medium truncate"
            x-bind:class="isSelected ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300'"
        >
            {{ $node['label'] }}
        </span>

        {{-- Quick Actions (visible on hover) --}}
        <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity" x-show="isHovered || isSelected">
            <button
                type="button"
                wire:click="duplicateBlock('{{ $node['id'] }}')"
                @click.stop
                class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors"
                title="Duplicate"
            >
                <x-heroicon-o-document-duplicate class="w-3 h-3" />
            </button>
            <button
                type="button"
                wire:click="deleteBlock('{{ $node['id'] }}')"
                @click.stop
                class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                title="Delete"
            >
                <x-heroicon-o-trash class="w-3 h-3" />
            </button>
        </div>
    </div>

    {{-- Children --}}
    @if($node['isContainer'] && !empty($node['children']))
        <div x-show="isExpanded('{{ $node['id'] }}')" x-collapse>
            @foreach($node['children'] as $child)
                @include('inspirecms::visual-editor.components.layer-node', ['node' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
