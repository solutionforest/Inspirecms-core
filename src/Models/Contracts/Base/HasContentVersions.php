<?php

namespace SolutionForest\InspireCms\Models\Contracts\Base;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;

interface HasContentVersions
{
    /**
     * Return the content versions relation.
     *
     * @return HasMany The content versions relation.
     */
    public function contentVersions(): HasMany;

    /**
     * Get the logs of the published versions.
     */
    public function publishVersionLogs(): HasMany;

    /**
     * Get the published versions.
     */
    public function publishedVersions(): BelongsToMany;

    /**
     * Retrieve the latest version of the content.
     *
     * @return ContentVersion|null The latest content version or null if none exists.
     */
    public function getLatestContentVersion(): ?ContentVersion;

    /**
     * Retrieve the latest published content version.
     *
     * @return ContentVersion|null The latest published content version, or null if none exists.
     */
    public function getLatestPublishedContentVersion(): ?ContentVersion;

    /**
     * Retrieve the latest version property data.
     *
     * @return array The data of the latest version property.
     */
    public function getLatestPublishedPropertyData(): array;

    /**
     * Retrieve the data of the latest version property.
     *
     * @return array The data of the latest version property.
     */
    public function getLatestVersionPropertyData(): array;

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
     * Sets the publishable data for the content.
     *
     * @param  array  $data  An associative array containing the publishable data.
     */
    public function setPublishableData(array $data): void;

    /**
     * Retrieve the data that can be published.
     *
     * This method should return an array of data that is ready to be published.
     *
     * @return array The data that can be published.
     */
    public function getPublishableData(): array;
}
