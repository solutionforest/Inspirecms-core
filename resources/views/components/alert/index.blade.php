@props([
    'message', 
    'type' => 'info', 
    'size' => 'md', 
    'color' => 'gray', 
    'icon' => null, 
    'assetReady' => false,
])
@use('SolutionForest\InspireCms\InspireCmsConfig')
@php
    if (($fiPanelId = filament()->getCurrentPanel()?->getId()) && $fiPanelId === InspireCmsConfig::getPanelId()) {
        $assetReady = true; // Already loaded in index.css
    }
@endphp
<div {{ 
    $attributes->class([
        'alert-dialog px-4 overflow-x-auto bg-custom-50 dark:bg-custom-900',
        "alert-dialog-{$size}" => in_array($size, ['sm', 'md', 'lg']),
        'py-2' => $size == 'sm',
        'py-3' => $size == 'md',
        'py-4' => $size == 'lg',
    ])->style(\Filament\Support\get_color_css_variables(
        $color,
        shades: [50, 200, 400, 700, 900],
    ))
}}
    x-data="{ ready: @js($assetReady) }"
    x-load-css="[
        @js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-alert', package: 'solution-forest/inspirecms'))
    ]"
    data-dispatch="alert-loaded"
    x-on:alert-loaded-css.window="ready = true"
>
    <div class="alert-dialog-body flex items-center gap-x-1">
        @if ($icon)
            <div class="alert-dialog-icon-ctn flex-shrink-0" 
                @unless ($assetReady) x-show="ready" x-cloak @endunless
            >
                <x-filament::icon :icon="$icon" class="w-6 h-6 text-custom-400 dark:text-custom-200" />
            </div>
        @endif
        <div class="alert-dialog-content text-sm inline-block whitespace-nowrap text-custom-700 dark:text-custom-400">
            @if ($assetReady === true)
                <p>{{ $message }}</p>
            @else
                <p x-show="ready" x-cloak>{{ $message }}</p>
                <p x-show="!ready">...</p>
            @endif
        </div>
    </div>
</div>