{{--
    Image Block

    Renders an image with optional caption, link, and responsive options.
    Supports lazy loading and various sizing modes.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks (usually empty)
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $src = $settings['src'] ?? $settings['url'] ?? '';
    $alt = $settings['alt'] ?? '';
    $title = $settings['title'] ?? null;
    $caption = $settings['caption'] ?? null;
    $link = $settings['link'] ?? null;
    $linkTarget = $settings['linkTarget'] ?? '_self';
    $linkRel = $settings['linkRel'] ?? ($linkTarget === '_blank' ? 'noopener noreferrer' : null);
    $width = $settings['width'] ?? null;
    $height = $settings['height'] ?? null;
    $objectFit = $settings['objectFit'] ?? 'cover'; // contain, cover, fill, none, scale-down
    $alignment = $settings['alignment'] ?? 'center'; // left, center, right
    $lazyLoad = $settings['lazyLoad'] ?? true;
    $aspectRatio = $settings['aspectRatio'] ?? null; // e.g., '16/9', '4/3', '1/1'
    $rounded = $settings['rounded'] ?? false;

    // Build wrapper classes
    $wrapperClasses = ['ve-image'];
    $wrapperClasses[] = "ve-image--align-{$alignment}";
    $wrapperClasses[] = "ve-image--fit-{$objectFit}";

    if ($rounded) {
        $wrapperClasses[] = 've-image--rounded';
    }

    if (!empty($settings['cssClass'])) {
        $wrapperClasses[] = $settings['cssClass'];
    }

    // Build wrapper styles
    $wrapperStyles = [];

    // Alignment
    $alignmentStyles = match($alignment) {
        'left' => 'margin-right: auto',
        'right' => 'margin-left: auto',
        'center' => 'margin-left: auto; margin-right: auto',
        default => '',
    };
    if ($alignmentStyles) {
        $wrapperStyles[] = $alignmentStyles;
    }

    // Width constraint
    if ($width) {
        $wrapperStyles[] = "max-width: {$width}";
    }

    // Aspect ratio
    if ($aspectRatio) {
        $wrapperStyles[] = "aspect-ratio: {$aspectRatio}";
    }

    // Build image attributes
    $imgAttributes = [
        'src' => $src,
        'alt' => $alt,
        'class' => 've-image__img',
    ];

    if ($title) {
        $imgAttributes['title'] = $title;
    }

    if ($width) {
        $imgAttributes['width'] = preg_replace('/[^0-9]/', '', $width);
    }

    if ($height) {
        $imgAttributes['height'] = preg_replace('/[^0-9]/', '', $height);
    }

    if ($lazyLoad) {
        $imgAttributes['loading'] = 'lazy';
        $imgAttributes['decoding'] = 'async';
    }

    // Image styles
    $imgStyles = [];
    $imgStyles[] = "object-fit: {$objectFit}";

    if ($aspectRatio) {
        $imgStyles[] = 'width: 100%';
        $imgStyles[] = 'height: 100%';
    }

    if ($rounded) {
        $imgStyles[] = 'border-radius: inherit';
    }

    $imgAttributes['style'] = implode('; ', $imgStyles);

    // Merge wrapper attributes
    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $wrapperClasses));
    if (!empty($wrapperStyles)) {
        $currentStyle = $attributes['style'] ?? '';
        $attributes['style'] = trim($currentStyle . '; ' . implode('; ', $wrapperStyles));
    }

    // Remove id from wrapper, use data attribute instead
    unset($attributes['id']);
    if (!empty($block['id'])) {
        $attributes['data-block-id'] = $block['id'];
    }

    // Check if we should use figure (has caption)
    $useFigure = !empty($caption);
@endphp

@if($useFigure)
    <figure{!! $renderer->buildAttributeString($attributes) !!}>
        @if($link)
            <a href="{{ $link }}" @if($linkTarget !== '_self') target="{{ $linkTarget }}" @endif @if($linkRel) rel="{{ $linkRel }}" @endif class="ve-image__link">
                <img{!! $renderer->buildAttributeString($imgAttributes) !!} />
            </a>
        @else
            <img{!! $renderer->buildAttributeString($imgAttributes) !!} />
        @endif

        <figcaption class="ve-image__caption">
            {{ $renderer->escape($caption) }}
        </figcaption>
    </figure>
@else
    <div{!! $renderer->buildAttributeString($attributes) !!}>
        @if($link)
            <a href="{{ $link }}" @if($linkTarget !== '_self') target="{{ $linkTarget }}" @endif @if($linkRel) rel="{{ $linkRel }}" @endif class="ve-image__link">
                <img{!! $renderer->buildAttributeString($imgAttributes) !!} />
            </a>
        @else
            <img{!! $renderer->buildAttributeString($imgAttributes) !!} />
        @endif
    </div>
@endif
