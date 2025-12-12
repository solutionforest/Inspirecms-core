{{--
    Spacer Block

    Creates vertical or horizontal spacing between elements.
    Supports responsive height options.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks (always empty)
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $height = $settings['height'] ?? '2rem';
    $heightMobile = $settings['heightMobile'] ?? null;
    $heightTablet = $settings['heightTablet'] ?? null;

    // Build spacer classes
    $spacerClasses = ['ve-spacer'];

    if (!empty($settings['cssClass'])) {
        $spacerClasses[] = $settings['cssClass'];
    }

    // Merge with existing classes
    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $spacerClasses));

    // Build spacer styles
    $spacerStyles = [];
    $spacerStyles[] = "height: {$height}";
    $spacerStyles[] = "display: block";

    // Merge with existing styles
    $currentStyle = $attributes['style'] ?? '';
    $attributes['style'] = trim($currentStyle . '; ' . implode('; ', $spacerStyles));

    // Generate responsive CSS variable
    $blockId = $block['id'] ?? uniqid('spacer_');
    $hasResponsive = $heightMobile || $heightTablet;
@endphp

@if($hasResponsive)
<style>
    [data-block-id="{{ $blockId }}"] {
        height: {{ $height }};
    }
    @if($heightTablet)
    @media (max-width: 1024px) {
        [data-block-id="{{ $blockId }}"] {
            height: {{ $heightTablet }};
        }
    }
    @endif
    @if($heightMobile)
    @media (max-width: 640px) {
        [data-block-id="{{ $blockId }}"] {
            height: {{ $heightMobile }};
        }
    }
    @endif
</style>
@endif

<div{!! $renderer->buildAttributeString($attributes) !!} data-block-id="{{ $blockId }}" aria-hidden="true"></div>
