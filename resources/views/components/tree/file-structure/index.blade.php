@props([
    'items' => [],
])
@php
    //todo: js
@endphp

<div role="tree" 
    aria-orientation="vertical" 
    {{ $attributes->class(['py-1 px-1.5']) }}
>

    <div role="group">
        @foreach ($items as $index => $item)
            @php
                $depth = 0;
                $key = $item['key'] ?? "d{$depth}-{$index}";
            @endphp
            <x-inspirecms::tree.file-structure.group 
                :key="$key"
                :depth="$depth"
                :title="$item['title']"
                :icon="$item['icon']"
                :icon-alias="$item['iconAlias']"
                :children="$item['children'] ?? []"
            />
        @endforeach
    </div>
</div>