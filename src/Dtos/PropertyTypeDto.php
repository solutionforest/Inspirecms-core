<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;

class PropertyTypeDto extends BaseDto
{
    /**
     * @var string
     */
    public $name;

    public $value;

    /**
     * @var FieldTypeConfig
     */
    public $type;
}
