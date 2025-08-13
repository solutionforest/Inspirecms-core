@php
    use Filament\Support\Facades\FilamentView;
    use Filament\Support\Facades\FilamentIcon;
    use Illuminate\View\View;

    $id = $getId();
    $statePath = $getStatePath();
    $trixFieldIconMapper = collect([
        'contentPicker' => FilamentIcon::resolve('inspirecms::content_picker'),
        'mediaPicker' => FilamentIcon::resolve('inspirecms::media_picker'),
    ])
    ->map(function ($value) {
        if ($value instanceof View) {
            return $value->render();
        }
        return $value;
    })
    ->all();

    $mediaLibraryModalId = $getMediaLibraryModalId();
    $selectContentActionName = 'selectContent';

@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    label-tag="div"
>
    @if ($isDisabled())
        <div
            aria-labelledby="{{ $id }}-label"
            id="{{ $id }}"
            role="group"
            class="fi-fo-markdown-editor fi-disabled prose block w-full max-w-none rounded-lg bg-gray-50 px-3 py-3 text-gray-500 shadow-sm ring-1 ring-gray-950/10 dark:prose-invert dark:bg-transparent dark:text-gray-400 dark:ring-white/10 sm:text-sm"
        >
            {!! str($getState())->markdown()->sanitizeHtml() !!}
        </div>
    @else
        <x-filament::input.wrapper
            :valid="! $errors->has($statePath)"
            :attributes="
                \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                    ->class(['fi-fo-markdown-editor max-w-full overflow-hidden font-mono text-base text-gray-950 dark:text-white sm:text-sm'])
            "
        >
            <div
                aria-labelledby="{{ $id }}-label"
                id="{{ $id }}"
                role="group"
                {{-- prettier-ignore-start --}}x-load="visible || event (ax-modal-opened)"
                {{-- prettier-ignore-end --}}
                x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('markdown-editor', 'solution-forest/inspirecms') }}"
                x-data="markdownEditorEnhancedFormComponent({
                            canAttachFiles: @js($hasToolbarButton('attachFiles')),
                            isLiveDebounced: @js($isLiveDebounced()),
                            isLiveOnBlur: @js($isLiveOnBlur()),
                            liveDebounce: @js($getNormalizedLiveDebounce()),
                            maxHeight: @js($getMaxHeight()),
                            minHeight: @js($getMinHeight()),
                            placeholder: @js($getPlaceholder()),
                            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')", isOptimisticallyLive: false) }},
                            toolbarButtons: @js($getToolbarButtons()),
                            translations: @js(__('filament-forms::components.markdown_editor')),
                            uploadFileAttachmentUsing: async (file, onSuccess, onError) => {
                                $wire.upload(`componentFileAttachments.{{ $statePath }}`, file, () => {
                                    $wire
                                        .getFormComponentFileAttachmentUrl('{{ $statePath }}')
                                        .then((url) => {
                                            if (! url) {
                                                return onError()
                                            }

                                            onSuccess(url)
                                        })
                                })
                            },
                            getExtraToolbarButtonsUsing: (toolbarButtons) => {
                                
                                let extraButtons = [];

                                if (toolbarButtons) {
                                    if (toolbarButtons.includes('contentPicker')) {
                                        extraButtons.push({
                                            name: 'contentPicker',
                                            title: 'Content Picker',
                                            icon: @js($trixFieldIconMapper['contentPicker']),
                                            action: (action) => {
                                                $wire.mountFormComponentAction(@js($statePath), @js($selectContentActionName))
                                            },
                                        });
                                    }
                                    if (toolbarButtons.includes('mediaPicker')) {
                                        extraButtons.push({
                                            name: 'mediaPicker',
                                            title: 'Media Picker',
                                            icon: @js($trixFieldIconMapper['mediaPicker']),
                                            action: (action) => {
                                                $dispatch('open-modal', { id: @js($mediaLibraryModalId), statePath: @js($statePath) })
                                                $dispatch('media-picker-setup', { statePath: @js($statePath), config: @js($getMediaLibraryModalConfig([])) });
                                            },
                                        });
                                    }
                                }
                                if (extraButtons.length > 0) {
                                    extraButtons.push('|'); // Add a separator
                                }
                                return extraButtons;
                            },
                        })"
                wire:ignore
                @if ($hasToolbarButton('mediaPicker'))
                    x-on:media-picker-trix-appead.window="() => {
                        if (editor && $event?.detail?.statePath === @js($statePath)) {
                            var cm = editor.codemirror;

                            var startPoint = cm.getCursor('start')
                            var endPoint = cm.getCursor('end')
                            cm.replaceRange(
                                $event.detail.data,
                                startPoint,
                                endPoint,
                            );
                        }
                    }"
                    x-on:close-modal.window="
                        if ($event.detail.statePath != @js($statePath)) {
                            return;
                        }

                        if ($event.detail.id === @js($mediaLibraryModalId) && ($event.detail?.save ?? false)) {
                            $wire.dispatchFormEvent(
                                'mediaPicker::select', 
                                '{{ $statePath }}', 
                                $event.detail?.data?.selected ?? [],
                            )
                        }
                    "
                @endif
                @if ($hasToolbarButton('contentPicker'))
                    x-on:content-picker-trix-appead.window="() => {
                        if (editor && $event?.detail?.statePath === @js($statePath)) {
                            var cm = editor.codemirror;

                            var startPoint = cm.getCursor('start')
                            var endPoint = cm.getCursor('end')
                            cm.replaceRange(
                                $event.detail.data,
                                startPoint,
                                endPoint,
                            );
                        }
                    }"
                @endif
                {{ $getExtraAlpineAttributeBag() }}
            >
                <textarea x-ref="editor" class="hidden"></textarea>
            </div>
        </x-filament::input.wrapper>
    @endif
</x-dynamic-component>