<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\InspireCms\Models\Contracts\DocumentType;

/**
 * @extends BaseDto<DocumentType>
 */
class DocumentTypeDto extends BaseDto
{
    /**
     * @var int|string
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    public static function fromModel($model)
    {
        return static::fromArray([
            'id' => $model->id,
            'title' => $model->title,
        ]);
    }
}
