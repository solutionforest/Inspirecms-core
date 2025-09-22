@php
    $startNode = $getStartNode();
    if ($startNode && $startNode instanceof \Illuminate\Database\Eloquent\Model) {
        $startNode = $startNode->getKey();
    }

    $limit = $getLimits();
    $maxSelections = $limit['max'] ?? null;
    $multipleSelection = $maxSelections > 1;
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
            :multipleSelection="$multipleSelection"
            :maxSelections="$maxSelections"
            :isDisabled="$isDisabled()"
            :filterByPermission="$isFilteringByPermission()"
            :modelableConfig="['selected'=>'state']"
        />
    </div>
</x-dynamic-component>