<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Blocks\Registry;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\VisualEditor\Blocks\Contracts\BlockInterface;
use SolutionForest\InspireCms\VisualEditor\Enums\BlockCategory;

class BlockRegistry
{
    /**
     * @var array<string, class-string<BlockInterface>>
     */
    protected static array $blocks = [];

    /**
     * @var array<string, BlockInterface>
     */
    protected static array $instances = [];

    /**
     * Register a block type.
     *
     * @param  class-string<BlockInterface>  $blockClass
     */
    public static function register(string $blockClass): void
    {
        $instance = app($blockClass);

        if (! $instance instanceof BlockInterface) {
            throw new \InvalidArgumentException(
                sprintf('Block class %s must implement %s', $blockClass, BlockInterface::class)
            );
        }

        static::$blocks[$instance->getType()] = $blockClass;
        static::$instances[$instance->getType()] = $instance;
    }

    /**
     * Register multiple block types.
     *
     * @param  array<class-string<BlockInterface>>  $blockClasses
     */
    public static function registerMany(array $blockClasses): void
    {
        foreach ($blockClasses as $blockClass) {
            static::register($blockClass);
        }
    }

    /**
     * Get a block instance by type.
     */
    public static function get(string $type): ?BlockInterface
    {
        return static::$instances[$type] ?? null;
    }

    /**
     * Check if a block type is registered.
     */
    public static function has(string $type): bool
    {
        return isset(static::$blocks[$type]);
    }

    /**
     * Get all registered blocks.
     *
     * @return Collection<string, BlockInterface>
     */
    public static function all(): Collection
    {
        return collect(static::$instances);
    }

    /**
     * Get blocks by category.
     *
     * @return Collection<string, BlockInterface>
     */
    public static function byCategory(BlockCategory|string $category): Collection
    {
        $categoryValue = $category instanceof BlockCategory ? $category->value : $category;

        return static::all()->filter(
            fn (BlockInterface $block) => $block->getCategory() === $categoryValue
        );
    }

    /**
     * Get blocks grouped by category.
     *
     * @return Collection<string, Collection<string, BlockInterface>>
     */
    public static function groupedByCategory(): Collection
    {
        return static::all()
            ->groupBy(fn (BlockInterface $block) => $block->getCategory())
            ->sortBy(function ($blocks, $category) {
                $enum = BlockCategory::tryFrom($category);

                return $enum ? $enum->getOrder() : 999;
            });
    }

    /**
     * Get container blocks only.
     *
     * @return Collection<string, BlockInterface>
     */
    public static function containers(): Collection
    {
        return static::all()->filter(fn (BlockInterface $block) => $block->isContainer());
    }

    /**
     * Clear all registered blocks.
     */
    public static function clear(): void
    {
        static::$blocks = [];
        static::$instances = [];
    }

    /**
     * Get the block data for the frontend block panel.
     */
    public static function getBlocksForPanel(): array
    {
        return static::groupedByCategory()
            ->map(function (Collection $blocks, string $categoryKey) {
                $category = BlockCategory::tryFrom($categoryKey);

                return [
                    'key' => $categoryKey,
                    'label' => $category?->getLabel() ?? ucfirst($categoryKey),
                    'icon' => $category?->getIcon() ?? 'heroicon-o-cube',
                    'blocks' => $blocks->map(fn (BlockInterface $block) => [
                        'type' => $block->getType(),
                        'label' => $block->getLabel(),
                        'icon' => $block->getIcon(),
                        'description' => $block->getDescription(),
                        'isContainer' => $block->isContainer(),
                        'preview' => $block->getPreviewImage(),
                    ])->values()->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Create a new block data structure with default props.
     */
    public static function createBlockData(string $type, ?string $id = null): ?array
    {
        $block = static::get($type);

        if (! $block) {
            return null;
        }

        return [
            'id' => $id ?? static::generateBlockId(),
            'type' => $type,
            'props' => $block->getDefaultProps(),
            'styles' => [],
            'children' => [],
        ];
    }

    /**
     * Generate a unique block ID.
     */
    public static function generateBlockId(): string
    {
        return 'block_' . uniqid();
    }

    /**
     * Validate a complete layout structure.
     */
    public static function validateLayout(array $layout): array
    {
        $errors = [];

        if (! isset($layout['root'])) {
            $errors[] = 'Layout must have a root element';

            return $errors;
        }

        $errors = array_merge($errors, static::validateBlock($layout['root'], 'root'));

        return $errors;
    }

    /**
     * Validate a single block and its children.
     */
    protected static function validateBlock(array $block, string $path): array
    {
        $errors = [];

        if (! isset($block['type'])) {
            $errors[] = "Block at {$path} is missing 'type'";

            return $errors;
        }

        if (! isset($block['id'])) {
            $errors[] = "Block at {$path} is missing 'id'";
        }

        $blockInstance = static::get($block['type']);

        if (! $blockInstance) {
            $errors[] = "Unknown block type '{$block['type']}' at {$path}";

            return $errors;
        }

        // Validate props
        $props = $block['props'] ?? [];
        $propErrors = $blockInstance->validateProps($props);
        foreach ($propErrors as $field => $message) {
            $errors[] = "Block at {$path}: {$field} - {$message}";
        }

        // Validate children
        if (isset($block['children']) && is_array($block['children'])) {
            if (! $blockInstance->isContainer()) {
                $errors[] = "Block at {$path} ({$block['type']}) cannot have children";
            } else {
                $maxChildren = $blockInstance->getMaxChildren();
                if ($maxChildren !== null && count($block['children']) > $maxChildren) {
                    $errors[] = "Block at {$path} exceeds maximum children limit of {$maxChildren}";
                }

                $allowedChildren = $blockInstance->getAllowedChildren();
                foreach ($block['children'] as $index => $child) {
                    $childPath = "{$path}.children[{$index}]";

                    if (! empty($allowedChildren) && ! in_array($child['type'] ?? '', $allowedChildren)) {
                        $errors[] = "Block type '{$child['type']}' is not allowed as child of '{$block['type']}' at {$childPath}";
                    }

                    $errors = array_merge($errors, static::validateBlock($child, $childPath));
                }
            }
        }

        return $errors;
    }
}
