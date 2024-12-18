<?php

namespace SolutionForest\InspireCms\Models\Contracts\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Models\Contracts\ContentPublishVersion;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<Model & ContentVersion> $contentVersions
 * @property-read \Illuminate\Database\Eloquent\Collection<Model & ContentPublishVersion> $publishVersionLogs
 * @property-read \Illuminate\Database\Eloquent\Collection<Model & ContentVersion> $publishedVersions
 * @property-read null | Model & ContentVersion $latestContentVersion
 */
interface HasContentVersions
{
    /**
     * Return the content versions relation.
     *
     * @return HasMany The content versions relation.
     */
    public function contentVersions();

    /**
     * Get the logs of the published versions.
     *
     * @return HasMany
     */
    public function publishVersionLogs();

    /**
     * Get the published versions.
     *
     * @return BelongsToMany
     */
    public function publishedVersions();

    /**
     * Get the latest content version associated with the model.
     *
     * @return HasOne
     */
    public function latestContentVersion();

    /**
     * Summary of getPublishedVersions
     *
     * @return Collection<\SolutionForest\InspireCms\Models\Contracts\ContentVersion&Model>
     */
    public function getPublishedVersions();

    /**
     * Retrieve the latest content version that has been published.
     *
     * @return ContentVersion|null The latest published content version, or null if none exists.
     */
    public function getLatestContentVersionHasPublish();

    /**
     * Retrieve the latest published content version.
     *
     * @return ContentVersion|null The latest published content version, or null if none exists.
     */
    public function getLatestPublishedContentVersion();

    /**
     * Retrieve the latest version property data.
     *
     * @return array The data of the latest version property.
     */
    public function getLatestPublishedPropertyData();

    /**
     * Retrieve the data of the latest version property.
     *
     * @return array The data of the latest version property.
     */
    public function getLatestVersionPropertyData();

    /**
     * Determine if this content is already published.
     *
     * @return bool True if published, false otherwise.
     */
    public function isPublished(): bool;

    /**
     * Get the publish time of the content.
     *
     * @return \Carbon\Carbon|null The publish time of the content, or null if not set.
     */
    public function getPublishTime();

    /**
     * Get the latest published time (includes schedule publish).
     *
     * @return \Carbon\Carbon|null The latest published time or null if not available.
     */
    public function getLatestPublishedTime();

    /**
     * Set the publishable state.
     *
     * This method allows you to define a specific state for the
     * publishable state, which can be used to control the publishing
     * behavior of the entity.
     *
     * @param  string  $state  The state to set for the publishable state.
     * @return void
     */
    public function setPublishableState(string $state);

    /**
     * Get the current publishable state.
     *
     * This method retrieves the current state representing the
     * publishable state of the entity.
     *
     * @return string The current publishable state.
     */
    public function getPublishableState();

    /**
     * Sets the versioning event for the content.
     *
     * @param  string  $event  The event to set for versioning.
     * @return void
     */
    public function setVersioningEvent(string $event);

    /**
     * Retrieve the event associated with versioning.
     *
     * @return string|null The versioning event, or null if not applicable.
     */
    public function getVersioningEvent();

    /**
     * Sets the publishable data for the content.
     *
     * @param  array  $data  An associative array containing the publishable data.
     * @return void
     */
    public function setPublishableData(array $data);

    /**
     * Retrieve the data that can be published.
     *
     * This method should return an array of data that is ready to be published.
     *
     * @return array The data that can be published.
     */
    public function getPublishableData();

    /**
     * Preloads the content version data.
     *
     * This method is responsible for preloading any necessary data related to content versions.
     *
     * @return void
     */
    public function preloadContentVersionData();

    /**
     * Retrieve the data required to preload a version.
     *
     * This method is responsible for fetching and returning an array of data
     * that is necessary to preload a specific version of the content.
     *
     * @return array An associative array containing the preload version data.
     */
    public function getPreloadVersionData();

    /**
     * Resets the state of the content version.
     *
     * This method is responsible for resetting any state or data
     * related to the content version of the implementing class.
     *
     * @return void
     */
    public function resetContentVersionState();

    /**
     * Scope a query to only include published content versions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsPublished($query, bool $condition = true);
}
