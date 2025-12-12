<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\AI\Services;

use SolutionForest\InspireCms\VisualEditor\AI\Contracts\AIProviderInterface;
use SolutionForest\InspireCms\VisualEditor\AI\Prompts\LayoutPromptBuilder;
use SolutionForest\InspireCms\VisualEditor\AI\Providers\AnthropicProvider;
use SolutionForest\InspireCms\VisualEditor\AI\Providers\OpenAIProvider;
use SolutionForest\InspireCms\VisualEditor\Blocks\Registry\BlockRegistry;
use SolutionForest\InspireCms\VisualEditor\Models\AIGenerationHistory;

class LayoutGeneratorService
{
    protected AIProviderInterface $provider;

    protected LayoutPromptBuilder $promptBuilder;

    public function __construct(?AIProviderInterface $provider = null)
    {
        $this->provider = $provider ?? $this->resolveProvider();
        $this->promptBuilder = new LayoutPromptBuilder;
    }

    protected function resolveProvider(): AIProviderInterface
    {
        $preferred = config('inspirecms.visual_editor.ai.provider', 'anthropic');

        $providers = [
            'anthropic' => AnthropicProvider::class,
            'openai' => OpenAIProvider::class,
        ];

        $providerClass = $providers[$preferred] ?? AnthropicProvider::class;
        $provider = app($providerClass);

        // Fallback to other available provider
        if (! $provider->isAvailable()) {
            foreach ($providers as $key => $class) {
                if ($key === $preferred) {
                    continue;
                }
                $fallback = app($class);
                if ($fallback->isAvailable()) {
                    return $fallback;
                }
            }
        }

        return $provider;
    }

    /**
     * Generate a complete layout from a text description.
     */
    public function generate(string $description, array $options = []): array
    {
        $startTime = microtime(true);

        if (! $this->provider->isAvailable()) {
            return [
                'success' => false,
                'layout' => null,
                'error' => 'No AI provider available. Please configure an API key.',
            ];
        }

        // Build the prompt
        $prompt = $this->promptBuilder->buildLayoutPrompt($description, [
            'template' => $options['template'] ?? null,
            'style' => $options['style'] ?? 'modern',
            'blocks' => $this->getAvailableBlocksSchema(),
        ]);

        // Generate the layout
        $result = $this->provider->generateJson($prompt, $this->getLayoutSchema(), [
            'system' => $this->promptBuilder->getSystemPrompt(),
        ]);

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);

        if ($result['success'] && $this->validateLayout($result['data'])) {
            // Transform and enhance the layout
            $layout = $this->transformLayout($result['data']);

            // Log success
            AIGenerationHistory::logSuccess(
                type: 'layout',
                prompt: $description,
                result: ['layout' => $layout],
                provider: $this->provider->getName(),
                model: $this->provider->getDefaultModel(),
                inputTokens: $result['inputTokens'],
                outputTokens: $result['outputTokens'],
                durationMs: $durationMs,
                context: $options
            );

            return [
                'success' => true,
                'layout' => $layout,
                'error' => null,
            ];
        }

        // Log error
        AIGenerationHistory::logError(
            type: 'layout',
            prompt: $description,
            errorMessage: $result['error'] ?? 'Invalid layout structure',
            provider: $this->provider->getName(),
            model: $this->provider->getDefaultModel(),
            context: $options
        );

        return [
            'success' => false,
            'layout' => null,
            'error' => $result['error'] ?? 'Failed to generate valid layout',
        ];
    }

    /**
     * Suggest next blocks based on current layout context.
     */
    public function suggestNextBlocks(array $context): array
    {
        if (! $this->provider->isAvailable()) {
            return [
                'success' => false,
                'suggestions' => [],
                'error' => 'No AI provider available',
            ];
        }

        $prompt = $this->promptBuilder->buildSuggestionPrompt($context);

        $result = $this->provider->generateJson($prompt, [], [
            'system' => 'You are a web design assistant. Suggest appropriate blocks to add to a page layout.',
        ]);

        if ($result['success']) {
            return [
                'success' => true,
                'suggestions' => $result['data']['suggestions'] ?? [],
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'suggestions' => [],
            'error' => $result['error'],
        ];
    }

    /**
     * Generate content for a specific block.
     */
    public function generateBlockContent(string $blockType, array $context): array
    {
        if (! $this->provider->isAvailable()) {
            return [
                'success' => false,
                'content' => null,
                'error' => 'No AI provider available',
            ];
        }

        $prompt = $this->promptBuilder->buildContentPrompt($blockType, $context);

        $result = $this->provider->generateJson($prompt);

        if ($result['success']) {
            return [
                'success' => true,
                'content' => $result['data'],
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'content' => null,
            'error' => $result['error'],
        ];
    }

    /**
     * Get available blocks schema for the AI.
     */
    protected function getAvailableBlocksSchema(): array
    {
        return BlockRegistry::all()
            ->map(fn ($block) => [
                'type' => $block->getType(),
                'label' => $block->getLabel(),
                'description' => $block->getDescription(),
                'isContainer' => $block->isContainer(),
                'defaultProps' => $block->getDefaultProps(),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get the layout schema for JSON generation.
     */
    protected function getLayoutSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'root' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'type' => ['type' => 'string'],
                        'props' => ['type' => 'object'],
                        'children' => ['type' => 'array'],
                    ],
                    'required' => ['id', 'type', 'props', 'children'],
                ],
            ],
            'required' => ['root'],
        ];
    }

    /**
     * Validate the generated layout structure.
     */
    protected function validateLayout(?array $layout): bool
    {
        if (! $layout || ! isset($layout['root'])) {
            return false;
        }

        return $this->validateBlock($layout['root']);
    }

    /**
     * Validate a single block.
     */
    protected function validateBlock(array $block): bool
    {
        if (! isset($block['type'])) {
            return false;
        }

        // Check if block type exists
        if (! BlockRegistry::has($block['type'])) {
            return false;
        }

        // Validate children
        foreach ($block['children'] ?? [] as $child) {
            if (! $this->validateBlock($child)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Transform and enhance the AI-generated layout.
     */
    protected function transformLayout(array $layout): array
    {
        $layout['version'] = '1.0';
        $layout['root'] = $this->transformBlock($layout['root']);

        return $layout;
    }

    /**
     * Transform a single block, ensuring IDs and proper structure.
     */
    protected function transformBlock(array $block): array
    {
        // Ensure ID exists
        if (! isset($block['id']) || empty($block['id'])) {
            $block['id'] = BlockRegistry::generateBlockId();
        }

        // Ensure props exist
        $block['props'] = $block['props'] ?? [];

        // Ensure styles exist
        $block['styles'] = $block['styles'] ?? [];

        // Ensure children is an array
        $block['children'] = $block['children'] ?? [];

        // Get block defaults and merge
        $blockType = BlockRegistry::get($block['type']);
        if ($blockType) {
            $defaults = $blockType->getDefaultProps();
            $block['props'] = array_merge($defaults, $block['props']);
        }

        // Transform children
        $block['children'] = array_map(
            fn ($child) => $this->transformBlock($child),
            $block['children']
        );

        return $block;
    }
}
