<?php

namespace SolutionForest\InspireCms\Fields\Configs\Attributes;

use Attribute;

/**
 * @property bool $translatable Whether the field is translatable.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Translatable
{
    public function __construct(
        public bool $translatable = true,
    ) {}
}
