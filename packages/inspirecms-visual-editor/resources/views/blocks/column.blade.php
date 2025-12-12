{{--
    Column Block

    A flexible column container used within Grid blocks.
    Supports span, alignment, and responsive options.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $span = $settings['span'] ?? 1;
    $verticalAlign = $settings['verticalAlign'] ?? 'start'; // start, center, end, stretch
    $horizontalAlign = $settings['horizontalAlign'] ?? 'stretch'; // start, center, end, stretch

    // Build column classes
    $columnClasses = ['ve-column'];
    $columnClasses[] = "ve-column--span-{$span}";
    $columnClasses[] = "ve-column--v-{$verticalAlign}";
    $columnClasses[] = "ve-column--h-{$horizontalAlign}";

    if (!empty($settings['cssClass'])) {
        $columnClasses[] = $settings['cssClass'];
    }

    // Merge with existing classes
    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $columnClasses));

    // Build column styles
    $columnStyles = [];

    if ($span > 1) {
        $columnStyles[] = "grid-column: span {$span}";
    }

    // Flex properties for content alignment
    $columnStyles[] = "display: flex";
    $columnStyles[] = "flex-direction: column";

    $justifyMap = [
        'start' => 'flex-start',
        'center' => 'center',
        'end' => 'flex-end',
        'stretch' => 'stretch',
    ];

    $alignMap = [
        'start' => 'flex-start',
        'center' => 'center',
        'end' => 'flex-end',
        'stretch' => 'stretch',
    ];

    $columnStyles[] = "justify-content: " . ($justifyMap[$verticalAlign] ?? 'flex-start');
    $columnStyles[] = "align-items: " . ($alignMap[$horizontalAlign] ?? 'stretch');

    // Merge with existing styles
    $currentStyle = $attributes['style'] ?? '';
    $attributes['style'] = trim($currentStyle . '; ' . implode('; ', $columnStyles));
@endphp

<div{!! $renderer->buildAttributeString($attributes) !!}>
    {{ $children }}
</div>
