<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\InspireCms\Models\Contracts\Template;

/**
 * @extends BaseDto<Template>
 */
class TemplateDto extends BaseDto
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $viewName;

    /**
     * @var bool
     */
    public $isDefault;

    public static function fromModel($model)
    {
        return static::fromArray([
            'name' => $model->name,
            'path' => $model->path,
            'viewName' => $model->getViewFullName(),
            'isDefault' => $model->pivot->is_default,
        ])->setModel($model);
    }
}
