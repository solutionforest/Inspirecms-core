<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Base\Models\Interfaces\HasLocaleUrl;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongsToNestableTree;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface;
use SolutionForest\InspireCms\Support\Models\Contracts\HasAuthor;

/**
 * @template TDto of \SolutionForest\InspireCms\Support\Base\Dtos\BaseTranslatableModelDto
 *
 * @property string $id
 * @property string $parent_id
 * @property string $title
 * @property string $slug
 * @property int $status
 * @property bool $is_default
 * @property int $document_type_id
 * @property ?\Carbon\CarbonInterface $created_at
 * @property ?\Carbon\CarbonInterface $updated_at
 * @property ?\Carbon\CarbonInterface $deleted_at
 * @property-read null|\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption $display_status
 * @property-read null | Model & DocumentType $documentType
 * @property-read null | Model & Sitemap $sitemap
 * @property-read null | Model & Content $trashedParent
 * @property-read null | Model & Navigation $navigation
 * @property-read null | Model & ContentPath $path
 * @property-read Collection<Model & Content> $ancestors
 * @property-read Collection<Model & Content> $ancestorsAndSelf
 * @property-read Collection<Model & Content> $bloodline
 * @property-read Collection<Model & Content> $children
 * @property-read Collection<Model & Content> $childrenAndSelf
 * @property-read Collection<Model & Content> $descendants
 * @property-read null | Model & Content $parent
 * @property-read Collection<Model & Content> $parentAndSelf
 */
interface Content extends Base\HasContentVersions, Base\HasContentWebSetting, Base\HasTemplates, BelongsToNestableTree, HasAuthor, HasDtoModel, HasLocaleUrl, HasRecursiveRelationshipsInterface
{
    /**
     * Return the document type relation.
     *
     * @return BelongsTo The document type relation.
     */
    public function documentType();

    /**
     * Define a one-to-one relationship for the sitemap.
     *
     * @return MorphOne
     */
    public function sitemap();

    /**
     * Establish a relationship with the parent model, including those that are soft deleted.
     *
     * @return BelongsTo
     */
    public function trashedParent();

    /**
     * Define a method to retrieve navigation data.
     *
     * @return HasOne
     */
    public function navigation();

    /**
     * @return HasOne
     */
    public function path();

    /**
     * Converts the given record and property data to a preview DTO.
     *
     * @param  array|Model&Content  $record  The record to be converted, either as an array or a Model instance.
     * @param  array  $propertyData  The property data associated with the record.
     * @param  string|null  $locale  Optional. The locale to be used for the preview. Defaults to null.
     * @param  DocumentType&Model|null  $documentType  Optional. The document type to be used for the preview. Defaults to null.
     * @return TDto The preview DTO.
     */
    public static function toPreviewDto($record, $propertyData, $locale = null, $documentType = null);

    /**
     * @return TDto The DTO representation of the model.
     */
    public function toDto(...$args);

    /**
     * Retrieve the segments of slug for the content.
     *
     * @return array An array of segments.
     */
    public function getSegments();

    /**
     * Generate the full slug for the content.
     *
     * @return string The generated full slug.
     */
    public function generateSlugPath();

    /**
     * Determine if the content is a web page.
     *
     * @return bool True if the content is a web page, false otherwise.
     */
    public function isWebPage();
    
    /**
     * Set the content as the default.
     *
     * @return void
     */
    public function setAsDefault();

    /**
     * Scope a query to only include web pages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsWebPage($query);

    /**
     * Scope a query to only include default content.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsDefault($query, bool $condition = true);
}
