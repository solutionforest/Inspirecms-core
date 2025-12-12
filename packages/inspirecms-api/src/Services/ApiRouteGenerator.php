<?php

namespace SolutionForest\InspireCmsApi\Services;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\InspireCmsConfig;

class ApiRouteGenerator
{
    public function __construct(
        protected ApiSettingsService $apiSettingsService
    ) {}

    /**
     * Get all document types that have API enabled.
     */
    public function getApiEnabledDocumentTypes(): Collection
    {
        $documentTypeClass = InspireCmsConfig::getDocumentTypeModelClass();

        return $documentTypeClass::query()
            ->whereNotNull('api_settings')
            ->get()
            ->filter(fn ($type) => $this->apiSettingsService->isEnabled($type));
    }

    /**
     * Get the API endpoint information for all enabled document types.
     */
    public function getEndpoints(): Collection
    {
        return $this->getApiEnabledDocumentTypes()->map(function ($documentType) {
            $settings = $this->apiSettingsService->getSettings($documentType);
            $slug = $settings['slug'];
            $prefix = config('inspirecms-api.prefix', 'api');
            $version = config('inspirecms-api.version', 'v1');

            $endpoints = [];

            if (in_array('index', $settings['allowed_operations'])) {
                $endpoints['index'] = [
                    'method' => 'GET',
                    'url' => "/{$prefix}/{$version}/{$slug}",
                    'description' => 'List all items',
                ];
            }

            if (in_array('show', $settings['allowed_operations'])) {
                $endpoints['show'] = [
                    'method' => 'GET',
                    'url' => "/{$prefix}/{$version}/{$slug}/{id}",
                    'description' => 'Get a single item by ID',
                ];
                $endpoints['show_by_slug'] = [
                    'method' => 'GET',
                    'url' => "/{$prefix}/{$version}/{$slug}/slug/{slug}",
                    'description' => 'Get a single item by slug',
                ];
            }

            if (in_array('store', $settings['allowed_operations'])) {
                $endpoints['store'] = [
                    'method' => 'POST',
                    'url' => "/{$prefix}/{$version}/{$slug}",
                    'description' => 'Create a new item',
                ];
            }

            if (in_array('update', $settings['allowed_operations'])) {
                $endpoints['update'] = [
                    'method' => 'PUT',
                    'url' => "/{$prefix}/{$version}/{$slug}/{id}",
                    'description' => 'Update an existing item',
                ];
            }

            if (in_array('destroy', $settings['allowed_operations'])) {
                $endpoints['destroy'] = [
                    'method' => 'DELETE',
                    'url' => "/{$prefix}/{$version}/{$slug}/{id}",
                    'description' => 'Delete an item',
                ];
            }

            return [
                'name' => $documentType->name,
                'slug' => $slug,
                'document_type_id' => $documentType->id,
                'public_read' => $settings['public_read'],
                'public_write' => $settings['public_write'],
                'endpoints' => $endpoints,
            ];
        })->values();
    }

    /**
     * Find a document type by its API slug.
     */
    public function findDocumentTypeBySlug(string $slug)
    {
        $documentTypeClass = InspireCmsConfig::getDocumentTypeModelClass();

        // First try to find by API settings slug
        $documentType = $documentTypeClass::query()
            ->whereNotNull('api_settings')
            ->get()
            ->first(function ($type) use ($slug) {
                $settings = $this->apiSettingsService->getSettings($type);

                return ($settings['slug'] ?? null) === $slug;
            });

        // If not found, try the regular slug
        if (! $documentType) {
            $documentType = $documentTypeClass::query()
                ->where('slug', $slug)
                ->first();
        }

        return $documentType;
    }
}
