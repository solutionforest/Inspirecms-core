<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongsToNestableTree;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface;

interface Content extends Base\HasContentVersions, Base\HasContentWebSetting, Base\HasTemplates, BelongsToNestableTree, HasDtoModel, HasRecursiveRelationshipsInterface
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

    /**
     * @return HasOne
     */
    public function path();

    public static function toPreviewDto(array | Model $record, array $propertyData, ?string $locale = null, ?DocumentType $documentType = null);

    /**
     * Retrieve the segments of slug for the content.
     *
     * @return array An array of segments.
     */
    public function getSegments(): array;

    /**
     * Generate the full slug for the content.
     *
     * @return string The generated full slug.
     */
    public function generateSlugPath();

    /**
     * Get the full URL of the content.
     * 
     * @param LanguageDto|string|null $locale
     * 
     * @return string
     */
    public function getUrl($locale = null);

    /**
     * Determine if the content is a web page.
     *
     * @return bool True if the content is a web page, false otherwise.
     */
    public function isWebPage(): bool;
}
