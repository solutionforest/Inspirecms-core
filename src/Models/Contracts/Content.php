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
     */
    public function documentType(): BelongsTo;

    /**
     * Return the multiple property data relation.
     */
    public function propertyDatas(): BelongsToMany;

    /**
     * Return the content versions relation.
     */
    public function contentVersions(): HasMany;

    /**
     * Return the content tree relation.
     */
    public function componentTree(): MorphOne;

    /**
     * Return the parent content relation.
     */
    public function parent(): BelongsTo;

    /**
     * Return the children contents relation.
     */
    public function children(): HasMany;

    /**
     * Determine if this content is already published.
     */
    public function isPublished(?\Closure $callback = null): bool;

    /**
     * Create versioning property data.
     *
     * @return \SolutionForest\InspireCms\Models\Contracts\PropertyData
     */
    public function createPropertyData(array $data);

    /**
     * Retrieves the latest version of property data.
     */
    public function getLatestPropertyData(): ?PropertyData;

    /**
     * Retrieves the latest published version of property data.
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
