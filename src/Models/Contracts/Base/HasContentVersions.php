<?php

namespace SolutionForest\InspireCms\Models\Contracts\Base;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
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
     * Get the latest content version associated with the model.
     */
    public function latestContentVersion(): HasOne;

    public function getPublishedVersions(): Collection;

    /**
     * Retrieve the latest content version that has been published.
     *
     * @return ContentVersion|null The latest published content version, or null if none exists.
     */
    public function getLatestContentVersionHasPublish(): ?ContentVersion;

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
     * Get the publish time of the content.
     *
     * @return \Carbon\Carbon|null The publish time of the content, or null if not set.
     */
    public function getPublishTime(): ?\Carbon\Carbon;

    /**
     * Get the latest published time (includes schedule publish).
     *
     * @return \Carbon\Carbon|null The latest published time or null if not available.
     */
    public function getLatestPublishedTime(): ?\Carbon\Carbon;

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
     * Sets the versioning event for the content.
     *
     * @param  string  $event  The event to set for versioning.
     */
    public function setVersioningEvent(string $event): void;

    /**
     * Retrieve the event associated with versioning.
     *
     * @return string|null The versioning event, or null if not applicable.
     */
    public function getVersioningEvent(): ?string;

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

    /**
     * Preloads the content version data.
     *
     * This method is responsible for preloading any necessary data related to content versions.
     */
    public function preloadContentVersionData(): void;

    /**
     * Retrieve the data required to preload a version.
     *
     * This method is responsible for fetching and returning an array of data
     * that is necessary to preload a specific version of the content.
     *
     * @return array An associative array containing the preload version data.
     */
    public function getPreloadVersionData(): array;

    /**
     * Resets the state of the content version.
     *
     * This method is responsible for resetting any state or data
     * related to the content version of the implementing class.
     */
    public function resetContentVersionState(): void;
}
