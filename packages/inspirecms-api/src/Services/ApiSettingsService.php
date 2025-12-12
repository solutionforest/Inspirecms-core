<?php

namespace SolutionForest\InspireCmsApi\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiSettingsService
{
    /**
     * Get API settings for a document type with defaults.
     */
    public function getSettings(Model $documentType): array
    {
        $stored = $documentType->api_settings ?? [];

        if (is_string($stored)) {
            $stored = json_decode($stored, true) ?? [];
        }

        return array_merge($this->getDefaults($documentType), $stored);
    }

    /**
     * Get default API settings for a document type.
     */
    public function getDefaults(Model $documentType): array
    {
        return [
            'enabled' => false,
            'slug' => Str::slug($documentType->name ?? $documentType->slug ?? 'content', '-'),
            'public_read' => config('inspirecms-api.defaults.public_read', false),
            'public_write' => config('inspirecms-api.defaults.public_write', false),
            'allowed_operations' => config('inspirecms-api.defaults.allowed_operations', ['index', 'show']),
            'default_includes' => [],
            'max_per_page' => config('inspirecms-api.defaults.max_per_page', 100),
        ];
    }

    /**
     * Check if API is enabled for a document type.
     */
    public function isEnabled(Model $documentType): bool
    {
        $settings = $this->getSettings($documentType);

        return $settings['enabled'] ?? false;
    }

    /**
     * Get the API slug for a document type.
     */
    public function getSlug(Model $documentType): string
    {
        $settings = $this->getSettings($documentType);

        return $settings['slug'] ?? Str::slug($documentType->name ?? $documentType->slug ?? 'content', '-');
    }

    /**
     * Check if an operation is allowed for a document type.
     */
    public function isOperationAllowed(Model $documentType, string $operation): bool
    {
        $settings = $this->getSettings($documentType);
        $allowedOperations = $settings['allowed_operations'] ?? [];

        return in_array($operation, $allowedOperations);
    }

    /**
     * Check if public read is enabled.
     */
    public function isPublicReadEnabled(Model $documentType): bool
    {
        $settings = $this->getSettings($documentType);

        return $settings['public_read'] ?? false;
    }

    /**
     * Check if public write is enabled.
     */
    public function isPublicWriteEnabled(Model $documentType): bool
    {
        $settings = $this->getSettings($documentType);

        return $settings['public_write'] ?? false;
    }

    /**
     * Get field API settings.
     */
    public function getFieldSettings(Model $field): array
    {
        $stored = $field->api_settings ?? [];

        if (is_string($stored)) {
            $stored = json_decode($stored, true) ?? [];
        }

        return array_merge([
            'exposed' => true,
            'readable' => true,
            'writable' => true,
            'alias' => null,
        ], $stored);
    }

    /**
     * Check if a field is exposed in the API.
     */
    public function isFieldExposed(Model $field): bool
    {
        $settings = $this->getFieldSettings($field);

        return $settings['exposed'] ?? true;
    }

    /**
     * Get the API alias for a field.
     */
    public function getFieldAlias(Model $field): ?string
    {
        $settings = $this->getFieldSettings($field);

        return $settings['alias'] ?? null;
    }
}
