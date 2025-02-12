@php
    $statePath = $getStatePath();
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    x-data="{ 
        state: $wire.$entangle('{{ $statePath }}'), 
    }"
>
    @livewire('inspirecms::content-tree-node', [
        'modelable' => 'state',
        'startNode' => $getStartNode(),
        'filter' => $getFilter(),
        'limits' => $getLimits(),
        'isDisabled' => $isDisabled(),
        'filterByPermission' => $isFilteringByPermission(),
    ])
</x-dynamic-component>