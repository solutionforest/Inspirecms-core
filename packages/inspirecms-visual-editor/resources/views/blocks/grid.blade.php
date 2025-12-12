{{--
    Grid Block

    A CSS Grid-based layout container for creating multi-column layouts.
    Supports responsive column configurations.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $columns = $settings['columns'] ?? 2;
    $gap = $settings['gap'] ?? '1rem';
    $rowGap = $settings['rowGap'] ?? $gap;
    $columnGap = $settings['columnGap'] ?? $gap;
    $minColumnWidth = $settings['minColumnWidth'] ?? '250px';
    $autoFit = $settings['autoFit'] ?? false;

    // Build grid classes
    $gridClasses = ['ve-grid'];
    $gridClasses[] = "ve-grid--cols-{$columns}";

    if (!empty($settings['cssClass'])) {
        $gridClasses[] = $settings['cssClass'];
    }

    // Merge with existing classes
    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $gridClasses));

    // Build grid styles
    $gridStyles = [];

    if ($autoFit) {
        $gridStyles[] = "grid-template-columns: repeat(auto-fit, minmax({$minColumnWidth}, 1fr))";
    } else {
        $gridStyles[] = "grid-template-columns: repeat({$columns}, 1fr)";
    }

    $gridStyles[] = "gap: {$rowGap} {$columnGap}";
    $gridStyles[] = "display: grid";

    // Merge with existing styles
    $currentStyle = $attributes['style'] ?? '';
    $attributes['style'] = trim($currentStyle . '; ' . implode('; ', $gridStyles));
@endphp

<div{!! $renderer->buildAttributeString($attributes) !!}>
    {{ $children }}
</div>
