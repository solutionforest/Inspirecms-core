<div {{ 
    $attributes->class([
        'alert-dialog px-4 bg-custom-50 dark:bg-custom-900',
        "alert-dialog-{$size}" => in_array($size, ['sm', 'md', 'lg']),
        'py-2' => $size === 'sm',
        'py-3' => $size === 'md',
        'py-4' => $size === 'lg',
    ])->style(\Filament\Support\get_color_css_variables(
        $color,
        shades: [50, 200, 400, 700, 900],
    ))
}}
    x-data="{}"
    x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-alert', package: 'solution-forest/inspirecms'))]"
>
    <div class="alert-dialog-body flex items-center h-full truncate">
        <div class="alert-dialog-icon-ctn">
            <x-filament::icon :icon="$icon" class="w-6 h-6 text-custom-400 text-custom-200" />
        </div>
        <div class="alert-dialog-content-ctn ml-4 text-sm text-custom-700 dark:text-custom-400">
            <p>
                {{ $message }}
            </p>
        </div>
    </div>
</div>