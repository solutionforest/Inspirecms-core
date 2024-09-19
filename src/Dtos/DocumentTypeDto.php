<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\Collection;
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

    /**
     * @var bool
     */
    public $asRoot;

    /**
     * @var Collection<TemplateDto>
     */
    public $templates;

    public static function fromModel($model)
    {
        $model->loadMissing([
            'templates',
        ]);

        return static::fromArray([
            'id' => $model->getKey(),
            'title' => $model->title,
            'asRoot' => $model->can_use_at_root,
            'templates' => collect($model->templates)->map(fn ($template) => TemplateDto::fromModel($template)),
        ])->setModel($model);
    }
}
