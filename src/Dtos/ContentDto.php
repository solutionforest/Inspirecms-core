<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Collection;
use SolutionForest\InspireCms\Models\Content;

/**
 * @extends BaseDto<Content>
 */
class ContentDto extends BaseDto
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
     * @var string
     */
    public $slug;

    /**
     * @var ?DocumentTypeDto
     */
    public $documentType;

    /**
     * @var Collection<TemplateDto>
     */
    public $templates;

    public static function fromModel($model)
    {
        $model->loadMissing([
            'documentType.templates',
            'templates',
        ]);

        return static::fromArray([
            'id' => $model->getKey(),
            'title' => $model->title,
            'slug' => $model->slug,
            'documentType' => DocumentTypeDto::fromModel($model->documentType),
            'templates' => collect($model->templates)->map(fn ($template) => TemplateDto::fromModel($template)),
        ]);
    }

    public function getDefaultTemplate(): ?TemplateDto
    {
        $fallbackTemplate = $this->documentType?->templates->first(function (TemplateDto $templateDto) {
            return $templateDto->isDefault;
        });

        $currTemplate = $this->templates->first(function (TemplateDto $templateDto) {
            return $templateDto->isDefault;
        });

        return $currTemplate ?? $fallbackTemplate;
    }
}
