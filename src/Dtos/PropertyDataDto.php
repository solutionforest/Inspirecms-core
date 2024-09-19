<?php

namespace SolutionForest\InspireCms\Dtos;

use Carbon\Carbon;
use SolutionForest\InspireCms\Models\Contracts\PropertyData;

/**
 * @extends BaseDto<PropertyData>
 */
class PropertyDataDto extends BaseDto
{
    /**
     * @var ?Carbon
     */
    public $versionDate;

    /**
     * @var ?Carbon
     */
    public $publishedAt;

    /**
     * @var ?Carbon
     */
    public $createdAt;

    /**
     * @var array
     */
    public $propertyValue;

    public static function fromModel($model)
    {
        return static::fromArray([
            'versionDate' => $model->pivot?->created_at,
            'publishedAt' => $model->published_at,
            'propertyValue' => $model->property_value,
        ])->setModel($model);
    }
}
