{{--
    Divider Block

    Creates a horizontal line/divider between content sections.
    Supports various styles and widths.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks (always empty)
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $width = $settings['width'] ?? '100%';
    $thickness = $settings['thickness'] ?? '1px';
    $lineStyle = $settings['style'] ?? 'solid'; // solid, dashed, dotted, double
    $color = $settings['color'] ?? '#e5e7eb';
    $alignment = $settings['alignment'] ?? 'center'; // left, center, right
    $verticalSpacing = $settings['verticalSpacing'] ?? '1rem';

    // Build divider classes
    $dividerClasses = ['ve-divider'];
    $dividerClasses[] = "ve-divider--{$lineStyle}";
    $dividerClasses[] = "ve-divider--align-{$alignment}";

    if (!empty($settings['cssClass'])) {
        $dividerClasses[] = $settings['cssClass'];
    }

    // Merge with existing classes
    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $dividerClasses));

    // Build divider styles
    $dividerStyles = [];
    $dividerStyles[] = "border: none";
    $dividerStyles[] = "border-top-width: {$thickness}";
    $dividerStyles[] = "border-top-style: {$lineStyle}";
    $dividerStyles[] = "border-top-color: {$color}";
    $dividerStyles[] = "width: {$width}";
    $dividerStyles[] = "margin-top: {$verticalSpacing}";
    $dividerStyles[] = "margin-bottom: {$verticalSpacing}";

    // Alignment
    $marginStyle = match($alignment) {
        'left' => 'margin-right: auto; margin-left: 0',
        'right' => 'margin-left: auto; margin-right: 0',
        'center' => 'margin-left: auto; margin-right: auto',
        default => '',
    };
    if ($marginStyle) {
        $dividerStyles[] = $marginStyle;
    }

    // Merge with existing styles (but our styles take precedence for hr reset)
    $currentStyle = $attributes['style'] ?? '';
    $attributes['style'] = implode('; ', $dividerStyles) . ($currentStyle ? '; ' . $currentStyle : '');

    // Remove id from attributes for hr
    unset($attributes['id']);
    if (!empty($block['id'])) {
        $attributes['data-block-id'] = $block['id'];
    }
@endphp

<hr{!! $renderer->buildAttributeString($attributes) !!} />
