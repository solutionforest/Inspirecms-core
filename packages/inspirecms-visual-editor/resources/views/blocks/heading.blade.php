{{--
    Heading Block

    Renders heading text with configurable level (h1-h6).
    Supports text alignment and styling options.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks (usually empty)
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $level = $settings['level'] ?? 2;
    $tag = "h{$level}";
    $content = $settings['content'] ?? $settings['text'] ?? '';
    $textAlign = $settings['textAlign'] ?? 'left';

    // Build heading classes
    $headingClasses = ['ve-heading'];
    $headingClasses[] = "ve-heading--{$tag}";
    $headingClasses[] = "ve-heading--align-{$textAlign}";

    if (!empty($settings['cssClass'])) {
        $headingClasses[] = $settings['cssClass'];
    }

    // Merge with existing classes
    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $headingClasses));

    // Add text-align style if not already in styles
    if (empty($styles['textAlign'])) {
        $currentStyle = $attributes['style'] ?? '';
        $attributes['style'] = trim($currentStyle . "; text-align: {$textAlign}");
    }

    // Remove generic block id if present (heading should use its own)
    unset($attributes['id']);
    if (!empty($settings['anchorId'])) {
        $attributes['id'] = $settings['anchorId'];
    }
@endphp

<{{ $tag }}{!! $renderer->buildAttributeString($attributes) !!}>
    {!! $renderer->sanitizeHtml($content) !!}
</{{ $tag }}>
