<?php

namespace SolutionForest\InspireCmsApi\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use SolutionForest\InspireCmsApi\Services\ApiSettingsService;
use SolutionForest\InspireCmsApi\Services\FieldTransformerService;

class ContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $locale = $request->attributes->get('api_locale') ?? $request->input('locale');
        $documentType = $this->resource->documentType;

        $fieldTransformer = app(FieldTransformerService::class);
        $apiSettingsService = app(ApiSettingsService::class);

        // Get API settings for the document type
        $apiSettings = $documentType ? $apiSettingsService->getSettings($documentType) : [];
        $apiSlug = $apiSettings['slug'] ?? $documentType?->slug ?? 'content';

        // Get title (handle translatable)
        $title = $this->title;
        if (is_array($title) && $locale) {
            $title = $title[$locale] ?? reset($title);
        } elseif (is_array($title)) {
            $title = reset($title);
        }

        // Transform property data
        $attributes = $fieldTransformer->transformPropertyData($this->resource, $locale);

        // Flatten attributes if only one group
        if (count($attributes) === 1) {
            $attributes = reset($attributes);
        }

        return [
            'id' => $this->id,
            'type' => $apiSlug,
            'attributes' => array_merge([
                'title' => $title,
                'slug' => $this->slug,
                'status' => $this->isPublished() ? 'published' : 'draft',
                'locale' => $locale ?? $this->getFallbackLocale(),
            ], $attributes),
            'relationships' => $this->when($this->shouldIncludeRelationships($request), function () use ($locale) {
                return $this->transformRelationships($locale);
            }),
            'meta' => [
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
                'published_at' => $this->getPublishTime()?->toIso8601String(),
            ],
            'links' => [
                'self' => $this->getSelfLink(),
            ],
        ];
    }

    /**
     * Check if relationships should be included.
     */
    protected function shouldIncludeRelationships(Request $request): bool
    {
        return $request->has('include') || ! empty($request->attributes->get('api_settings')['default_includes'] ?? []);
    }

    /**
     * Transform relationships for the response.
     */
    protected function transformRelationships(?string $locale = null): array
    {
        $relationships = [];

        // Parent relationship
        if ($this->relationLoaded('parent') && $this->parent) {
            $relationships['parent'] = [
                'data' => [
                    'id' => $this->parent->id,
                    'type' => $this->parent->documentType?->slug ?? 'content',
                ],
            ];
        }

        // Children relationship
        if ($this->relationLoaded('children') && $this->children->isNotEmpty()) {
            $relationships['children'] = [
                'data' => $this->children->map(fn ($child) => [
                    'id' => $child->id,
                    'type' => $child->documentType?->slug ?? 'content',
                ])->toArray(),
            ];
        }

        // Document type relationship
        if ($this->relationLoaded('documentType') && $this->documentType) {
            $relationships['document_type'] = [
                'data' => [
                    'id' => $this->documentType->id,
                    'type' => 'document_types',
                    'name' => $this->documentType->name,
                    'slug' => $this->documentType->slug,
                ],
            ];
        }

        // Author relationship
        if ($this->relationLoaded('author') && $this->author) {
            $relationships['author'] = [
                'data' => [
                    'id' => $this->author->id,
                    'type' => 'users',
                    'name' => $this->author->name,
                ],
            ];
        }

        return $relationships;
    }

    /**
     * Get the self link for this resource.
     */
    protected function getSelfLink(): string
    {
        $prefix = config('inspirecms-api.prefix', 'api');
        $version = config('inspirecms-api.version', 'v1');
        $apiSettings = app(ApiSettingsService::class)->getSettings($this->documentType);
        $typeSlug = $apiSettings['slug'] ?? $this->documentType?->slug ?? 'content';

        return url("{$prefix}/{$version}/{$typeSlug}/{$this->id}");
    }
}
