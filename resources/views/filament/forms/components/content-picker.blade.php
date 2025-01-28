@php
    $statePath = $getStatePath();
    $id = $getId();
    $isDisabled = $isDisabled();

    $stateForDisplay = $getFormattedStateForDisplay();

    $selectAction = $getAction('select');
    $clearAction = $getAction('clear');

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
            @foreach ($stateForDisplay as $key => $label)
                <li
                    wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $key }}.item"
                    class="fi-fo-pagination-picker-item flex"
                >
                    <div class="flex-1 mb-2 inline-flex items-center justify-start gap-x-3">
                        @if ($isMultiple)
                            <ul class="flex gap-x-1">
                                @if (! $isDisabled)
                                    <li>{{ $moveUpAction(['item' => $key, 'disabled' => $loop->first]) }}</li>
                                    <li>{{ $moveDownAction(['item' => $key, 'disabled' => $loop->last]) }}</li>
                                @endif
                            </ul>
                            <div class="flex-1 ring-1 rounded-md px-2 py-3 shadow-sm ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                                {{ $label }}
                            </div>
                            <ul class="flex gap-x-1">
                                @if (! $isDisabled)
                                    <li>{{ $deleteAction(['item' => $key]) }}</li>
                                @endif
                            </ul>
                        @else
                            <div class="flex-1 ring-1 rounded-md px-2 py-3 shadow-sm ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
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
                {{ $clearAction }}
                {{ $selectAction }}
            @endif
        </div>
    </div>
    
</x-dynamic-component>