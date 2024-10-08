<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\Models\Field;

/**
 * @extends BaseModelDto<Field>
 */
class FieldDto extends BaseModelDto
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $label;

    /**
     * @var FieldTypeConfig
     */
    public $config;

    public static function fromModel($model)
    {
        $dto = parent::fromModel($model);

        $dto->config = FilamentFieldGroup::getFieldTypeConfig($model->type, $model->config ?? []);

        return $dto;
    }
}
