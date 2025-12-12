<?php

namespace SolutionForest\InspireCmsApi\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\InspireCmsConfig;

class FieldTransformerService
{
    public function __construct(
        protected ApiSettingsService $apiSettingsService
    ) {}

    /**
     * Transform content property data for API output.
     */
    public function transformPropertyData(Model $content, ?string $locale = null): array
    {
        $documentType = $content->documentType;

        if (! $documentType) {
            return [];
        }

        $fields = $documentType->fields;
        $propertyData = $this->getPropertyData($content);

        $result = [];

        foreach ($fields as $field) {
            // Check if field is exposed in API
            if (! $this->apiSettingsService->isFieldExposed($field)) {
                continue;
            }

            $fieldName = $field->name;
            $alias = $this->apiSettingsService->getFieldAlias($field) ?? $fieldName;
            $groupName = $field->group?->name ?? $field->group_name ?? 'default';

            // Get the value from property data
            $value = $this->getFieldValue($propertyData, $groupName, $fieldName, $locale);

            // Transform based on field type
            $transformedValue = $this->transformFieldValue($field, $value, $locale);

            // Store in result grouped by field group
            if (! isset($result[$groupName])) {
                $result[$groupName] = [];
            }

            $result[$groupName][$alias] = $transformedValue;
        }

        return $result;
    }

    /**
     * Get the raw property data from content.
     */
    protected function getPropertyData(Model $content): array
    {
        // Try to get from latest published version
        if (method_exists($content, 'getLatestPublishedPropertyData')) {
            return $content->getLatestPublishedPropertyData() ?? [];
        }

        // Fallback to latest version
        if (method_exists($content, 'getLatestVersionPropertyData')) {
            return $content->getLatestVersionPropertyData() ?? [];
        }

        return [];
    }

    /**
     * Get a field value from property data.
     */
    protected function getFieldValue(array $propertyData, string $groupName, string $fieldName, ?string $locale = null): mixed
    {
        $groupData = $propertyData[$groupName] ?? [];
        $value = $groupData[$fieldName] ?? null;

        // Handle translatable values
        if (is_array($value) && $locale && isset($value[$locale])) {
            return $value[$locale];
        }

        // If no locale specified and value is translatable, return first available
        if (is_array($value) && ! isset($value[0])) {
            return reset($value) ?: null;
        }

        return $value;
    }

    /**
     * Transform a field value based on its type.
     */
    protected function transformFieldValue(Model $field, mixed $value, ?string $locale = null): mixed
    {
        if (is_null($value)) {
            return null;
        }

        $fieldType = $field->type;

        return match ($fieldType) {
            'media', 'image', 'file' => $this->transformMediaValue($value),
            'content_picker' => $this->transformContentPickerValue($value, $locale),
            'repeater' => $this->transformRepeaterValue($value, $field, $locale),
            'rich_editor', 'markdown' => $this->transformRichTextValue($value),
            'date', 'datetime' => $this->transformDateValue($value),
            'boolean', 'toggle' => (bool) $value,
            'number' => is_numeric($value) ? (float) $value : null,
            'json' => is_string($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }

    /**
     * Transform media field value.
     */
    protected function transformMediaValue(mixed $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        // Handle single media
        if (is_string($value) || is_int($value)) {
            return $this->getMediaData($value);
        }

        // Handle multiple media
        if (is_array($value)) {
            return array_filter(array_map(
                fn ($item) => $this->getMediaData($item),
                $value
            ));
        }

        return null;
    }

    /**
     * Get media data by ID or path.
     */
    protected function getMediaData(mixed $identifier): ?array
    {
        if (empty($identifier)) {
            return null;
        }

        // If it's already a URL or path
        if (is_string($identifier) && (str_starts_with($identifier, 'http') || str_starts_with($identifier, '/'))) {
            return [
                'url' => $identifier,
            ];
        }

        // Try to find media by ID
        try {
            $mediaClass = InspireCmsConfig::getMediaAssetModelClass();
            $media = $mediaClass::find($identifier);

            if ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->url ?? $media->getUrl(),
                    'name' => $media->name ?? $media->file_name ?? null,
                    'mime_type' => $media->mime_type ?? null,
                    'size' => $media->size ?? null,
                    'alt' => $media->alt ?? null,
                ];
            }
        } catch (\Throwable $e) {
            // Media model might not exist or have different structure
        }

        return [
            'id' => $identifier,
        ];
    }

    /**
     * Transform content picker field value.
     */
    protected function transformContentPickerValue(mixed $value, ?string $locale = null): mixed
    {
        if (empty($value)) {
            return null;
        }

        $contentClass = InspireCmsConfig::getContentModelClass();

        // Handle single content
        if (is_string($value) || is_int($value)) {
            $content = $contentClass::find($value);

            return $content ? $this->getContentReference($content, $locale) : null;
        }

        // Handle multiple content
        if (is_array($value)) {
            $contents = $contentClass::whereIn('id', $value)->get();

            return $contents->map(fn ($c) => $this->getContentReference($c, $locale))->filter()->values()->toArray();
        }

        return null;
    }

    /**
     * Get a minimal content reference.
     */
    protected function getContentReference(Model $content, ?string $locale = null): array
    {
        $title = $content->title;
        if (is_array($title) && $locale) {
            $title = $title[$locale] ?? reset($title);
        }

        return [
            'id' => $content->id,
            'title' => $title,
            'slug' => $content->slug,
            'type' => $content->documentType?->slug,
        ];
    }

    /**
     * Transform repeater field value.
     */
    protected function transformRepeaterValue(mixed $value, Model $field, ?string $locale = null): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        // Repeaters are already arrays, just ensure proper structure
        return array_values($value);
    }

    /**
     * Transform rich text field value.
     */
    protected function transformRichTextValue(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Return as-is (HTML content)
        return is_string($value) ? $value : null;
    }

    /**
     * Transform date field value.
     */
    protected function transformDateValue(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->toIso8601String();
        } catch (\Throwable $e) {
            return $value;
        }
    }

    /**
     * Get exposed fields for a document type.
     */
    public function getExposedFields(Model $documentType): Collection
    {
        return $documentType->fields->filter(
            fn ($field) => $this->apiSettingsService->isFieldExposed($field)
        );
    }
}
