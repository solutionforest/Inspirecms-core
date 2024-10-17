<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

class PropertyDataDto extends BaseDto
{
    /**
     * @var string
     */
    public $propertyKey;

    /**
     * @var mixed
     */
    public $propertyValue;

    /**
     * @var ?FieldTypeConfig
     */
    public $config;
}
