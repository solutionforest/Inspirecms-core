
@php
    $label = $getLabel();
    $color = $getColor();
@endphp

<div class="w-full">
    @if (filled($label))
        <div 
            @class([
                'ring-1 ring-inset rounded-md px-6 py-3 font-medium',
                'inline-flex items-center w-full',
                'bg-custom-100 text-custom-700 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/20' => $color != 'gray',
                'bg-gray-100 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20' => $color == 'gray',
            ])
            @style(\Filament\Support\get_color_css_variables(
                $color,
                shades: [100, 400, 600, 700],
            ))
        >
            {{ $label }}
        </div>
    @endif
</div>