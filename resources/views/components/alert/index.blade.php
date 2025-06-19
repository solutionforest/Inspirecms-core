@props(['message', 'type' => 'info', 'size' => 'md', 'icon' => null, 'assetReady' => false])
@use('SolutionForest\InspireCms\InspireCmsConfig')
@php
    if (($fiPanelId = filament()->getCurrentPanel()?->getId()) && $fiPanelId === InspireCmsConfig::getPanelId()) {
        $assetReady = true; // Already loaded in index.css
    }
@endphp
<div {{ 
    $attributes->class([
        'alert-dialog px-4 overflow-x-auto',
        "alert-dialog-{$size}" => in_array($size, ['sm', 'md', 'lg']),
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
    <div class="alert-dialog-body">
        @if ($assetReady === true)
            @if ($icon)
                <div class="alert-dialog-icon-ctn">
                    <x-filament::icon :icon="$icon" />
                </div>
            @endif
            <div class="alert-dialog-content">
                <p>{{ $message }}</p>
            </div>
        @else
            @if ($icon)
                <div class="alert-dialog-icon-ctn" x-show="ready" x-cloak>
                    <x-filament::icon :icon="$icon" />
                </div>
            @endif
            <div class="alert-dialog-content">
                <p x-show="ready" x-cloak>{{ $message }}</p>
                <p x-show="!ready">...</p>
            </div>
        @endif
    </div>
</div>