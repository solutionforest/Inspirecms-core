<?php

namespace SolutionForest\InspireCmsApi\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use SolutionForest\InspireCmsApi\Services\ApiSettingsService;
use SolutionForest\InspireCmsApi\Services\FieldTransformerService;

class DocumentTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $apiSettingsService = app(ApiSettingsService::class);
        $fieldTransformer = app(FieldTransformerService::class);

        $apiSettings = $apiSettingsService->getSettings($this->resource);
        $exposedFields = $fieldTransformer->getExposedFields($this->resource);

        $prefix = config('inspirecms-api.prefix', 'api');
        $version = config('inspirecms-api.version', 'v1');
        $slug = $apiSettings['slug'];

        return [
            'name' => $slug,
            'label' => $this->name,
            'description' => $this->description ?? null,
            'category' => $this->category,
            'endpoints' => $this->buildEndpoints($apiSettings, $prefix, $version, $slug),
            'fields' => $exposedFields->map(function ($field) use ($apiSettingsService) {
                $fieldSettings = $apiSettingsService->getFieldSettings($field);

                return [
                    'name' => $fieldSettings['alias'] ?? $field->name,
                    'type' => $field->type,
                    'label' => $field->label ?? $field->name,
                    'required' => $field->required ?? false,
                    'readable' => $fieldSettings['readable'] ?? true,
                    'writable' => $fieldSettings['writable'] ?? true,
                    'group' => $field->group?->name ?? 'default',
                ];
            })->values(),
            'public_read' => $apiSettings['public_read'],
            'public_write' => $apiSettings['public_write'],
        ];
    }

    /**
     * Build endpoint information.
     */
    protected function buildEndpoints(array $apiSettings, string $prefix, string $version, string $slug): array
    {
        $endpoints = [];
        $baseUrl = "/{$prefix}/{$version}/{$slug}";

        if (in_array('index', $apiSettings['allowed_operations'])) {
            $endpoints['index'] = "GET {$baseUrl}";
        }

        if (in_array('show', $apiSettings['allowed_operations'])) {
            $endpoints['show'] = "GET {$baseUrl}/{id}";
            $endpoints['show_by_slug'] = "GET {$baseUrl}/slug/{slug}";
        }

        if (in_array('store', $apiSettings['allowed_operations'])) {
            $endpoints['store'] = "POST {$baseUrl}";
        }

        if (in_array('update', $apiSettings['allowed_operations'])) {
            $endpoints['update'] = "PUT {$baseUrl}/{id}";
        }

        if (in_array('destroy', $apiSettings['allowed_operations'])) {
            $endpoints['destroy'] = "DELETE {$baseUrl}/{id}";
        }

        return $endpoints;
    }
}
