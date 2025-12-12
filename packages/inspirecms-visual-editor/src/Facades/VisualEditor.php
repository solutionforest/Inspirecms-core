<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCmsVisualEditor\Rendering\BlockRenderer;

/**
 * @method static \Illuminate\Support\HtmlString renderLayout(array $layoutData, array $context = [])
 * @method static string renderBlock(array $blockData, array $context = [])
 * @method static string renderChildren(array $children, array $context = [])
 *
 * @see \SolutionForest\InspireCmsVisualEditor\Rendering\BlockRenderer
 */
class VisualEditor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BlockRenderer::class;
    }
}
