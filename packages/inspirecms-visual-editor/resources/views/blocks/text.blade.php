{{--
    Text Block

    Renders rich text content with HTML formatting support.
    Content is sanitized to prevent XSS attacks.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks (usually empty)
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $tag = $settings['htmlTag'] ?? 'div';
    $content = $settings['content'] ?? $settings['text'] ?? '';
    $textAlign = $settings['textAlign'] ?? 'left';
    $columns = $settings['columns'] ?? 1;
    $dropCap = $settings['dropCap'] ?? false;

    // Build text classes
    $textClasses = ['ve-text'];
    $textClasses[] = "ve-text--align-{$textAlign}";

    if ($columns > 1) {
        $textClasses[] = "ve-text--columns-{$columns}";
    }

    if ($dropCap) {
        $textClasses[] = 've-text--drop-cap';
    }

    if (!empty($settings['cssClass'])) {
        $textClasses[] = $settings['cssClass'];
    }

    // Merge with existing classes
    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $textClasses));

    // Build additional styles
    $additionalStyles = [];

    if (empty($styles['textAlign'])) {
        $additionalStyles[] = "text-align: {$textAlign}";
    }

    if ($columns > 1) {
        $additionalStyles[] = "column-count: {$columns}";
        $columnGap = $settings['columnGap'] ?? '2rem';
        $additionalStyles[] = "column-gap: {$columnGap}";
    }

    if (!empty($additionalStyles)) {
        $currentStyle = $attributes['style'] ?? '';
        $attributes['style'] = trim($currentStyle . '; ' . implode('; ', $additionalStyles));
    }
@endphp

<{{ $tag }}{!! $renderer->buildAttributeString($attributes) !!}>
    {!! $renderer->sanitizeHtml($content) !!}
</{{ $tag }}>
