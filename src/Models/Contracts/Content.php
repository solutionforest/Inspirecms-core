<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Base\Interfaces\NestableInterface;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\IndexableModel;

interface Content extends Base\HasContentVersions, Base\HasTemplates, HasDtoModel, IndexableModel, NestableInterface
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
     * Return the nestable tree relation.
     *
     * @return MorphOne The content tree relation.
     */
    public function nestableTree(): MorphOne;

    /**
     * Return the parent content relation.
     *
     * @return BelongsTo The parent content relation.
     */
    public function parent(): BelongsTo;

    /**
     * Return the children content relation.
     *
     * @return HasMany The children content relation.
     */
    public function children(): HasMany;

    public function getNestableParentIdColumn(): string;

    public function getNestableRootValue(): int | string;

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
