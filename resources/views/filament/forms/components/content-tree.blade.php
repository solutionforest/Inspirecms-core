@php
    $startNode = $getStartNode();
    if ($startNode && $startNode instanceof \Illuminate\Database\Eloquent\Model) {
        $startNode = $startNode->getKey();
    }

    $limit = $getLimits();
    $maxSelections = $limit['max'] ?? null;
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }} }">
        <livewire:inspirecms::content-tree-node
            lazy
            :startNodeId="$startNode"
            :filter="$getFilter()"
            :maxSelections="$maxSelections"
            :isDisabled="$isDisabled()"
            :filterByPermission="$isFilteringByPermission()"
            :modelableConfig="['selected'=>'state']"
        />
    </div>
</x-dynamic-component>