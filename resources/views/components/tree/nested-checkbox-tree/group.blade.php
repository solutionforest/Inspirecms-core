@props([
    'key',
    'depth',
    'title',
    'children' => [],
    'groupKey' => null,
    'collapsable' => false,
    'description' => null,
])
<div role="treeitem" {{ $attributes }}>
    
    <x-inspirecms::tree.nested-checkbox-tree.item 
        :key="$key"
        :depth="$depth"
        :title="$title"
        :group-key="$groupKey"
        :collapsable="$collapsable"
        :description="$description"
    />

    @if (count($children) > 0)
        <div class="w-full overflow-hidden" role="group" x-show="!isCollapsed('{{ $key }}')">
            <div class="ms-1 ps-7 relative before:absolute before:top-0 before:start-3 before:w-0.5 before:-ms-px before:h-full before:bg-gray-800/20 before:dark:bg-gray-100/40" role="group">
                @foreach ($children as $index => $item)
                    @php
                        $childDepth = $depth + 1;
                        $childKey = $item['key'] ?? "d{$childDepth}-{$index}";
                    @endphp
                    <x-inspirecms::tree.nested-checkbox-tree.group 
                        :key="$childKey"
                        :depth="$childDepth"
                        :title="$item['title']"
                        :children="$item['children'] ?? []"
                        :description="$item['description'] ?? null"
                        :group-key="$key"
                        :collapsable="$collapsable"
                />
                @endforeach
            </div>
        </div>
    @endif

</div>