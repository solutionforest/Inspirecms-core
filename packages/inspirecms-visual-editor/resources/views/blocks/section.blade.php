{{--
    Section Block

    A semantic section element with optional background and layout options.
    Typically used for major page sections.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $tag = $settings['htmlTag'] ?? 'section';
    $contentWidth = $settings['contentWidth'] ?? 'boxed'; // 'full', 'boxed', 'narrow'
    $verticalAlign = $settings['verticalAlign'] ?? 'top';

    // Build section classes
    $sectionClasses = ['ve-section'];
    $sectionClasses[] = "ve-section--{$contentWidth}";
    $sectionClasses[] = "ve-section--align-{$verticalAlign}";

    if (!empty($settings['cssClass'])) {
        $sectionClasses[] = $settings['cssClass'];
    }

    // Merge with existing classes
    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $sectionClasses));

    // Content width mapping
    $contentMaxWidth = match($contentWidth) {
        'full' => '100%',
        'narrow' => '800px',
        'boxed' => '1200px',
        default => '1200px',
    };
@endphp

<{{ $tag }}{!! $renderer->buildAttributeString($attributes) !!}>
    @if($contentWidth !== 'full')
        <div class="ve-section__content" style="max-width: {{ $contentMaxWidth }}; margin: 0 auto; width: 100%;">
            {{ $children }}
        </div>
    @else
        {{ $children }}
    @endif
</{{ $tag }}>
