<?php

namespace SolutionForest\InspireCms\Helpers;

use Closure;
use Exception;
use Filament\Forms\Components\Field;
use Illuminate\Support\Arr;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Filament\Forms\Components\Translate as TranslateComponent;

class FieldTypeHelper
{
    /**
     * Perform form field creation from configuration.
     *
     * @param  string  $typeName  The type name of the form field.
     * @param  callable(FieldTypeConfig,string,array)  $createFieldUsing  A closure that creates the form field.
     * @param  array  $config  Optional configuration array for the form field.
     * @return mixed The created form field.
     */
    public static function performFormFieldFromConfig(string $typeName, Closure $createFieldUsing, array $config = [])
    {

        $fiFormConfig = static::getFieldTypeConfig($typeName, $config);

        if (! $fiFormConfig) {
            return null;
        }

        $fiFormComponentFQCN = Arr::first(Arr::pluck($fiFormConfig->getFormComponents(), 'component'));
        if (! $fiFormComponentFQCN) {
            throw new Exception("The field type config class '{$typeName}' does not have a FormComponent attribute.");
        }

        $fiFormComponent = $createFieldUsing($fiFormConfig, $fiFormComponentFQCN, $config);
        if (! $fiFormComponent) {
            throw new Exception("The field type config class '{$typeName}' does not have a FormComponent attribute.");
        }

        $fiFormConfig->applyConfig($fiFormComponent);

        return $fiFormComponent;
    }

    /**
     * Builds a field for the given field type.
     *
     * @param  string  $fieldTypeName  The name of the field type.
     * @param  array  $fieldTypeConfig  The configuration array for the field type.
     * @param  string  $name  The name of the field.
     * @param  string  $label  The label for the field.
     * @param  string|null  $helperText  The helper text for the field.
     * @param  bool  $required  Whether the field is required.
     * @param  string|null  $groupName  The name of the group the field belongs to.
     * @return ?Field The built filament form field.
     */
    public static function buildFieldForFieldType($fieldTypeName, $fieldTypeConfig, $name, $label, $helperText, $required, $groupName)
    {
        $fieldType = static::getFieldTypeConfig($fieldTypeName, $fieldTypeConfig);

        $fiFormComponent = static::performFormFieldFromConfig(
            typeName: $fieldTypeName,
            config: $fieldTypeConfig,
            createFieldUsing: function ($fieldType, $fiFormComponentFQCN, $config) use ($name, $label, $helperText, $required, $groupName) {
                if (is_subclass_of($fiFormComponentFQCN, Field::class)) {

                    $fiFormComponent = $fiFormComponentFQCN::make($name);

                    $fiFormComponent->label($label);
                    $fiFormComponent->helperText($helperText);
                    $fiFormComponent->required($required);

                    if (filled($groupName)) {
                        $statePath = implode('.', [$groupName, $name]);
                        $fiFormComponent->statePath($statePath);
                    }

                } else {

                    $fiFormComponent = null;
                }

                return $fiFormComponent;

            },
        );

        if (! $fieldType) {
            return null;
        }

        // if the field is translatable
        if ($fieldType->isTranslatable()) {

            $translateComponent = TranslateComponent::make();

            return $translateComponent
                ->schema([$fiFormComponent])
                // also set the state path for this component
                ->groupName($groupName);
        }

        return $fiFormComponent;

    }

    /**
     * Get the configuration form schema for a given field type.
     *
     * @param  ?string  $typeName  The name of the field type.
     * @return array The configuration form schema for the specified field type.
     */
    public static function getFieldConfigFormSchemaForFieldType($typeName)
    {
        if (filled($typeName) && ($fieldTypeConfig = static::getFieldTypeConfig($typeName))) {
            // hidden "translatable" field for the field type
            if (! $fieldTypeConfig->isFieldTypeTranslatable()) {
                return $fieldTypeConfig->getFormSchemaForConfig();
            }

            // display "translatable" field for the field type
            return $fieldTypeConfig->getEnhancedFormSchema();
        }

        return [];
    }

    public static function getFieldTypeConfig(string $typeName, array $config = []): ?FieldTypeConfig
    {
        try {
            return FilamentFieldGroup::getFieldTypeConfig($typeName, $config);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function getFieldTypeOptions(?string $search = null, array $excepts = []): array
    {
        return FilamentFieldGroup::getFieldTypeGroupedKeyValueWithIconOptions($search, $excepts);
    }

    public static function getFieldTypeIcon(string $typeName): null | string | array
    {
        $config = static::getFieldTypeConfig($typeName)?->getConfigNames() ?? [];

        $icons = collect($config)->pluck('icon')->filter()->unique()->values();

        if ($icons->count() > 1) {
            return $icons->toArray();
        } else {
            return $icons->first();
        }
    }
}
