<?php

namespace SolutionForest\InspireCmsVisualEditor\Rendering;

use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use SolutionForest\InspireCmsVisualEditor\Blocks\Contracts\BlockInterface;
use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;

class BlockRenderer
{
    protected array $renderStack = [];

    protected int $maxDepth = 50;

    public function __construct()
    {
        //
    }

    /**
     * Render a complete layout from JSON data
     */
    public function renderLayout(array $layoutData, array $context = []): HtmlString
    {
        $this->renderStack = [];

        $html = $this->renderBlock($layoutData, $context);

        return new HtmlString($html);
    }

    /**
     * Render a single block and its children recursively
     */
    public function renderBlock(array $blockData, array $context = []): string
    {
        if (empty($blockData) || !isset($blockData['type'])) {
            return '';
        }

        // Prevent infinite recursion
        if (count($this->renderStack) >= $this->maxDepth) {
            return '<!-- Max nesting depth reached -->';
        }

        $blockId = $blockData['id'] ?? uniqid('block_');
        $this->renderStack[] = $blockId;

        try {
            $blockType = $blockData['type'];
            $block = BlockRegistry::get($blockType);

            if (!$block) {
                return $this->renderUnknownBlock($blockData);
            }

            // Prepare block attributes
            $attributes = $this->prepareAttributes($blockData, $block);

            // Prepare children HTML
            $childrenHtml = '';
            if (!empty($blockData['children']) && is_array($blockData['children'])) {
                $childrenHtml = $this->renderChildren($blockData['children'], $context);
            }

            // Determine the view to use
            $viewName = $this->getBlockViewName($blockType);

            if (!View::exists($viewName)) {
                return $this->renderFallback($blockData, $attributes, $childrenHtml);
            }

            return View::make($viewName, [
                'block' => $blockData,
                'attributes' => $attributes,
                'children' => new HtmlString($childrenHtml),
                'settings' => $blockData['settings'] ?? [],
                'styles' => $blockData['styles'] ?? [],
                'context' => $context,
                'renderer' => $this,
            ])->render();

        } finally {
            array_pop($this->renderStack);
        }
    }

    /**
     * Render multiple children blocks
     */
    public function renderChildren(array $children, array $context = []): string
    {
        $html = '';

        foreach ($children as $child) {
            if (is_array($child)) {
                $html .= $this->renderBlock($child, $context);
            }
        }

        return $html;
    }

    /**
     * Get the view name for a block type
     */
    protected function getBlockViewName(string $blockType): string
    {
        // Convert block type to view name (e.g., 'heading' -> 'visual-editor::blocks.heading')
        $viewName = str_replace(['_', '-'], '.', strtolower($blockType));

        return "visual-editor::blocks.{$viewName}";
    }

    /**
     * Prepare HTML attributes from block data
     */
    protected function prepareAttributes(array $blockData, BlockInterface $block): array
    {
        $attributes = [];
        $settings = $blockData['settings'] ?? [];
        $styles = $blockData['styles'] ?? [];

        // Add block ID
        $attributes['id'] = $blockData['id'] ?? null;

        // Add CSS classes
        $classes = ['ve-block', "ve-block-{$blockData['type']}"];

        if (!empty($settings['cssClass'])) {
            $classes[] = $settings['cssClass'];
        }

        if (!empty($settings['className'])) {
            $classes[] = $settings['className'];
        }

        $attributes['class'] = implode(' ', array_filter($classes));

        // Build inline styles
        $inlineStyles = $this->buildInlineStyles($styles);
        if (!empty($inlineStyles)) {
            $attributes['style'] = $inlineStyles;
        }

        // Add data attributes
        $attributes['data-block-type'] = $blockData['type'];

        if (!empty($blockData['id'])) {
            $attributes['data-block-id'] = $blockData['id'];
        }

        return $attributes;
    }

    /**
     * Build inline CSS styles from styles array
     */
    protected function buildInlineStyles(array $styles): string
    {
        $cssProperties = [];

        // Spacing
        if (!empty($styles['margin'])) {
            $cssProperties[] = "margin: {$styles['margin']}";
        }
        if (!empty($styles['marginTop'])) {
            $cssProperties[] = "margin-top: {$styles['marginTop']}";
        }
        if (!empty($styles['marginRight'])) {
            $cssProperties[] = "margin-right: {$styles['marginRight']}";
        }
        if (!empty($styles['marginBottom'])) {
            $cssProperties[] = "margin-bottom: {$styles['marginBottom']}";
        }
        if (!empty($styles['marginLeft'])) {
            $cssProperties[] = "margin-left: {$styles['marginLeft']}";
        }

        if (!empty($styles['padding'])) {
            $cssProperties[] = "padding: {$styles['padding']}";
        }
        if (!empty($styles['paddingTop'])) {
            $cssProperties[] = "padding-top: {$styles['paddingTop']}";
        }
        if (!empty($styles['paddingRight'])) {
            $cssProperties[] = "padding-right: {$styles['paddingRight']}";
        }
        if (!empty($styles['paddingBottom'])) {
            $cssProperties[] = "padding-bottom: {$styles['paddingBottom']}";
        }
        if (!empty($styles['paddingLeft'])) {
            $cssProperties[] = "padding-left: {$styles['paddingLeft']}";
        }

        // Background
        if (!empty($styles['backgroundColor'])) {
            $cssProperties[] = "background-color: {$styles['backgroundColor']}";
        }
        if (!empty($styles['backgroundImage'])) {
            $cssProperties[] = "background-image: url('{$styles['backgroundImage']}')";
        }
        if (!empty($styles['backgroundSize'])) {
            $cssProperties[] = "background-size: {$styles['backgroundSize']}";
        }
        if (!empty($styles['backgroundPosition'])) {
            $cssProperties[] = "background-position: {$styles['backgroundPosition']}";
        }
        if (!empty($styles['backgroundRepeat'])) {
            $cssProperties[] = "background-repeat: {$styles['backgroundRepeat']}";
        }

        // Border
        if (!empty($styles['borderWidth'])) {
            $cssProperties[] = "border-width: {$styles['borderWidth']}";
        }
        if (!empty($styles['borderStyle'])) {
            $cssProperties[] = "border-style: {$styles['borderStyle']}";
        }
        if (!empty($styles['borderColor'])) {
            $cssProperties[] = "border-color: {$styles['borderColor']}";
        }
        if (!empty($styles['borderRadius'])) {
            $cssProperties[] = "border-radius: {$styles['borderRadius']}";
        }

        // Typography
        if (!empty($styles['color'])) {
            $cssProperties[] = "color: {$styles['color']}";
        }
        if (!empty($styles['fontSize'])) {
            $cssProperties[] = "font-size: {$styles['fontSize']}";
        }
        if (!empty($styles['fontWeight'])) {
            $cssProperties[] = "font-weight: {$styles['fontWeight']}";
        }
        if (!empty($styles['fontFamily'])) {
            $cssProperties[] = "font-family: {$styles['fontFamily']}";
        }
        if (!empty($styles['lineHeight'])) {
            $cssProperties[] = "line-height: {$styles['lineHeight']}";
        }
        if (!empty($styles['letterSpacing'])) {
            $cssProperties[] = "letter-spacing: {$styles['letterSpacing']}";
        }
        if (!empty($styles['textAlign'])) {
            $cssProperties[] = "text-align: {$styles['textAlign']}";
        }
        if (!empty($styles['textTransform'])) {
            $cssProperties[] = "text-transform: {$styles['textTransform']}";
        }
        if (!empty($styles['textDecoration'])) {
            $cssProperties[] = "text-decoration: {$styles['textDecoration']}";
        }

        // Layout
        if (!empty($styles['width'])) {
            $cssProperties[] = "width: {$styles['width']}";
        }
        if (!empty($styles['maxWidth'])) {
            $cssProperties[] = "max-width: {$styles['maxWidth']}";
        }
        if (!empty($styles['minWidth'])) {
            $cssProperties[] = "min-width: {$styles['minWidth']}";
        }
        if (!empty($styles['height'])) {
            $cssProperties[] = "height: {$styles['height']}";
        }
        if (!empty($styles['maxHeight'])) {
            $cssProperties[] = "max-height: {$styles['maxHeight']}";
        }
        if (!empty($styles['minHeight'])) {
            $cssProperties[] = "min-height: {$styles['minHeight']}";
        }

        // Flexbox
        if (!empty($styles['display'])) {
            $cssProperties[] = "display: {$styles['display']}";
        }
        if (!empty($styles['flexDirection'])) {
            $cssProperties[] = "flex-direction: {$styles['flexDirection']}";
        }
        if (!empty($styles['justifyContent'])) {
            $cssProperties[] = "justify-content: {$styles['justifyContent']}";
        }
        if (!empty($styles['alignItems'])) {
            $cssProperties[] = "align-items: {$styles['alignItems']}";
        }
        if (!empty($styles['flexWrap'])) {
            $cssProperties[] = "flex-wrap: {$styles['flexWrap']}";
        }
        if (!empty($styles['gap'])) {
            $cssProperties[] = "gap: {$styles['gap']}";
        }

        // Grid
        if (!empty($styles['gridTemplateColumns'])) {
            $cssProperties[] = "grid-template-columns: {$styles['gridTemplateColumns']}";
        }
        if (!empty($styles['gridTemplateRows'])) {
            $cssProperties[] = "grid-template-rows: {$styles['gridTemplateRows']}";
        }
        if (!empty($styles['gridGap'])) {
            $cssProperties[] = "grid-gap: {$styles['gridGap']}";
        }

        // Effects
        if (!empty($styles['boxShadow'])) {
            $cssProperties[] = "box-shadow: {$styles['boxShadow']}";
        }
        if (!empty($styles['opacity'])) {
            $cssProperties[] = "opacity: {$styles['opacity']}";
        }
        if (!empty($styles['overflow'])) {
            $cssProperties[] = "overflow: {$styles['overflow']}";
        }
        if (!empty($styles['zIndex'])) {
            $cssProperties[] = "z-index: {$styles['zIndex']}";
        }

        // Position
        if (!empty($styles['position'])) {
            $cssProperties[] = "position: {$styles['position']}";
        }
        if (isset($styles['top'])) {
            $cssProperties[] = "top: {$styles['top']}";
        }
        if (isset($styles['right'])) {
            $cssProperties[] = "right: {$styles['right']}";
        }
        if (isset($styles['bottom'])) {
            $cssProperties[] = "bottom: {$styles['bottom']}";
        }
        if (isset($styles['left'])) {
            $cssProperties[] = "left: {$styles['left']}";
        }

        return implode('; ', $cssProperties);
    }

    /**
     * Render unknown block type
     */
    protected function renderUnknownBlock(array $blockData): string
    {
        if (config('app.debug')) {
            return "<!-- Unknown block type: {$blockData['type']} -->";
        }

        return '';
    }

    /**
     * Fallback rendering when view doesn't exist
     */
    protected function renderFallback(array $blockData, array $attributes, string $childrenHtml): string
    {
        $tag = $this->getDefaultTag($blockData['type']);
        $attrString = $this->buildAttributeString($attributes);

        if ($this->isVoidElement($tag)) {
            return "<{$tag}{$attrString} />";
        }

        $content = $blockData['settings']['content'] ?? '';

        return "<{$tag}{$attrString}>{$content}{$childrenHtml}</{$tag}>";
    }

    /**
     * Get default HTML tag for block type
     */
    protected function getDefaultTag(string $blockType): string
    {
        return match ($blockType) {
            'heading' => 'h2',
            'text' => 'div',
            'button' => 'a',
            'image' => 'img',
            'container', 'section', 'grid', 'column' => 'div',
            'spacer' => 'div',
            'divider' => 'hr',
            default => 'div',
        };
    }

    /**
     * Check if HTML element is void (self-closing)
     */
    protected function isVoidElement(string $tag): bool
    {
        return in_array($tag, ['img', 'hr', 'br', 'input', 'meta', 'link']);
    }

    /**
     * Build HTML attribute string
     */
    public function buildAttributeString(array $attributes): string
    {
        $parts = [];

        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }

            if ($value === true) {
                $parts[] = e($key);
            } else {
                $parts[] = e($key) . '="' . e($value) . '"';
            }
        }

        return $parts ? ' ' . implode(' ', $parts) : '';
    }

    /**
     * Get sanitized content (XSS protection)
     */
    public function sanitizeHtml(string $html): string
    {
        // Basic XSS protection - in production, consider using a library like HTMLPurifier
        $allowed = '<p><br><strong><b><em><i><u><s><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><span><div>';

        return strip_tags($html, $allowed);
    }

    /**
     * Escape content for safe HTML output
     */
    public function escape(string $content): string
    {
        return e($content);
    }
}
