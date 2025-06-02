<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry" :id="$getId()">
    <div {{ \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
        ->class(['code-editor-textarea relative overflow-hidden']) 
        }}
    >
        <div class="code-editor-textarea-wrapper-ctn overflow-auto"
            @theme-changed.window="(e) => toggleTheme(
                e.detail === 'system' ? window.matchMedia('(prefers-color-scheme: dark)').matches : e.detail === 'dark'
            )" 
            x-data="codeEditorFormComponent({
                state: @js($getState()),
                isReadOnly: true,
                isDarkMode: (Alpine.store('theme') || 'light') === 'system' ? window.matchMedia('(prefers-color-scheme: dark)').matches : (Alpine.store('theme') || 'light') === 'dark',
            })"
            x-load
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-code-editor', 'solution-forest/inspirecms') }}"
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-code-editor', package: 'solution-forest/inspirecms'))]"
        >
            <div wire:ignore 
                x-ref="codeEditor"
                @class([
                    'code-editor-textarea-wrapper',
                    'w-full overflow-hidden', 
                ])
            >
            </div>
        </div>
    </div>
</x-dynamic-component>
