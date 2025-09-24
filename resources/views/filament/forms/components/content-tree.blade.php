@php
    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributes = $getExtraAttributes();
    $id = $getId();

    $startNode = $getStartNode();
    if ($startNode && $startNode instanceof \Illuminate\Database\Eloquent\Model) {
        $startNode = $startNode->getKey();
    }

    $limit = $getLimits();
    $maxSelections = $limit['max'] ?? null;

    $filters = $getFilter();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }} }"
        {{
            $attributes
                ->merge([
                    'id' => $id,
                ], escape: false)
                ->merge($extraAttributes, escape: false)
        }}
    >
        @livewire('inspirecms::content-tree-node', [
            'startNodeId' => $startNode,
            'filter' => $filters,
            'maxSelections' => $maxSelections,
            'isDisabled' => $isDisabled(),
            'filterByPermission' => $isFilteringByPermission(),
            $applyStateBindingModifiers('wire:model') => $getStatePath(),
        ], key($getLivewireKey()))
    </div>
</x-dynamic-component>