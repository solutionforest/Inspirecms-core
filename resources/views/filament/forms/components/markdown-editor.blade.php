@php
    use Filament\Support\Facades\FilamentIcon;

    $id = $getId();
    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributeBag = $getExtraAttributeBag();
    $key = $getKey();
    $statePath = $getStatePath();
    $livewireId = $getLivewire()->getId();

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

    $contentPickerAction = $getAction('contentPicker');
    $contentPickerActionJsHandler = $contentPickerAction ? 
        (
            (
                ($contentPickerActionLivewireHandler = $contentPickerAction->getLivewireClickHandler())
                ? '$wire.'.$contentPickerActionLivewireHandler.';'
                : null
            ) 
            ?? $contentPickerAction->getAlpineClickHandler()
        ) : 
        null;

    $mediaPickerAction = $getAction('mediaPicker');
    $mediaPickerActionJsHandler = $mediaPickerAction ? 
        (
            (
                ($mediaPickerActionLivewireHandler = $mediaPickerAction->getLivewireClickHandler())
                ? '$wire.'.$mediaPickerActionLivewireHandler.';'
                : null
            ) 
            ?? $mediaPickerAction->getAlpineClickHandler()
        ) : 
        null;

@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    @if ($isDisabled())
        <div id="{{ $id }}" class="fi-fo-markdown-editor fi-disabled fi-prose">
            {!! str($getState())->sanitizeHtml()->markdown($getCommonMarkOptions(), $getCommonMarkExtensions()) !!}
        </div>
    @else
        <x-filament::input.wrapper
            :valid="! $errors->has($statePath)"
            :attributes="
                \Filament\Support\prepare_inherited_attributes($extraAttributeBag)
                    ->class(['fi-fo-markdown-editor'])
            "
        >
            <div
                aria-labelledby="{{ $id }}-label"
                id="{{ $id }}"
                role="group"
                x-load
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
                                        .callSchemaComponentMethod(
                                            '{{ $key }}',
                                            'saveUploadedFileAttachmentAndGetUrl',
                                        )
                                        .then((url) => {
                                            if (! url) {
                                                return onError()
                                            }

                                            onSuccess(url)
                                        })
                                })
                            },
                            getExtraToolbarButtonUsing: (name) => {
                                
                                if (name === 'contentPicker') {
                                    return {
                                        name: 'contentPicker',
                                        title: 'Content Picker',
                                        icon: @js($trixFieldIconMapper['contentPicker']),
                                        action: (action) => {
                                            @if ($contentPickerActionJsHandler)
                                            {!! $contentPickerActionJsHandler !!}
                                            @endif
                                        },
                                    };
                                }
                                    
                                if (name === 'mediaPicker') {
                                    return {
                                        name: 'mediaPicker',
                                        title: 'Media Picker',
                                        icon: @js($trixFieldIconMapper['mediaPicker']),
                                        action: (action) => {
                                            @if ($mediaPickerActionJsHandler)
                                            {!! $mediaPickerActionJsHandler !!}
                                            @endif
                                        },
                                    };
                                }

                                return null;
                            },
                        })"
                wire:ignore
                x-on:append-custom-links-to-markdown-editor.window="() => {
                    if ($event.detail.key !== @js($key)
                        || $event.detail.livewireId !== @js($livewireId)
                    ) {
                        return;
                    }

                    if (!editor) {
                        return;
                    }

                    var cm = editor.codemirror;

                    const urls = $event.detail.data || '';

                    var startPoint = cm.getCursor('start')
                    var endPoint = cm.getCursor('end')
                    cm.replaceRange(
                        urls,
                        startPoint,
                        endPoint,
                    );
                }"
                {{ $getExtraAlpineAttributeBag() }}
            >
                <textarea x-ref="editor" x-cloak></textarea>
            </div>
        </x-filament::input.wrapper>
    @endif
</x-dynamic-component>