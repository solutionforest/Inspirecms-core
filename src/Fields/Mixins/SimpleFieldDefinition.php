<?php

namespace SolutionForest\InspireCms\Fields\Mixins;

use ReflectionAttribute;
use ReflectionClass;

class SimpleFieldDefinition
{
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
