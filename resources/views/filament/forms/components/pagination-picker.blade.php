@php
    use Filament\Support\Facades\FilamentView;

    $statePath = $getStatePath();
    $id = $getId();
    $isDisabled = $isDisabled();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <x-filament::input.wrapper
        :disabled="$isDisabled"
        :valid="! $errors->has($statePath)"
        :attributes="
            \Filament\Support\prepare_inherited_attributes($attributes)
                ->merge($getExtraAttributes(), escape: false)
                ->class(['fi-fo-pagination-picker'])
        "
    >
    
        <div
            @if (FilamentView::hasSpaMode())
                {{-- format-ignore-start --}}ax-load="visible || event (ax-modal-opened)"{{-- format-ignore-end --}}
            @else
                ax-load
            @endif
            x-data="{
                state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            }"
            x-ignore
        >
            <x-filament::input
                autocomplete="off"
                :autofocus="$isAutofocused()"
                :disabled="$isDisabled"
                :id="$id"
                :placeholder="$getPlaceholder()"
                type="text"
                x-bind="input"
                :attributes="\Filament\Support\prepare_inherited_attributes($getExtraInputAttributeBag())"
            />
            <div wire:ignore>
                <template x-cloak x-if="state?.length">
                    <div
                        x-on:end.stop="reorderTags($event)"
                        x-sortable
                        data-sortable-animation-duration="{{ $getReorderAnimationDuration() }}"
                        @class([
                            'flex w-full flex-wrap gap-1.5 p-2',
                            'border-t border-t-gray-200 dark:border-t-white/10',
                        ])
                    >
                        <template
                            x-for="(key, index) in state"
                            x-bind:key="`${key}-${index}`"
                            class="hidden"
                        >
                            <x-filament::badge
                                x-bind:x-sortable-item="index"
                                :x-sortable-handle
                            >
                                <span
                                    x-text="key"
                                    class="select-none text-start"
                                ></span>

                            </x-filament::badge>
                        </template>
                    </div>
                </template>
            </div>
        </div>

    </x-filament::input.wrapper>

    {{ $getAction('select') }}
    
</x-dynamic-component>