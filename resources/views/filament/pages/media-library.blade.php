@php
    $isMultiple = false;
    $formConfig = [
        'upload' => [
            'collap_open' => true,
        ],
    ];

@endphp
<x-filament-panels::page>
    <livewire:inspirecms-support::media-library
        :isMultiple="$isMultiple"
        :formConfig="$formConfig"
    />
</x-filament-panels::page>
