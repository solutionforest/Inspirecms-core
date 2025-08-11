<?php

namespace SolutionForest\InspireCms\Fields\Mixins;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Converter;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Translatable;
use SolutionForest\InspireCms\Fields\Converters\DefaultConverter;

/**
 * @method array getFormSchema()
 * @method array getFormSchemaForConfig()
 * @method array getFieldAttributes()
 * @method array getTargetFieldAttributes($target)
 *
 * @mixin FieldTypeBaseConfig
 *
 * @property null|bool $translatable
 */
class FieldTypeDefinition
{
    /**
     * Gets the converter associated with this field type.
     *
     * The converter is responsible for transforming field values between
     * different representations (e.g., from database format to display format).
     */
    public function getConverter()
    {
        return fn () => collect($this->getTargetFieldAttributes(Converter::class))
            ->map(fn (Converter $attribute) => $attribute->converter)
            ->first() ?? DefaultConverter::class;
    }

    /**
     * Retrieves the enhanced form schema for the field.
     *
     * This method returns an enhanced schema that defines how the field
     * should be rendered and processed in forms, potentially including
     * additional validation, formatting, or UI-related configuration.
     */
    public function getEnhancedFormSchema()
    {
        return function () {
            return [
                Section::make()
                    ->schema([
                        Toggle::make('translatable')
                            ->label(__('inspirecms::resources/field.translatable.label'))
                            ->validationAttribute(__('inspirecms::resources/field.translatable.validation_attribute'))
                            ->default(false)
                            ->inlineLabel(),
                    ]),
                ...$this->getFormSchemaForConfig(),
            ];
        };
    }

    /**
     * Determines whether the field supports translation/localization.
     */
    public function isTranslatable()
    {
        return function () {
            return isset($this->translatable) && $this->translatable === true;
        };
    }

    /**
     * Determines whether the field type supports translation functionality.
     */
    public static function isFieldTypeTranslatable()
    {
        return function () {
            $translatable = collect($this->getTargetFieldAttributes(Translatable::class))
                ->map(fn (Translatable $attribute) => $attribute->translatable)
                ->first();

            // Default to true if no translatable attribute is found
            if (is_null($translatable)) {
                return true;
            }

            return $translatable;
        };
    }
}
