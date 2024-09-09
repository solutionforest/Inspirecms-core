<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Base\Interfaces\NestableInterface;

interface Content extends NestableInterface
{
    /**
     * Return the document type relation.
     *
     * @return BelongsTo The document type relation.
     */
    public function documentType(): BelongsTo;

    /**
     * Return the multiple property data relation.
     *
     * @return BelongsToMany The property data relation.
     */
    public function propertyDatas(): BelongsToMany;

    /**
     * Return the content versions relation.
     *
     * @return HasMany The content versions relation.
     */
    public function contentVersions(): HasMany;

    /**
     * Return the content tree relation.
     *
     * @return MorphOne The content tree relation.
     */
    public function componentTree(): MorphOne;

    /**
     * Return the parent content relation.
     *
     * @return BelongsTo The parent content relation.
     */
    public function parent(): BelongsTo;

    /**
     * Return the children contents relation.
     *
     * @return HasMany The children contents relation.
     */
    public function children(): HasMany;

    /**
     * Determine if this content is already published.
     *
     * This method checks if the content has been published,
     * optionally allowing a callback for additional checks.
     *
     * @param  \Closure|null  $callback  Optional callback for additional checks.
     * @return bool True if published, false otherwise.
     */
    public function isPublished(?\Closure $callback = null): bool;

    /**
     * Create versioning property data.
     *
     * This method creates a new property data instance for versioning.
     *
     * @param  array  $data  The data to create the property data with.
     * @return \SolutionForest\InspireCms\Models\Contracts\PropertyData The created property data.
     */
    public function createPropertyData(array $data);

    /**
     * Retrieves the latest version of property data.
     *
     * This method returns the most recent property data associated with the content.
     *
     * @return PropertyData|null The latest property data or null if none exists.
     */
    public function getLatestPropertyData(): ?PropertyData;

    /**
     * Retrieves the latest published version of property data.
     *
     * This method returns the most recent published property data associated with the content.
     *
     * @return PropertyData|null The latest published property data or null if none exists.
     */
    public function getLatestPublishedPropertyData(): ?PropertyData;

    /**
     * Set the publishable state.
     *
     * This method allows you to define a specific state for the
     * publishable state, which can be used to control the publishing
     * behavior of the entity.
     *
     * @param  string  $state  The state to set for the publishable state.
     */
    public function setPublishableState(string $state): void;

    /**
     * Get the current publishable state.
     *
     * This method retrieves the current state representing the
     * publishable state of the entity.
     *
     * @return string The current publishable state.
     */
    public function getPublishableState(): string;

    /**
     * Reset the publishable state to the default state.
     *
     * This method sets the publishable state back to its default
     * value, e.g. 'draft'.
     */
    public function resetPublishableState(): void;
}
