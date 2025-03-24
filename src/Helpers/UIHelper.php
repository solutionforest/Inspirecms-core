<?php

namespace SolutionForest\InspireCms\Helpers;

use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;
use Stringable;

/**
 * @phpstan-type AttributeArray array<string,string>
 */
class UIHelper
{
    /**
     * @param  AttributeArray  $attributes
     */
    public static function generateIcon(?string $icon, ?string $color = null, int $width = 5, array $attributes = []): HtmlString
    {
        if (! filled($icon)) {
            return new HtmlString('');
        }

        $bindings = ['icon'];
        $props = [];

        $data = [
            ...compact($bindings),
            ...$props,
        ];

        $attributes = static::mergeAttributes($attributes, [
            'class' => Arr::toCssClasses([
                "w-{$width} h-{$width} ",
                'text-custom-500 dark:text-custom-400' => $color !== 'gray' && filled($color),
                'text-gray-400 dark:text-gray-500' => $color === 'gray',
            ]),
            'style' => Arr::toCssStyles([
                \Filament\Support\get_color_css_variables($color, shades: [400, 500]) => $color != 'gray' && filled($color),
            ]),
        ]);

        $template = static::buildComponentTemplate(componentName: 'filament::icon', bindings: $bindings, props: $props, attributes: $attributes);

        return str(Blade::render($template, $data))->toHtmlString();
    }

    /**
     * @param  AttributeArray  $attributes
     */
    public static function generateBooleanIcon(bool $condition, ?string $trueIcon = 'heroicon-m-check-circle', ?string $falseIcon = 'heroicon-m-x-circle', string $trueColor = 'success', string $falseColor = 'danger', array $attributes = []): HtmlString
    {
        return static::generateIcon(
            icon: $condition ? ($trueIcon ?? 'heroicon-m-check-circle') : ($falseIcon ?? 'heroicon-m-x-circle'),
            color: $condition ? $trueColor : $falseColor,
            width: 5,
            attributes: $attributes,
        );
    }

    /**
     * @param  array{btn:?AttributeArray,icon:?AttributeArray}  $attributes
     */
    public static function generateIconButton(?string $icon, string $color = 'primary', string $size = 'md', string $url = '', array $attributes = []): HtmlString
    {
        $bindings = ['color', 'size'];
        $props['tag'] = 'button';
        if (filled($url)) {
            $props['href'] = $url;
            $props['tag'] = 'a';
        }

        $data = [
            ...compact($bindings),
            ...$props,
        ];

        $template = static::buildComponentTemplate(
            componentName: 'filament::button',
            bindings: $bindings,
            props: $props,
            attributes: $attributes['btn'] ?? [],
            slot: filled($icon) ? static::generateIcon(icon: $icon, color: $color, width: 5, attributes: $attributes['icon'] ?? []) : '',
        );

        return str(Blade::render($template, $data))->toHtmlString();
    }

    /**
     * @param  array{text:?AttributeArray,icon:?AttributeArray}  $attributes
     */
    public static function generateTextWithIcon(string $text, ?string $icon, ?string $iconColor = null, IconPosition | string $iconPosition = IconPosition::Before, int $iconWidth = 5, array $attributes = []): HtmlString
    {
        if (is_string($iconPosition)) {
            $iconPosition = IconPosition::tryFrom($iconPosition) ?? IconPosition::Before;
        }
        $iconHtml = static::generateIcon(icon: $icon, color: $iconColor, width: $iconWidth, attributes: $attributes['icon'] ?? []);
        $haveIcon = filled(strval($iconHtml));

        return str(static::wrapWithHtmlTag($text, 'span', $attributes['text'] ?? []))
            ->when(
                $haveIcon,
                fn ($str) => $str
                    ->when(
                        $iconPosition == IconPosition::After,
                        fn ($str) => $str->finish($iconHtml->toHtml()),
                        fn ($str) => $str->prepend($iconHtml->toHtml())
                    )
            )
            ->wrap('<div class="flex items-center gap-2">', '</div>')
            ->toHtmlString();
    }

    /**
     * @param  array{text:?AttributeArray,btn:?AttributeArray}  $attributes
     */
    public static function generateTextWithIconButton(string $text, ?string $icon, string $color = 'primary', string $size = 'md', string $url = '', string $linkTarget = '', array $attributes = []): HtmlString
    {
        $btnAttributes = static::mergeAttributes($attributes['btn'] ?? [], filled($linkTarget) ? ['target' => $linkTarget] : []);
        $textAttributes = static::mergeAttributes($attributes['text'] ?? [], ['class' => 'flex-1']);

        return str(static::wrapWithHtmlTag($text, 'span', $textAttributes))
            ->finish(static::generateIconButton(icon: $icon, color: $color, size: $size, url: $url, attributes: ['btn' => $btnAttributes]))
            ->wrap('<div class="flex items-center gap-2">', '</div>')
            ->toHtmlString();
    }

    /**
     * @param  array{text:?AttributeArray,btn:?AttributeArray}  $attributes
     */
    public static function generateCopyableTextWithIconButton(string $text, ?string $icon, string $color = 'primary', string $size = 'md', string $url = '', string $linkTarget = '', array $attributes = []): HtmlString
    {
        $btnAttributes = static::mergeAttributes($attributes['btn'] ?? [], filled($linkTarget) ? ['target' => $linkTarget] : []);
        $textAttributes = static::mergeAttributes($attributes['text'] ?? [], ['class' => 'flex-1 truncate']);

        return str(static::generateCopyableText(text: $text, attributes: $textAttributes))
            ->finish(static::generateIconButton(icon: $icon, color: $color, size: $size, url: $url, attributes: ['btn' => $btnAttributes]))
            ->wrap('<div class="flex items-center gap-2">', '</div>')
            ->toHtmlString();
    }

    /**
     * @param  AttributeArray  $attributes
     */
    public static function generateCopyableText(string $text, array $attributes = []): HtmlString
    {
        $copiedMessage = __('inspirecms::messages.copied');
        $messageTimeout = 2000;

        $attributes = static::mergeAttributes($attributes, ['class' => 'cursor-pointer']);

        $template = <<<HTML
<span x-on:click="
    window.navigator.clipboard.writeText('$text')
    \$tooltip('$copiedMessage', {
        theme: \$store.theme,
        timeout: $messageTimeout,
    })
">
    $text
</span>
HTML;

        return str(static::wrapWithHtmlTag($template, 'div', $attributes))->toHtmlString();

    }

    /**
     * @param  AttributeArray  $attributes
     */
    public static function generateBadge(string $text, string $color = 'primary', ?string $icon = null, array $attributes = []): HtmlString
    {
        $bindings = ['icon', 'color'];
        $props = [];

        $data = [
            ...compact($bindings),
            ...$props,
        ];

        $template = static::buildComponentTemplate(
            componentName: 'filament::badge',
            bindings: $bindings,
            props: $props,
            slot: $text,
            attributes: $attributes,
        );

        return str(Blade::render($template, $data))->toHtmlString();
    }

    /**
     * @param  array{text:?AttributeArray,badge:?AttributeArray}  $attributes
     */
    public static function generateTextWithBadge(string $text, string $badgeText, string $color = 'primary', ?string $icon = null, array $attributes = []): HtmlString
    {
        return str(static::wrapWithHtmlTag($text, 'span', $attributes['text'] ?? []))
            ->finish(static::generateBadge(text: $badgeText, color: $color, icon: $icon, attributes: $attributes['badge'] ?? []))
            ->wrap('<div class="flex items-center gap-2">', '</div>')
            ->toHtmlString();
    }

    /**
     * @param  array{ctn:?AttributeArray,img:?AttributeArray}  $attributes
     */
    public static function generateCircularImage(string $src, string $alt, array $attributes = []): HtmlString
    {
        $ctnAttributes = static::mergeAttributes($attributes['ctn'] ?? [], ['class' => 'bg-gray-200 rounded-full overflow-hidden']);
        $imgAttributes = static::mergeAttributes($attributes['img'] ?? [], ['class' => 'rounded-full object-cover']);

        $bindings = ['src', 'alt'];
        $data = compact($bindings);

        $avatarHtml = Blade::render(static::buildComponentTemplate(
            componentName: 'filament::avatar',
            bindings: $bindings,
            attributes: $imgAttributes
        ), $data);

        return str(static::wrapWithHtmlTag($avatarHtml, 'div', $ctnAttributes))->toHtmlString();
    }

    /**
     * @param  array{text:?AttributeArray,description:?AttributeArray}  $attributes
     */
    public static function generateTextWithDescription(string $text, ?string $description = null, array $attributes = []): HtmlString
    {
        $textAttributes = $attributes['text'] ?? [];
        $descriptionAttributes = static::mergeAttributes($attributes['description'] ?? [], ['class' => 'text-xs text-gray-500']);

        return str(static::wrapWithHtmlTag($text, 'span', $textAttributes))
            ->finish(static::wrapWithHtmlTag($description, 'span', $descriptionAttributes))
            ->wrap('<div class="flex flex-col">', '</div>')
            ->toHtmlString();
    }

    private static function buildComponentTemplate($componentName, $props = [], $bindings = [], $attributes = [], $slot = null)
    {
        $template = <<<'blade'
<x-{{ component }} 
    {{ props }} {{ bindings }} {{ attributes }}
 >
{{ slot }}
</x-{{ component }}>
blade;
        $buildCamelKey = fn ($key) => Str::camel(str_replace([':', '.'], ' ', $key));

        $propsReplacement = collect($props)
            ->map(fn ($value, $key) => "{$buildCamelKey($key)}=\"{$value}\"")
            ->implode(' ');
        $attributeReplacement = static::buildAttributeReplacement($attributes);
        $bindingReplacements = collect($bindings)
            ->map(fn ($key) => ":$key=\"\${$buildCamelKey($key)}\"")
            ->implode(' ');

        $bladeHtml = str($template)
            ->replace(
                ['{{ component }}', '{{ props }}', '{{ bindings }}', '{{ attributes }}', '{{ slot }}'],
                [$componentName, $propsReplacement, $bindingReplacements, $attributeReplacement, $slot]
            )
            ->toString();

        return $bladeHtml;
    }

    private static function buildAttributeReplacement($attributes = [])
    {
        $attributesBag = new ComponentAttributeBag(Arr::wrap($attributes));

        return collect($attributesBag->getAttributes())
            ->map(fn ($value, $key) => "{$key}=\"$value\"")
            ->implode(' ');
    }

    private static function wrapWithHtmlTag(string $html, string $tag, array $attributes = []): Stringable
    {
        $attributeReplacement = static::buildAttributeReplacement($attributes);

        return str($html)
            ->wrap('<{{ tag }}{{ attributes }}>', '</{{ tag }}>')
            ->replace(['{{ tag }}', '{{ attributes }}'], [$tag, filled($attributeReplacement) ? ' ' . $attributeReplacement : '']);
    }

    private static function mergeAttributes(array $attributes, array $additionalAttributes): array
    {
        $attributes = Arr::wrap($attributes);
        $additionalAttributes = Arr::wrap($additionalAttributes);

        foreach ($additionalAttributes as $key => $value) {
            if (isset($attributes[$key])) {
                $attributes[$key] .= ' ' . $value;
            } else {
                $attributes[$key] = $value;
            }
        }

        return $attributes;
    }
}
