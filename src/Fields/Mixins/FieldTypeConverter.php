<?php

namespace SolutionForest\InspireCms\Fields\Mixins;

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
}
