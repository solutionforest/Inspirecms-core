@props([
    'items' => [],
    'autoSelectChildren' => false,
    'collapsable' => false,
    'modelable' => null,
    'max' => null,
    'min' => null,
    'isDisabled' => false,
])

<div role="tree" 
    aria-orientation="vertical" 
    x-data="nestedCheckboxTree({
        max: @js($max),
        min: @js($min),
    })"
    @if (filled($modelable))
        x-model="{{ $modelable }}"
        x-modelable="selected" 
    @endif
    {{ $attributes->class(['py-1 px-1.5']) }}
>
    <div role="group">
        @foreach ($items as $index => $item)
            @php
                $depth = 0;
                $key = $item['key'] ?? "d{$depth}-{$index}";
            @endphp
            <x-inspirecms::tree.nested-checkbox-tree.group 
                :key="$key"
                :depth="$depth"
                :title="$item['title']"
                :description="$item['description'] ?? null"
                :children="$item['children'] ?? []"
                :collapsable="$collapsable"
                :is-disabled="$isDisabled"
            />
        @endforeach
    </div>
</div>