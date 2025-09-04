@php
    use Filament\Support\Facades\FilamentIcon;
    use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\FilterCollection;

    $id = $getId();
    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributeBag = $getExtraAttributeBag();
    $key = $getKey();
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

    $contentPickerModalId = "{$key}-content-picker";
    $mediaLibraryModalId = $getMediaLibraryModalId();

    $contentTreeNodeIdentifier = "{$key}-content-tree-node";

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
                                            $dispatch('open-modal', { id: @js($contentPickerModalId) })
                                        },
                                    };
                                }
                                    
                                if (name === 'mediaPicker') {
                                    return {
                                        name: 'mediaPicker',
                                        title: 'Media Picker',
                                        icon: @js($trixFieldIconMapper['mediaPicker']),
                                        action: (action) => {
                                            $dispatch('open-modal', { id: @js($mediaLibraryModalId), key: @js($key), statePath: @js($statePath) })
                                            $dispatch('media-picker-setup', { key: @js($key), statePath: @js($statePath), config: @js($getMediaLibraryModalConfig([])) });
                                        },
                                    };
                                }

                                return null;
                            },
                        })"
                wire:ignore
                @if ($hasToolbarButton('mediaPicker'))
                    x-on:update-media-picker-selection.window="
                        if ($event.detail.key !== @js($key)) {
                            return;
                        }
                        if (!editor) {
                            return;
                        }

                        if ($event.detail.id === @js($mediaLibraryModalId) && ($event.detail?.save ?? false)) {
                            $wire
                                .callSchemaComponentMethod(
                                    @js($key),
                                    'appendFromMediaLibrary',
                                    { ids: $event.detail?.data?.selected || [] },
                                )
                                .then((urls) => {
                                    if (urls && urls.length > 0) {
                                        var cm = editor.codemirror;
                                        
                                        var startPoint = cm.getCursor('start')
                                        var endPoint = cm.getCursor('end')
                                        cm.replaceRange(
                                            urls,
                                            startPoint,
                                            endPoint,
                                        );
                                    }
                                });
                        }
                    "
                @endif
                @if ($hasToolbarButton('contentPicker'))
                    x-on:update-content-picker-selection.window="
                        if ($event.detail.key !== @js($key)) {
                            return;
                        }
                        if (!editor) {
                            return;
                        }
                        $wire
                            .callSchemaComponentMethod(
                                @js($key),
                                'appendFromContentPicker',
                                { ids: $event.detail?.data || [] },
                            )
                            .then((urls) => {
                                if (urls && urls.length > 0) {
                                    var cm = editor.codemirror;
                                    
                                    var startPoint = cm.getCursor('start')
                                    var endPoint = cm.getCursor('end')
                                    cm.replaceRange(
                                        urls,
                                        startPoint,
                                        endPoint,
                                    );
                                }
                            });
                    "
                @endif
                {{ $getExtraAlpineAttributeBag() }}
            >
                <textarea x-ref="editor" x-cloak></textarea>
            </div>
        </x-filament::input.wrapper>

        @if ($hasToolbarButton('contentPicker'))
            <x-filament::modal 
                id="{{ $contentPickerModalId }}"
                slide-over
                sticky-header
                sticky-footer
                footer-actions-alignment="end"
                display-classes="block"
                x-init="() => {
                    this.selectedContent = [];
                }"
                x-on:x-modal-opened.window="
                    if ($event?.detail?.id === '{{ $contentPickerModalId }}') {
                        this.selectedContent = [];
                        $dispatch('content-tree-node:reset-selected', { key: '{{ $contentTreeNodeIdentifier }}' });
                    }
                "
            >
                <div>
                    <livewire:inspirecms::content-tree-node 
                        lazy
                        :modelable="'selectedContent'"
                        :isDisabled="false"
                        :filterByPermission="false"
                        :customId="$contentTreeNodeIdentifier"
                    />
                </div>

                <x-slot name="footerActions">
                    <x-filament::button type="button" x-on:click="() => {
                        $dispatch(
                            'update-content-picker-selection',
                            { 
                                id: '{{ $contentPickerModalId }}', 
                                key: '{{ $key }}', 
                                data: this.selectedContent || [] 
                            }
                        );
                        close();
                    }">
                        {{ __('filament-actions::modal.actions.submit.label') }}
                    </x-filament::button>
                    <x-filament::button color="gray" x-on:click="close()">
                        {{ __('filament-actions::modal.actions.cancel.label') }}
                    </x-filament::button>
                </x-slot>
                
            </x-filament::modal>
        @endif

    @endif
</x-dynamic-component>