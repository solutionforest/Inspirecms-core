<?php

namespace SolutionForest\InspireCms\Models\Contracts;

/**
 * @property int|string $id
 * @property string $title
 * @property string $slug
 * @property string $category
 * @property bool $show_as_table Determine if the children should be displayed as a table
 * @property ?string $icon
 * @property int|string|null $parent_id
 * @property ?\Carbon\CarbonInterface $created_at
 * @property ?\Carbon\CarbonInterface $updated_at
 * 
 * @property-read null | (\SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory & \BackedEnum) $display_category
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection<Model&Field> $fields
 * @property-read \Illuminate\Database\Eloquent\Collection<Model&FieldGroup> $fieldGroups
 * @property-read \Illuminate\Database\Eloquent\Collection<Model&FieldGroupable> $fieldGroupables
 * @property-read \Illuminate\Database\Eloquent\Collection<Model&DocumentType> $inheritedDocumentTypes
 * @property-read \Illuminate\Database\Eloquent\Collection<Model&DocumentType> $inheritingDocumentTypes
 * @property-read \Illuminate\Database\Eloquent\Collection<Model&DocumentType> $rejectedDocumentTypes
 * @property-read \Illuminate\Database\Eloquent\Collection<Model&DocumentType> $rejectingDocumentTypes
 * @property-read \Illuminate\Database\Eloquent\Collection<Model&Content> $content
 */
interface DocumentType extends Base\HasTemplates
{
    /**
     * Get the fields associated with the document type through fieldGroups and fieldGroupables.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function fields();

    /**
     * Get the field groups associated with the document type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany The field groups associated with the document type.
     */
    public function fieldGroups();

    /**
     * Get the morph field groups associated with the document type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany The morph field groups associated with the document type.
     */
    public function fieldGroupables();

    /**
     * Get the document types that are inherited by this document type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function inheritedDocumentTypes();

    /**
     * Get the document types that inherit from this document type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany The relationship instance.
     */
    public function inheritingDocumentTypes();

    /**
     * Retrieve the document types that are rejected by the current doucment type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany The relationship instance.
     */
    public function rejectedDocumentTypes();

    /**
     * Retrieve the document types that reject the current document type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany The relationship instance.
     */
    public function rejectingDocumentTypes();

    /**
     * Get the content associated with the document type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function content();

    public function isWebPageType();

    /**
     * Get the class name of the type enumeration.
     *
     * @return enum-string<\SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory> & class-string<\BackedEnum> The class name of the type enumeration.
     */
    public static function getCategoryEnumClass();

    /**
     * Inherit the document type from another source.
     *
     * @param  string|int|DocumentType&Model  $documentType  The document type to inherit, which can be a string, an integer, or an instance of DocumentType.
     * @return bool Returns true if the document type was successfully inherited, false otherwise.
     */
    public function inheritDocumentType($documentType);

    /**
     * Inherit field groups from the specified document type.
     *
     * @param  string|int|DocumentType&Model  $documentType  The document type to inherit field groups from.
     *                                                       This can be a string, an integer, or an instance of DocumentType.
     * @return bool Returns true if the field groups were successfully inherited, false otherwise.
     */
    public function inheritFieldGroupsFrom($documentType);

    /**
     * Detaches inherited field groups from the specified document type.
     *
     * @param  string|int|DocumentType&Model  $documentType  The document type from which to detach inherited field groups.
     * @return bool True on success, false on failure.
     */
    public function deteachInheritFieldGroupsFrom($documentType);

    /**
     * Determine if the document type can manage templates.
     *
     * @return bool True if the document type can manage templates, false otherwise.
     */
    public function canManageTemplates();

    /**
     * Scope a query to only include document types that can be inherited.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCanBeInherited($query);

    /**
     * Scope a query to only include web page document types.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsWebPage($query);

    /**
     * Scope a query to only include content that can be used.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereCanBeContent($query);
}
