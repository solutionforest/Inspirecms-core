@props(['key', 'value'])


<x-filament::grid default="3" md="5" {{ $attributes->merge(['class' => 'version-diff-item']) }}>
    <x-filament::grid.column default="1" class="version-diff-item-title font-semibold">{{ $key }}</x-filament::grid.column>
    <x-filament::grid.column default="2" md="4">
        @if (is_array($value))
            <x-inspirecms::version-diff.items :items="$value"/>
        @else
            {!! $value !!}
        @endif
    </x-filament::grid.column>
</x-filament::grid>