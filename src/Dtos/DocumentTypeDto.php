<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\InspireCms\Models\CmsDocumentType;

/**
 * @extends BaseDto<CmsDocumentType>
 */
class DocumentTypeDto extends BaseDto
{
    public int $id;

    public string $title;

    public static function fromModel($documentType): static
    {
        return new self([
            'id' => $documentType->id,
            'title' => $documentType->title,
        ]);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
