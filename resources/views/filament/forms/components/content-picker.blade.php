@php
    $statePath = $getStatePath();
    $id = $getId();
    $key = $getKey();
    $isDisabled = $isDisabled();

    $stateForDisplay = $getFormattedStateForDisplay();

    $moveUpAction = $getAction('moveUp');
    $moveDownAction = $getAction('moveDown');
    $deleteAction = $getAction('delete');

    $isMultiple = $isMultiple();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class([
                    'fi-fo-content-picker',
                ])
        }}
    >
        <ul>
            @foreach ($stateForDisplay as $itemKey => $label)
                <li
                    wire:ignore.self
                    wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $itemKey }}.item"
                    class="fi-fo-pagination-picker-item flex"
                >
                    <div class="flex-1 mb-2 inline-flex items-center justify-start gap-x-3">
                        @if ($isMultiple)
                            @if (! $isDisabled && ($moveUpAction || $moveDownAction))
                                <!-- Item Controls -->
                                <ul class="flex gap-x-1">
                                    @if ($moveUpAction)
                                        <li>{{ $moveUpAction(['item' => $itemKey, 'disabled' => $loop->first]) }}</li>
                                    @endif
                                    @if ($moveDownAction)
                                        <li>{{ $moveDownAction(['item' => $itemKey, 'disabled' => $loop->last]) }}</li>
                                    @endif
                                </ul>
                            @endif
                            <div class="flex-1 ring-1 rounded-md px-2 py-3 shadow-xs ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                                {{ $label }}
                            </div>
                            @if (! $isDisabled && $deleteAction)
                                <ul class="flex gap-x-1">
                                    <li>{{ $deleteAction(['item' => $itemKey]) }}</li>
                                </ul>
                            @endif
                        @else
                            <div class="flex-1 ring-1 rounded-md px-2 py-3 shadow-xs ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                                {{ $label }}
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        <div @class([
            'flex gap-2' => $isMultiple,
        ])>
            @if (! $isDisabled)
                {{ $getAction('clear') }}
                {{ $getAction('select') }}
            @endif
        </div>
    </div>
    
</x-dynamic-component>