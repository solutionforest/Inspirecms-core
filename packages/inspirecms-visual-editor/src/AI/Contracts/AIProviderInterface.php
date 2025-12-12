<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\AI\Contracts;

interface AIProviderInterface
{
    /**
     * Get the provider name.
     */
    public function getName(): string;

    /**
     * Get the default model for this provider.
     */
    public function getDefaultModel(): string;

    /**
     * Get available models for this provider.
     */
    public function getAvailableModels(): array;

    /**
     * Check if the provider is configured and available.
     */
    public function isAvailable(): bool;

    /**
     * Generate a completion from the AI.
     *
     * @return array{
     *     success: bool,
     *     content: string|null,
     *     inputTokens: int|null,
     *     outputTokens: int|null,
     *     error: string|null
     * }
     */
    public function complete(string $prompt, array $options = []): array;

    /**
     * Generate a structured JSON response from the AI.
     *
     * @return array{
     *     success: bool,
     *     data: array|null,
     *     inputTokens: int|null,
     *     outputTokens: int|null,
     *     error: string|null
     * }
     */
    public function generateJson(string $prompt, array $schema = [], array $options = []): array;
}
