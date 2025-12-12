<?php

namespace SolutionForest\InspireCmsApi\Traits;

/**
 * Trait for models that have API settings.
 *
 * Add this trait to your DocumentType or Field model to enable API settings.
 */
trait HasApiSettings
{
    /**
     * Initialize the trait.
     */
    public function initializeHasApiSettings(): void
    {
        $this->mergeCasts([
            'api_settings' => 'array',
        ]);
    }

    /**
     * Check if API is enabled for this model.
     */
    public function isApiEnabled(): bool
    {
        return $this->api_settings['enabled'] ?? false;
    }

    /**
     * Get the API slug.
     */
    public function getApiSlug(): ?string
    {
        if (! $this->isApiEnabled()) {
            return null;
        }

        return $this->api_settings['slug'] ?? $this->slug ?? null;
    }

    /**
     * Check if public read is enabled.
     */
    public function isApiPublicReadEnabled(): bool
    {
        return $this->api_settings['public_read'] ?? false;
    }

    /**
     * Check if public write is enabled.
     */
    public function isApiPublicWriteEnabled(): bool
    {
        return $this->api_settings['public_write'] ?? false;
    }

    /**
     * Get allowed API operations.
     */
    public function getAllowedApiOperations(): array
    {
        return $this->api_settings['allowed_operations'] ?? ['index', 'show'];
    }

    /**
     * Check if an API operation is allowed.
     */
    public function isApiOperationAllowed(string $operation): bool
    {
        return in_array($operation, $this->getAllowedApiOperations());
    }

    /**
     * Get the API endpoint URL for this document type.
     */
    public function getApiEndpointUrl(): ?string
    {
        if (! $this->isApiEnabled()) {
            return null;
        }

        $prefix = config('inspirecms-api.prefix', 'api');
        $version = config('inspirecms-api.version', 'v1');
        $slug = $this->getApiSlug();

        return url("{$prefix}/{$version}/{$slug}");
    }
}
