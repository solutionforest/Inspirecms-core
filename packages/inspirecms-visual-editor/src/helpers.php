<?php

use Illuminate\Support\HtmlString;
use SolutionForest\InspireCmsVisualEditor\Rendering\BlockRenderer;

if (! function_exists('render_visual_layout')) {
    /**
     * Render a visual editor layout to HTML
     *
     * @param  array  $layoutData  The layout data (JSON decoded)
     * @param  array  $context  Additional context to pass to block templates
     */
    function render_visual_layout(array $layoutData, array $context = []): HtmlString
    {
        /** @var BlockRenderer $renderer */
        $renderer = app(BlockRenderer::class);

        return $renderer->renderLayout($layoutData, $context);
    }
}

if (! function_exists('render_visual_block')) {
    /**
     * Render a single visual editor block to HTML
     *
     * @param  array  $blockData  The block data
     * @param  array  $context  Additional context to pass to block template
     */
    function render_visual_block(array $blockData, array $context = []): string
    {
        /** @var BlockRenderer $renderer */
        $renderer = app(BlockRenderer::class);

        return $renderer->renderBlock($blockData, $context);
    }
}
