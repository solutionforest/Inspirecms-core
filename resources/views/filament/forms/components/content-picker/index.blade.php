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

    $contentTreeModalId = $getContentTreeModalId();
    $contentTreeModalConfig = $getContentTreeModalConfig();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div 
        x-data="{ 
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            openModal() {
                $dispatch('x-content-picker-modal-setup', { 
                    selected: this.state || [],
                    key: @js($key),
                    config: @js($contentTreeModalConfig),
                    modalId: @js($contentTreeModalId),
                    openModal: true,
                });
            },
            clear() {
                $wire
                    .callSchemaComponentMethod(
                        @js($key),
                        'clearSelected',
                        {}
                    );
            }
        }"
        x-on:update-content-picker-selection.window="
            if ($event.detail.key !== @js($key) || $event.detail.id !== @js($contentTreeModalId)) {
                return;
            }

            console.log('Updating selection (contentpicker)', $event.detail || []);

            $wire
                .callSchemaComponentMethod(
                    @js($key),
                    'updateSelected',
                    { ids: $event.detail?.data || [] },
                );
        "
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
                                @if ($isDisabled && ($moveUpAction || $moveDownAction))
                                    <!-- Item Controls -->
                                    <div class="flex gap-x-1">
                                        @if ($moveUpAction)
                                            <li>{{ $moveUpAction(['item' => $key, 'disabled' => $loop->first]) }}</li>
                                        @endif
                                        @if ($moveDownAction)
                                            <li>{{ $moveDownAction(['item' => $key, 'disabled' => $loop->last]) }}</li>
                                        @endif
                                    </div>
                                @endif
                                <div class="flex-1 ring-1 rounded-md px-2 py-3 shadow-xs ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                                    {{ $label }}
                                </div>
                                @if (! $isDisabled && $deleteAction)
                                    <ul class="flex gap-x-1">
                                        <li>{{ $deleteAction(['item' => $key]) }}</li>
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
                    <x-filament::button color="gray" x-on:click="clear">
                        {{ __('inspirecms::buttons.clear.label') }}
                    </x-filament::button>
                    <x-filament::button x-on:click="openModal()">
                        {{ __('inspirecms::buttons.select.label') }}
                    </x-filament::button>
                @endif
            </div>
        </div>

    </div>
    
</x-dynamic-component>