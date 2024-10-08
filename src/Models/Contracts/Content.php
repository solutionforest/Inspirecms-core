<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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
     * Define a one-to-one relationship with the WebSetting model.
     */
    public function webSetting(): HasOne;

    /**
     * Define a one-to-one relationship for the site map.
     */
    public function siteMap(): MorphOne;

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
     * Return the children contents relation.
     *
     * @return HasMany The children contents relation.
     */
    public function children(): HasMany;

    /**
     * Get the templates associated with the document type.
     *
     * @return MorphToMany The templates associated with the document type.
     */
    public function templates(): MorphToMany;

    /**
     * Get the morph field templates associated with the document type.
     *
     * @return MorphMany The morph field templates associated with the document type.
     */
    public function templateable(): MorphMany;

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

    /**
     * Set the specified template as the default for the document type.
     *
     * @param  Template|string|int  $template  The template to set as default, which can be a Template object, a string, or an integer.
     */
    public function setAsDefaultTemplate(Template | string | int $template): void;

    public function getNestableParentIdColumn(): string;

    public function getNestableRootValue(): int | string;
}
