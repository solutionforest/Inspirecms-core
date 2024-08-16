<?php

namespace SolutionForest\InspireCms\DTOs;

use SolutionForest\InspireCms\Models\CmsDocumentType;

/**
 * @extends BaseDTO<CmsDocumentType>
 */
class DocumentTypeDTO extends BaseDTO
{
    public int $id;
    public string $title;
    public bool $is_root;

    public static function fromModel($documentType): static
    {
        return new self([
            'id' => $documentType->id,
            'title' => $documentType->title,
            'is_root' => $documentType->isRoot(),
        ]);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'is_root' => $this->is_root,
        ];
    }
}