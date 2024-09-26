<?php

namespace SolutionForest\InspireCms\Helpers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class UIHelper
{
    public static function getBooleanIconPlaceholder(bool $condition, string $trueIcon = 'heroicon-m-check-circle', string $falseIcon = 'heroicon-m-x-circle', string $trueColor = 'success', string $falseColor = 'danger'): HtmlString
    {
        return new HtmlString(Blade::render(<<<'blade'
            <x-filament::icon
                icon="{{$icon}}"
                class="h-5 w-5 text-custom-500 dark:text-custom-400"
                style="{{$iconStyle}}"
            >
            </x-filament::icon>
        blade, [
            'icon' => $condition ? $trueIcon : $falseIcon,
            'iconStyle' => \Filament\Support\get_color_css_variables(
                $condition ? $trueColor : $falseColor,
                shades: [400, 500],
            ),
        ]));
    }
}
