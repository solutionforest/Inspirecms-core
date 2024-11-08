<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

class PropertyTypeDto extends BaseDto
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $group;

    /**
     * @var FieldTypeConfig
     */
    public $config;
}
