@php
    $state = $getState();
    if (is_array($state)) {
        $state = json_encode($state, JSON_PRETTY_PRINT);
    }
@endphp

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div
        wire:ignore
        x-ignore
        ax-load
        ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-ace-editor', 'riodwanto/filament-ace-editor') }}"
        x-data="aceEditorComponent({
            state: @js($state),
            statePath: @js($getStatePath()),
            placeholder: @js($getPlaceholder() ?? '// No record.'),
            aceUrl: @js($getAceUrl()),
            extensions: @js($getEnabledExtensions()),
            config: @js($getConfig()),
            options: @js($getEditorOptions()),
            darkTheme: @js($getDarkTheme()),
            disableDarkTheme: @js($isDisableDarkTheme()),
        })"
        x-ref="aceCodeEditor"
        style="min-height: {{ $getHeight() }};"
        class="ace-editor"
    >
    </div>
</x-dynamic-component>