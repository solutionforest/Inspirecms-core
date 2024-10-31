<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Contracts;

use Illuminate\Database\Eloquent\Model;

interface DocumentTypeForm
{
    public function getParentKey(): string | int | null;
    public function getParent(): ?Model;
    /**
     * Determine if the document type can be a parent.
     *
     * @param null|string|int $parentKey The key of the potential parent document type.
     * @return bool True if the document type can be a parent, false otherwise.
     */
    public function canBeParent($parentKey): bool;
}
