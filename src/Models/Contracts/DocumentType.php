<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface;

interface DocumentType extends Base\HasTemplates, HasRecursiveRelationshipsInterface
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
     * Get the content associated with the document type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function content();

    /**
     * Determine if the children should be displayed as a table.
     *
     * @return bool True if the children should be shown as a table, false otherwise.
     */
    public function isShowChildrenAsTable();

    public function isWebPageType();

    public function canInheriting();

    public function canBeInherited();

    /**
     * Get the category enum for the document type.
     *
     * @return \SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory|null The category enum or null if not set.
     */
    public function getCategoryEnum();

    /**
     * Get the class name of the type enumeration.
     *
     * @return string The class name of the type enumeration.
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
     * Determine if the document type can be a parent.
     *
     * @return bool True if the document type can be a parent, false otherwise.
     */
    public function canBeParent();

    /**
     * Determine if the document type can have a parent.
     *
     * @return bool True if the document type can have a parent, false otherwise.
     */
    public function canHaveParent();

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
}
