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
 * @property ?\Carbon\Carbon $created_at
 * @property ?\Carbon\Carbon $updated_at
 * @property ?\Carbon\Carbon $deleted_at
 * @property null|\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption $display_status
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
     * Scope a query to only include web pages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsWebPage($query);
}
