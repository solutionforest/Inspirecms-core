<?php

namespace SolutionForest\InspireCms\Fields\Mixins;

use ReflectionAttribute;
use ReflectionClass;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Converter;
use SolutionForest\InspireCms\Fields\Converters\DefaultConverter;

class FieldTypeConverter
{
    public function getConverter()
    {
        return fn () => collect($this->getTargetAttributes(Converter::class))
            ->map(fn (Converter $attribute) => $attribute->converter)
            ->first() ?? DefaultConverter::class;
    }

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
}
