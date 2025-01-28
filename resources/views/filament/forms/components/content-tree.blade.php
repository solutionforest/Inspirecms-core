@php
    $statePath = $getStatePath();
    ray($getFilters());
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    x-data="{ 
        state: $wire.$entangle('{{ $statePath }}'), 
    }"
>
    <livewire:inspirecms::content-tree-node 
        modelable="state" 
        :parent-id="$getStartNode()"
        :filters="$getFilters()"
        :limits="$getLimits()"
    /> 
</x-dynamic-component>