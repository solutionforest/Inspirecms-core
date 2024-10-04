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

    public static function getIconButtonPlaceholder(string $icon, string $color = 'primary', string $size = 'md', string $class = '', string $url = ''): HtmlString
    {
        return new HtmlString(Blade::render(<<<'blade'
            <x-filament::button
                color="{{$color}}"
                size="{{$size}}"
                class="{{$class}}"
                @if (filled($url))
                    tag="a"
                    href="{{$url}}"
                @endif
            >
                <x-filament::icon
                    icon="{{$icon}}"
                    class="h-5 w-5"
                >
                </x-filament::icon>
            </x-filament::button>
        blade, [
            'color' => $color,
            'size' => $size,
            'class' => $class,
            'icon' => $icon,
            'url' => $url,
        ]));
    }

    public static function getInlineTextWithIconButtonPlaceholder(string $text, string $icon, string $color = 'primary', string $size = 'md', string $class = '', string $url = ''): HtmlString
    {
        return new HtmlString(Blade::render(<<<'blade'
            <div class="flex items-center space-x-2 gap-2">
                <span class="flex-1">{{$text}}</span>
                <x-filament::button
                    color="{{$color}}"
                    size="{{$size}}"
                    class="{{$class}}"
                    tag="a"
                    href="{{$url}}"
                >
                    <x-filament::icon
                        icon="{{$icon}}"
                        class="h-5 w-5"
                    >
                    </x-filament::icon>
                </x-filament::button>
            </div>
        blade, [
            'text' => $text,
            'color' => $color,
            'size' => $size,
            'class' => $class,
            'icon' => $icon,
            'url' => $url,
        ]));
    }
}
