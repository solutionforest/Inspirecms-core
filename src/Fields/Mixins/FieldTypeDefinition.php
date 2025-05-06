<?php

namespace SolutionForest\InspireCms\Fields\Mixins;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use ReflectionAttribute;
use ReflectionClass;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Converter;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Translatable;
use SolutionForest\InspireCms\Fields\Converters\DefaultConverter;

/**
 * @method array getFormSchema()
 * 
 * @mixin \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig
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
        return fn () => collect($this->getTargetAttributes(Converter::class))
            ->map(fn (Converter $attribute) => $attribute->converter)
            ->first() ?? DefaultConverter::class;
    }

    /**
     * Retrieves the attributes associated with the field type.
     */
    public function getAttributes()
    {
        return function () {
            $reflection = new ReflectionClass(static::class);

            return $reflection->getAttributes();
        };
    }

    public function getTargetAttributes()
    {
        return fn (string $attributeName) => collect($this->getAttributes())
            ->whereInstanceOf(ReflectionAttribute::class)
            ->filter(fn (ReflectionAttribute $attribute) => $attribute->getName() === $attributeName)
            ->map(fn (ReflectionAttribute $attribute) => $attribute->newInstance())
            ->all();
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
                ...$this->getFormSchema(),
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
    public function isFieldTypeTranslatable()
    {
        return function () {
            $translatable = collect($this->getTargetAttributes(Translatable::class))
                ->map(fn (Translatable $attribute) => $attribute->translatable)
                ->first();

            if (is_null($translatable)) {
                return true;
            }

            return $translatable;
        };
    }
}
