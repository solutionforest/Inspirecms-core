<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongToNestableTree;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\IndexableModel;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\NestableInterface;

interface Content extends Base\HasContentVersions, Base\HasTemplates, HasDtoModel, IndexableModel, NestableInterface, BelongToNestableTree
{
    /**
     * Return the document type relation.
     *
     * @return BelongsTo The document type relation.
     */
    public function documentType(): BelongsTo;

    /**
     * Define a one-to-one relationship with the WebSetting model.
     */
    public function webSetting(): HasOne;

    /**
     * Define a one-to-one relationship for the site map.
     */
    public function siteMap(): MorphOne;

    /**
     * Establish a relationship with the parent model, including those that are soft deleted.
     *
     * @return BelongsTo
     */
    public function withTrashedParent(): BelongsTo;

    public static function toPreviewDto(array | Model $record, array $propertyData, ?string $locale = null, ?string $fallbackLocale = null, ?DocumentType $documentType = null);

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
