<?php

namespace SolutionForest\InspireCms\Helpers;

use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class UIHelper
{
    public static function generateBooleanIcon(bool $condition, string $trueIcon = 'heroicon-m-check-circle', string $falseIcon = 'heroicon-m-x-circle', string $trueColor = 'success', string $falseColor = 'danger'): HtmlString
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

    public static function generateIconButton(string $icon, string $color = 'primary', string $size = 'md', string $class = '', string $url = ''): HtmlString
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

    public static function generateTextWithIcon(string $text, string $icon, string $color = 'primary', IconPosition | string $iconPosition = IconPosition::Before): HtmlString
    {
        $data = [
            'text' => $text,
            'icon' => $icon,
            'iconStyle' => \Filament\Support\get_color_css_variables(
                $color,
                shades: [400, 500],
            ),
        ];
        if (is_string($iconPosition)) {
            $iconPosition = IconPosition::tryFrom($iconPosition) ?? IconPosition::Before;
        }
        if ($iconPosition == IconPosition::After) {
            return new HtmlString(Blade::render(<<<'blade'
                <div class="flex gap-2">
                    <span>
                        {{ $text }}
                    </span>
                    <x-filament::icon
                        icon="{{$icon}}"
                        class="h-5 w-5 text-custom-500 dark:text-custom-400"
                        style="{{$iconStyle}}"
                    />
                </div>
            blade, $data));
        }

        return new HtmlString(Blade::render(<<<'blade'
            <div class="flex gap-2">
                <x-filament::icon
                    icon="{{$icon}}"
                    class="h-5 w-5 text-custom-500 dark:text-custom-400"
                    style="{{$iconStyle}}"
                />
                <span>
                    {{ $text }}
                </span>
            </div>
        blade, $data));
    }

    public static function generateTextWithIconButton(string $text, string $icon, string $color = 'primary', string $size = 'md', string $class = '', string $url = '', string $linkTarget = ''): HtmlString
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
                    target="{{$linkTarget}}"
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
            'linkTarget' => $linkTarget,
        ]));
    }

    public static function generateCopyableTextWithIconButton(string $text, string $icon, string $color = 'primary', string $size = 'md', string $class = '', string $url = '', string $linkTarget = ''): HtmlString
    {
        return new HtmlString(Blade::render(<<<'blade'
            <div class="flex items-center space-x-2 gap-2">
                <div class="flex-1 cursor-pointer">
                    <span x-on:click="
                            window.navigator.clipboard.writeText('{{$text}}')
                                $tooltip('{{$copiedMessage}}', {
                                theme: $store.theme,
                                timeout: 2000,
                            })
                    ">
                        {{$text}}
                    </span>
                </div>
                <x-filament::button
                    color="{{$color}}"
                    size="{{$size}}"
                    class="{{$class}}"
                    tag="a"
                    href="{{$url}}"
                    target="{{$linkTarget}}"
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
            'linkTarget' => $linkTarget,
            'copiedMessage' => __('inspirecms::inspirecms.copied'),
        ]));
    }

    public static function generateCopyableText(string $text): HtmlString
    {
        return new HtmlString(Blade::render(<<<'blade'
            <div class="cursor-pointer">
                <span x-on:click="
                        window.navigator.clipboard.writeText('{{$text}}')
                        $tooltip('{{$copiedMessage}}', {
                            theme: $store.theme,
                            timeout: 2000,
                        })
                ">
                    {{$text}}
                </span>
            </div>
        blade, [
            'text' => $text,
            'copiedMessage' => __('inspirecms::inspirecms.copied'),
        ]));
    }

    public static function generateBadge(string $text, string $color = 'primary', ?string $icon = null): HtmlString
    {
        return new HtmlString(Blade::render(<<<'blade'
            <x-filament::badge
                color="{{$color}}"
                icon="{{$icon}}"
            >
                {{$text}}
            </x-filament::badge>
        blade, [
            'text' => $text,
            'color' => $color,
            'icon' => $icon,
        ]));
    }
}
