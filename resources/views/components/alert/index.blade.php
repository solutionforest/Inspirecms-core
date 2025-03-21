<div {{ 
    $attributes->class([
        'alert-dialog',
        'border-custom-400 bg-custom-50 dark:bg-custom-900/95' => $color != 'gray',
        'border-gray-400 bg-gray-50 dark:bg-gray-900/95' => $color == 'gray',
    ])->style(\Filament\Support\get_color_css_variables(
        $color,
        shades: [50, 200, 400, 700, 900],
    ))
}}>
    <div class="flex">
        <div class="flex-shrink-0">
            <x-filament::icon 
                :icon="$icon" 
                @class([
                    'alert-dialog-icon',
                    'text-custom-400 dark:text-custom-200/80' => $color != 'gray',
                    'text-gray-400 dark:text-gray-200/80' => $color == 'gray',
                ])
            />
        </div>
        <div class="alert-dialog-content-ctn">
            <p @class([
                'alert-dialog-content',
                'text-custom-700 dark:text-custom-400' => $color != 'gray',
                'text-gray-700 dark:text-gray-400' => $color == 'gray',
            ])>
                {{ $message }}
            </p>
        </div>
    </div>
</div>