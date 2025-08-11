<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components;

use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Dtos\PropertyTypeDto;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;
use SolutionForest\InspireCms\Helpers\PropertyTypeHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;

class TemplatePropertyTypeInstructionsEntry
{
    public static function make()
    {
        return Group::make()
            ->statePath('property_type_instructions')
            ->schema(function ($state) {
                $groupedPropertyTypes = collect(is_array($state) ? $state : [])->map(function ($arr) {
                    $data = $arr['dtoData'] ?? [];
                    $data['config'] = FieldTypeHelper::getFieldTypeConfig($arr['fieldType'], $data['config'] ?? []);

                    return PropertyTypeDto::fromArray($data);
                })->groupBy('group')->sortKeys();

                return $groupedPropertyTypes->map(function ($propertyTypes, $group) {

                    $fieldSections = collect($propertyTypes)
                        ->map(function ($propertyType) use ($group) {
                            $fieldType = $propertyType->config;

                            $fieldTypeConfig = $fieldType ? Arr::first($fieldType->getConfigNames()) : [];
                            $fieldTypeName = $fieldTypeConfig['name'] ?? null;
                            $icon = $fieldTypeConfig['icon'] ?? null;
                            $fieldKey = $propertyType->key;

                            $translatable = $fieldType?->isTranslatable() ?? false;

                            return Section::make()
                                ->collapsible()
                                ->icon($icon)
                                ->iconSize('md')
                                ->compact()
                                ->heading($fieldKey)
                                ->afterHeader(Schema::between([
                                    ...($translatable ? [Icon::make(Heroicon::OutlinedLanguage)->color('gray')] : []),
                                    Text::make(__('inspirecms::resources/template.property_type_instructions.field'))
                                        ->tooltip(
                                            'Field Type: ' . ($fieldTypeName ?? 'Unknown')
                                        ),
                                ]))
                                ->schema([
                                    RepeatableEntry::make('sample_codes')
                                        ->hiddenLabel()
                                        ->contained(false)
                                        ->state(collect(static::getSampleCodesForField($fieldType, $group, $fieldKey))->map(fn ($code) => ['code' => $code])->all())
                                        ->schema([
                                            CodeEntry::make('code')
                                                ->hiddenLabel()
                                                ->grammar('php')
                                                ->copyable(),
                                        ]),
                                ]);
                        })
                        ->all();

                    return Section::make()
                        ->compact()
                        ->heading($group)
                        ->collapsed()
                        ->afterHeader(__('inspirecms::resources/template.property_type_instructions.group'))
                        ->schema($fieldSections);

                })->values()->all();
            });
    }

    /**
     * @param  FieldTypeConfig  $fieldType
     * @param  string  $group
     * @param  string  $field
     */
    protected static function getSampleCodesForField($fieldType, $group, $field): array
    {
        $translatable = $fieldType?->isTranslatable() ?? false;

        $valueType = PropertyTypeHelper::getFieldDisplayValueType($fieldType);

        if ($valueType == 'boolean') {
            return [
                static::getSampleCodeForBooleanField($group, $field),
            ];
        }

        $result = [];

        if ($valueType != 'array' || $translatable) {
            $result[] = static::getSampleCodeForBasicField($group, $field);
        } else {
            $result[] = static::getSampleCodeForArrayField($group, $field);
        }

        $result[] = static::getSampleCodeForConditionalField($group, $field);

        return $result;
    }

    protected static function getSampleCodeForBooleanField($group, $field): string
    {
        return <<<BLADE
        @if (\$content?->getPropertyGroup('{$group}')?->getPropertyData('{$field}')?->getValue() ?? false)
        @endif
        BLADE;
    }

    protected static function getSampleCodeForConditionalField($group, $field): string
    {
        $propertyVarName = TemplateHelper::generatePropertyVarName($group, $field);

        return <<<BLADE
        @propertyNotEmpty('{$group}', '{$field}')
            // \${$propertyVarName} = ...
        @endif
        BLADE;
    }

    protected static function getSampleCodeForBasicField($group, $field): string
    {
        return <<<BLADE
        @property('{$group}', '{$field}')
        BLADE;
    }

    protected static function getSampleCodeForArrayField($group, $field): string
    {
        $propertyVarName = TemplateHelper::generatePropertyVarName($group, $field);

        return <<<BLADE
        @propertyArray('{$group}', '{$field}')
        @foreach (\${$propertyVarName} ?? [] as \$item)
            // ...
        @endforeach
        BLADE;
    }
}
