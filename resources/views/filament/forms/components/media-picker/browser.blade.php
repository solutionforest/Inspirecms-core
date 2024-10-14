@php
    $statePath = $getStatePath();
    $isMultiple = $isMultiple();
    $mimeTypes = $getMimeTypes();
    $filter = [
        'mime_type' => $mimeTypes,
    ];
@endphp
<div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }} }">
    @livewire('inspirecms::media-library', ['isMultiple' => $isMultiple, 'filter' => $filter])
</div>