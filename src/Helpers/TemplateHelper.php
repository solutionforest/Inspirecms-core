<?php

namespace SolutionForest\InspireCms\Helpers;

use SolutionForest\InspireCms\Base\Enums\CacheKeys;
use SolutionForest\InspireCms\InspireCmsConfig;

class TemplateHelper
{
    public static function setupKeyValueForCurrentTemplate()
    {
        $model = InspireCmsConfig::getKeyValueModelClass();
        $tableName = app($model)->getTable();

        if (ModelHelper::isTableExists($tableName)) {
            $model::query()->firstOrCreate(
                ['key' => static::getCurrentThemeKey()],
                ['value' => static::getDefaultTemplateTheme()]
            );
        }
    }

    public static function getCurrentThemeKey(): string
    {
        return CacheKeys::CURRENT_THEME->value;
    }

    /**
     * Get the default template theme from the configuration file.
     *
     * @return string The name of the default template theme.
     */
    public static function getDefaultTemplateTheme(): string
    {
        return trim(InspireCmsConfig::get('template.default_theme', 'manifest'));
    }

    public static function getDefaultTemplateThemes(): array
    {
        return [
            'manifest',
            'blogrock',
            'know-press',
        ];
    }

    public static function getComponentPrefixForThemes(): string
    {
        return str(InspireCmsConfig::get('template.component_prefix', 'inspirecms'))
            ->trim()
            ->trim('.')
            ->toString();
    }

    public static function getDirectoryForThemedComponents(): string
    {
        $directory = str_replace('.', '/', static::getComponentPrefixForThemes());

        return resource_path("views/components/{$directory}");
    }

    public static function getDirectoryForExportedTemplates(): string
    {
        return str(InspireCmsConfig::get('template.exported_template_dir', resource_path('views/inspirecms/templates')))
            ->trim()
            ->rtrim('/')
            ->toString();
    }

    public static function retrieveDefaultLayoutContent()
    {
        return <<<'HTML'
        @php
            $locale ??= $content->getLocale() ?? request()->getLocale();
            $seo = $content->getSeo()?->getHtml();
            $title = $content->getTitle();
        @endphp
        <html lang="{{ $locale }}">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{{ $title }}</title>
            {!! $seo !!}
        </head>
        <body>

            {{ $slot }}

        </body>
        </html>
        HTML;
    }

    public static function retrieveDefaultThemeContent()
    {
        $componentName = static::getDefaultThemedLayoutComponentName();

        return <<<HTML
        @php
            \$locale ??= \$content->getLocale();
        @endphp
        <x-cms-template :content="\$content" type="{$componentName}">
            Your content here
        </x-cms-template>
        HTML;
    }

    public static function getDefaultThemedLayoutComponentName(): string
    {
        return 'page';
    }

    /**
     * Splits a Blade expression to extract a property.
     *
     * This method takes a Blade expression as input and splits it to retrieve
     * the property specified within the expression.
     *
     * @param  string  $bladeExpression  The Blade expression to be split.
     * @return array An array containing the split parts of the Blade expression.
     */
    public static function splitBladeExpressionForProperty(string $bladeExpression)
    {
        $explodedValues = array_map('trim', explode(',', $bladeExpression));

        if (count($explodedValues) > 3) {
            [$group, $property, $propertyVarName, $dtoVar] = $explodedValues;
        } elseif (count($explodedValues) > 2) {
            [$group, $property, $propertyVarName] = $explodedValues;
        } else {
            [$group, $property] = $explodedValues;
        }

        $group = static::normalizeVarNameFromBladeExpression($group);
        $property = static::normalizeVarNameFromBladeExpression($property);

        $propertyVarName ??= static::generatePropertyVarName($group, $property);
        $dtoVar ??= '$content';

        return [$group, $property, $dtoVar, static::normalizeVarNameFromBladeExpression($propertyVarName)];
    }

    /**
     * Generates a variable name for a given property within a specified group.
     *
     * @param  string  $group  The group to which the property belongs.
     * @param  string  $property  The property for which the variable name is generated.
     * @return string The generated variable name.
     */
    public static function generatePropertyVarName($group, $property)
    {
        $group = static::normalizeVarNameFromBladeExpression($group);
        $property = static::normalizeVarNameFromBladeExpression($property);

        return "{$group}_{$property}";
    }

    protected static function normalizeVarNameFromBladeExpression($text)
    {
        return ltrim(trim($text, "'\""), '$');
    }
}
