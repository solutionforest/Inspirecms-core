@php
    $statePath = $getStatePath();
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    :id="$getId()" 
    :label="$getLabel()" 
    :label-sr-only="$isLabelHidden()" 
    :helper-text="$getHelperText()"    
    :hint="$getHint()" 
    :hint-icon="$getHintIcon()" 
    :required="$isRequired()" 
    :state-path="$getStatePath()"
>
    <x-filament::input.wrapper
      :disabled="$isDisabled"
      :valid="! $errors->has($statePath)"
      :attributes="
          \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
              ->class(['code-editor-textarea relative overflow-hidden'])
      "
    >
        <div class="code-editor-textarea-wrapper-ctn overflow-auto"
            @theme-changed.window="(e) => toggleTheme(e.detail)" 
            x-data="codeEditorFormComponentEnhace({
                state: $wire.{{ $applyStateBindingModifiers('entangle(\'' . $getStatePath() . '\')') }},
                darkTheme: @js($getDarkModeTheme()),
                lightTheme: @js($getLightModeTheme()),
                isReadOnly: @js($isDisabled()),
            })"
            x-load
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-code-editor', 'solution-forest/inspirecms') }}"
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-code-editor', package: 'solution-forest/inspirecms'))]"
        >
            <div wire:ignore 
                x-ref="codeEditor"
                {{
                    $getExtraInputAttributeBag()
                      ->class([
                        'code-editor-textarea-wrapper',
                        'w-full overflow-hidden', 
                      ])
                      ->style([
                        'height:' . ($getMinHeight() ?? '200px'),
                      ])
                }}
            >
            </div>
        </div>
    </x-filament::input.wrapper>
</x-dynamic-component>
