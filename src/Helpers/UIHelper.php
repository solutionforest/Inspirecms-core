<?php

namespace SolutionForest\InspireCms\Helpers;

use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class UIHelper
{
    public static function generateIcon(string $icon, ?string $color = null, int $width = 5): HtmlString
    {
        return new HtmlString(Blade::render(<<<'blade'
            <x-filament::icon
                icon="{{ $icon }}"
                class="{{  $iconClass }}"
                style="{{ $iconStyle }}"
            >
            </x-filament::icon>
        blade, [
            'icon' => $icon,
            'iconClass' => Arr::toCssClasses([
                "w-{$width} h-{$width} ",
                'text-custom-500 dark:text-custom-400' => $color !== 'gray' && filled($color),
                'text-gray-400 dark:text-gray-500' => $color === 'gray',
            ]),
            'iconStyle' => Arr::toCssStyles([
                \Filament\Support\get_color_css_variables($color, shades: [400, 500]) => $color != 'gray' && filled($color),
            ]),
        ]));
    }

    public static function generateBooleanIcon(bool $condition, string $trueIcon = 'heroicon-m-check-circle', string $falseIcon = 'heroicon-m-x-circle', string $trueColor = 'success', string $falseColor = 'danger'): HtmlString
    {
        $color = $condition ? $trueColor : $falseColor;

        return new HtmlString(Blade::render(<<<'blade'
            <x-filament::icon
                icon="{{ $icon }}"
                class="{{  $iconClass }}"
                style="{{ $iconStyle }}"
            >
            </x-filament::icon>
        blade, [
            'icon' => $condition ? $trueIcon : $falseIcon,
            'iconClass' => Arr::toCssClasses([
                'h-5 w-5 ',
                'text-custom-500 dark:text-custom-400' => $color != 'gray',
                'text-gray-400 dark:text-gray-500' => $color == 'gray',
            ]),
            'iconStyle' => Arr::toCssStyles([
                \Filament\Support\get_color_css_variables($color, shades: [400, 500]) => $color != 'gray',
            ]),
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

    public static function generateTextWithIcon(string $text, string $icon, ?string $iconColor = null, IconPosition | string $iconPosition = IconPosition::Before, int $iconWidth = 5): HtmlString
    {
        $data = [
            'text' => $text,
            'icon' => $icon,
            'iconClass' => Arr::toCssClasses([
                "w-{$iconWidth} h-{$iconWidth} ",
                'text-custom-500 dark:text-custom-400' => $iconColor !== 'gray' && filled($iconColor),
                'text-gray-400 dark:text-gray-500' => $iconColor === 'gray',
            ]),
            'iconStyle' => Arr::toCssStyles([
                \Filament\Support\get_color_css_variables($iconColor, shades: [400, 500]) => $iconColor !== 'gray' && filled($iconColor),
            ]),
        ];
        if (is_string($iconPosition)) {
            $iconPosition = IconPosition::tryFrom($iconPosition) ?? IconPosition::Before;
        }
        if ($iconPosition == IconPosition::After) {
            return new HtmlString(Blade::render(<<<'blade'
                <div class="flex items-center gap-2">
                    <span>
                        {{ $text }}
                    </span>
                    <x-filament::icon
                        icon="{{ $icon }}"
                        class="{{  $iconClass }}"
                        style="{{ $iconStyle }}"
                    />
                </div>
            blade, $data));
        }

        return new HtmlString(Blade::render(<<<'blade'
            <div class="flex items-center gap-2">
                <x-filament::icon
                    icon="{{ $icon }}"
                    class="{{  $iconClass }}"
                    style="{{ $iconStyle }}"
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
            'copiedMessage' => __('inspirecms::actions.copy.message'),
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
            'copiedMessage' => __('inspirecms::actions.copy.message'),
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

    /**
     * @param array<string,array{class:[]string}> $attributes Additional HTML attributes for the badge.
     */
    public static function generateTextWithBadge(string $text, $badgeText, string $color = 'primary', ?string $icon = null, $attibutes = []): HtmlString
    {
        return new HtmlString(Blade::render(<<<'blade'
            <div class="flex items-center gap-x-2">
                <span class="{{$textClass}}">
                    {{$text}}
                </span>
                <x-filament::badge
                    color="{{$color}}"
                    icon="{{$icon}}"
                    class="{{$badgeClass}}"
                >
                    {{$badgeText}}
                </x-filament::badge>
            </div>
        blade, [
            'text' => $text,
            'badgeText' => $badgeText,
            'color' => $color,
            'icon' => $icon,
            'textClass' => \Illuminate\Support\Arr::toCssClasses($attibutes['text']['class'] ?? []),
            'badgeClass' => \Illuminate\Support\Arr::toCssClasses($attibutes['badge']['class'] ?? []),
        ]));
    }

    public static function generateCircularImage(string $src, string $alt, array $containerAttributes = [], array $imgAttributes = []): HtmlString
    {
        $filterAndWrap = fn (array $data) => collect($data)->flatten()->filter()->values()->all();

        return new HtmlString(Blade::render(<<<'blade'
            <div class="{{ $ctnClasses }}" style="{{ $ctnStyles }}">
                <x-filament::avatar
                    src="{{$src}}"
                    alt="{{$alt}}"
                    class="{{ $imgClasses }}"
                    style="{{ $imgStyles }}"
                />
            </div>
        blade, [
            'src' => $src,
            'alt' => $alt,
            'ctnClasses' => Arr::toCssClasses($filterAndWrap([
                'bg-gray-200 rounded-full overflow-hidden',
                $containerAttributes['class'] ?? null,
            ])),
            'ctnStyles' => Arr::toCssStyles($filterAndWrap([
                $containerAttributes['style'] ?? null,
            ])),
            'imgClasses' => Arr::toCssClasses($filterAndWrap([
                'rounded-full object-cover',
                $imgAttributes['class'] ?? null,
            ])),
            'imgStyles' => Arr::toCssStyles($filterAndWrap([
                $imgAttributes['style'] ?? null,
            ])),
        ]));
    }

    public static function generateTextWithDescription(string $text, string $description): HtmlString
    {
        return new HtmlString(Blade::render(<<<'blade'
            <div class="flex flex-col">
                <span>
                    {{$text}}
                </span>
                <span class="text-xs text-gray-500">
                    {{$description}}
                </span>
            </div>
        blade, [
            'text' => $text,
            'description' => $description,
        ]));
    }
}
