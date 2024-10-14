@php
    $statePath = $getStatePath();
    $id = $getId();
    $isDisabled = $isDisabled();

    $stateForDisplay = $getFormattedStateForDisplay();

    $selectAction = $getAction('select');
    $clearAction = $getAction('clear');

@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['fi-fo-media-picker'])
        }}
    >
        <ul>
            <div
            >
            <x-filament::grid 
                :default="2"
                :lg="5"
                :md="3"
                class="gap-3"
            >
                @foreach ($stateForDisplay as $key => $arr)
                    <li
                        wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $key }}.item"
                        class="fi-fo-media-picker-item flex"
                    >
                        <img src="{{$arr['url']}}" alt="{{$arr['title']}}">
                    </li>
                @endforeach
            </x-filament::grid>
        </ul>
    </div>

    <div class="flex gap-2">
        @if (! $isDisabled)
            {{ $clearAction }}
            {{ $selectAction }}
        @endif
    </div>
    
</x-dynamic-component>