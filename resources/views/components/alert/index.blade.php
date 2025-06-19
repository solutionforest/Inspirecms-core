<div {{ 
    $attributes->class([
        'alert-dialog',
        "alert-dialog-{$size}" => in_array($size, ['sm', 'md', 'lg']),
    ])->style(\Filament\Support\get_color_css_variables(
        $color,
        shades: [50, 200, 400, 700, 900],
    ))
}}
    x-data="{}"
    x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-alert', package: 'solution-forest/inspirecms'))]"
>
    <div class="alert-dialog-body">
        <div class="alert-dialog-icon-ctn">
            <x-filament::icon :icon="$icon" />
        </div>
        <div class="alert-dialog-content">
            <p>{{ $message }}</p>
        </div>
    </div>
</div>