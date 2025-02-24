<?php

namespace SolutionForest\InspireCms\Fields\Configs\Attributes;

use Attribute;
use SolutionForest\InspireCms\Fields\Converters\BaseConverter;

/**
 * @property class-string<BaseConvert> $converter The converter class to use for this field type.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Converter
{
    public function __construct(
        /**
         * @var class-string<BaseConverter>
         */
        public string $converter,
    ) {}
}
