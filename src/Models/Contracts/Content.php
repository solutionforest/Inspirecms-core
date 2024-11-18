<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongsToNestableTree;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\IndexableModel;

interface Content extends Base\HasContentVersions, Base\HasContentWebSetting, Base\HasTemplates, BelongsToNestableTree, HasDtoModel, HasRecursiveRelationshipsInterface, IndexableModel
{
    /**
     * Return the document type relation.
     *
     * @return BelongsTo The document type relation.
     */
    public function documentType(): BelongsTo;

    /**
     * Define a one-to-one relationship for the sitemap.
     */
    public function sitemap(): MorphOne;

    /**
     * Establish a relationship with the parent model, including those that are soft deleted.
     */
    public function trashedParent(): BelongsTo;

    public function navigation(): HasOne;

    public static function toPreviewDto(array | Model $record, array $propertyData, ?string $locale = null, ?DocumentType $documentType = null);

    /**
     * Retrieve the segments of slug for the content.
     *
     * @return array An array of segments.
     */
    public function getSegments(): array;

    /**
     * Determine if the content is the first and root element.
     *
     * @return bool True if the content is the first and root element, false otherwise.
     */
    public function isFirstAndRoot(): bool;
    
    /**
     * Generate a full slug base on parent.
     */
    public function getFullSlug(?string $locale = null): string;

    /**
     * Get the full URL of the content.
     */
    public function getUrl(?string $locale = null): string;

    /**
     * Determine if the content is a web page.
     *
     * @return bool True if the content is a web page, false otherwise.
     */
    public function isWebPage(): bool;
}
