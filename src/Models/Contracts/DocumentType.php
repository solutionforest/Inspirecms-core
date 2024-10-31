<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;

interface DocumentType extends Base\HasTemplates, HasDtoModel
{
    /**
     * Get the field groups associated with the document type.
     *
     * @return MorphToMany The field groups associated with the document type.
     */
    public function fieldGroups(): MorphToMany;

    /**
     * Get the morph field groups associated with the document type.
     *
     * @return MorphMany The morph field groups associated with the document type.
     */
    public function fieldGroupables(): MorphMany;

    /**
     * Get the document types that are inherited by this document type.
     */
    public function inheritedDocumentTypes(): BelongsToMany;

    /**
     * Get the document types that inherit from this document type.
     *
     * @return BelongsToMany The relationship instance.
     */
    public function inheritingDocumentTypes(): BelongsToMany;

    /**
     * Get the content associated with the document type.
     *
     * @return HasMany The content associated with the document type.
     */
    public function content(): HasMany;

    /**
     * Determine if the children should be displayed as a table.
     *
     * @return bool True if the children should be shown as a table, false otherwise.
     */
    public function isShowChildrenAsTable(): bool;

    public function isWebPageType(): bool;

    public function canInheriting(): bool;

    public function canBeInherited(): bool;

    public function getTypeEnum(): ?\SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeType;

    /**
     * Get the class name of the type enumeration.
     *
     * @return string The class name of the type enumeration.
     */
    public static function getTypeEnumClass(): string;

    public function inheritDocumentType(string | int | DocumentType $documentType): bool;

    public function inheritFieldGroupsFrom(string | int | DocumentType $documentType): bool;

    public function deteachInheritFieldGroupsFrom(string | int | DocumentType $documentType): bool;

    /**
     * Determine if the document type can be a parent.
     *
     * @return bool True if the document type can be a parent, false otherwise.
     */
    public function canBeParent(): bool;

    /**
     * Determine if the document type can have a parent.
     *
     * @return bool True if the document type can have a parent, false otherwise.
     */
    public function canHaveParent(): bool;
}
