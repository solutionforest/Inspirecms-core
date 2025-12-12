<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\AI\Providers;

use Illuminate\Support\Facades\Http;
use SolutionForest\InspireCms\VisualEditor\AI\Contracts\AIProviderInterface;

class OpenAIProvider implements AIProviderInterface
{
    protected string $apiKey;

    protected string $baseUrl = 'https://api.openai.com/v1';

    protected string $defaultModel = 'gpt-4-turbo-preview';

    public function __construct()
    {
        $this->apiKey = config('inspirecms.visual_editor.ai.openai.api_key', '');
        $this->baseUrl = config('inspirecms.visual_editor.ai.openai.base_url', $this->baseUrl);
        $this->defaultModel = config('inspirecms.visual_editor.ai.openai.model', $this->defaultModel);
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function getDefaultModel(): string
    {
        return $this->defaultModel;
    }

    public function getAvailableModels(): array
    {
        return [
            'gpt-4-turbo-preview',
            'gpt-4',
            'gpt-4-0125-preview',
            'gpt-3.5-turbo',
        ];
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }

    public function complete(string $prompt, array $options = []): array
    {
        if (! $this->isAvailable()) {
            return [
                'success' => false,
                'content' => null,
                'inputTokens' => null,
                'outputTokens' => null,
                'error' => 'OpenAI API key not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(120)->post("{$this->baseUrl}/chat/completions", [
                'model' => $options['model'] ?? $this->defaultModel,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $options['system'] ?? 'You are a helpful assistant that generates page layouts.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['maxTokens'] ?? 4000,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'inputTokens' => $data['usage']['prompt_tokens'] ?? null,
                    'outputTokens' => $data['usage']['completion_tokens'] ?? null,
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'content' => null,
                'inputTokens' => null,
                'outputTokens' => null,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'content' => null,
                'inputTokens' => null,
                'outputTokens' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function generateJson(string $prompt, array $schema = [], array $options = []): array
    {
        $systemPrompt = ($options['system'] ?? 'You are a helpful assistant that generates page layouts.') .
            "\n\nIMPORTANT: You must respond with valid JSON only. No markdown, no explanation, just the JSON object.";

        if (! empty($schema)) {
            $systemPrompt .= "\n\nThe JSON must follow this schema:\n" . json_encode($schema, JSON_PRETTY_PRINT);
        }

        $result = $this->complete($prompt, array_merge($options, [
            'system' => $systemPrompt,
        ]));

        if (! $result['success']) {
            return [
                'success' => false,
                'data' => null,
                'inputTokens' => $result['inputTokens'],
                'outputTokens' => $result['outputTokens'],
                'error' => $result['error'],
            ];
        }

        // Extract JSON from response
        $content = $result['content'];

        // Try to extract JSON if wrapped in markdown code blocks
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $content = $matches[1];
        }

        $data = json_decode(trim($content), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'data' => null,
                'inputTokens' => $result['inputTokens'],
                'outputTokens' => $result['outputTokens'],
                'error' => 'Failed to parse JSON response: ' . json_last_error_msg(),
            ];
        }

        return [
            'success' => true,
            'data' => $data,
            'inputTokens' => $result['inputTokens'],
            'outputTokens' => $result['outputTokens'],
            'error' => null,
        ];
    }
}
