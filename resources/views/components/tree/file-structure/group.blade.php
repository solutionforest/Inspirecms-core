@props([
    'key',
    'depth',
    'title',
    'icon' => null,
    'iconAlias' => null,
    'children' => [],
    'groupKey' => null,
])
<div role="treeitem" {{ $attributes }}>
    
    <x-inspirecms::tree.file-structure.item 
        :key="$key"
        :depth="$depth"
        :title="$title"
        :icon="$icon"
        :icon-alias="$iconAlias"
        :children="$children"
        :group-key="$groupKey"
    />

    @if (count($children) > 0)
        <div class="w-full overflow-hidden" role="group">
            <div class="ms-1 ps-7 relative before:absolute before:top-0 before:start-3 before:w-0.5 before:-ms-px before:h-full before:bg-gray-800/20 dark:before:bg-gray-100/40" role="group">
                @foreach ($children as $index => $item)
                    @php
                        $childDepth = $depth + 1;
                        $childKey = $item['key'] ?? "d{$childDepth}-{$index}";
                    @endphp
                    <x-inspirecms::tree.file-structure.group 
                        :key="$childKey"
                        :depth="$childDepth"
                        :title="$item['title']"
                        :icon="$item['icon']"
                        :icon-alias="$item['iconAlias']"
                        :children="$item['children'] ?? []"
                        :group-key="$key"
                />
                @endforeach
            </div>
        </div>
    @endif

</div>