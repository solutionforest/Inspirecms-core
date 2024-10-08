<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Models\DocumentType;

/**
 * @extends BaseModelDto<DocumentType>
 */
class DocumentTypeDto extends BaseModelDto
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
     * @var Collection<FieldDto>
     */
    public $fields;

    public static function fromModel($model)
    {
        $model->loadMissing(['fieldGroups.fields']);

        $dto = parent::fromModel($model);

        $dto->fields = $model->fieldGroups->flatMap(function ($group) {
            return $group->fields;
        })->map(fn ($field) => FieldDto::fromModel($field));

        return $dto;
    }

    public function getField(string $name): ?FieldDto
    {
        return $this->fields->first(fn ($field) => $field->name === $name);
    }
}
