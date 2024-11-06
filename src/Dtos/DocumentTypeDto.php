<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

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
     * @var Collection<FieldDto>
     */
    public $fields;

    public function getField(string $name): ?FieldDto
    {
        return $this->fields->first(fn ($field) => $field->name === $name);
    }
}
