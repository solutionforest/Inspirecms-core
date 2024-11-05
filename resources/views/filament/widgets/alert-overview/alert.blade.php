
@php
    $label = $getLabel();
    $color = $getColor();
    $icon = $getIcon();
@endphp

<div 
    @class([
        'rounded-r-md border-l-4 p-4',
        'border-custom-400 bg-custom-50 dark:bg-custom-400/40' => $color != 'gray',
        'border-gray-400 bg-gray-50 dark:bg-gray-400/40' => $color == 'gray',
    ])
    @style(\Filament\Support\get_color_css_variables(
        $color,
        shades: [50, 200, 400, 700],
    ))>
    <div class="flex">
        <div class="flex-shrink-0">
            <x-filament::icon 
                :icon="$icon" 
                @class([
                    'w-5 h-5',
                    'text-custom-400 dark:text-custom-200/80' => $color != 'gray',
                    'text-gray-400 dark:text-gray-200/80' => $color == 'gray',
                ])
            />
        </div>
        <div class="ml-3">
            <p 
                @class([
                    'text-sm',
                    'text-custom-700 dark:text-custom-400' => $color != 'gray',
                    'text-gray-700 dark:text-gray-400' => $color == 'gray',
                ])
            >
                {{ $label }}
            </p>
        </div>
    </div>
</div>