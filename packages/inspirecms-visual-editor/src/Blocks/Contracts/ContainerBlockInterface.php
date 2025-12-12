<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Blocks\Contracts;

interface ContainerBlockInterface extends BlockInterface
{
    /**
     * Get the layout type for children (e.g., 'flex', 'grid', 'stack').
     */
    public function getChildrenLayout(): string;

    /**
     * Get default layout options for children.
     */
    public function getChildrenLayoutOptions(): array;

    /**
     * Wrap children in a container element.
     */
    public function wrapChildren(string $childrenHtml, array $props): string;
}
