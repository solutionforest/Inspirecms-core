{{--
    Container Block

    A full-width container that can hold other blocks.
    Supports max-width constraints and background options.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $tag = $settings['htmlTag'] ?? 'div';
    $maxWidth = $settings['maxWidth'] ?? null;
    $fullWidth = $settings['fullWidth'] ?? true;

    // Build container classes
    $containerClasses = ['ve-container'];
    if ($fullWidth) {
        $containerClasses[] = 've-container--full-width';
    }
    if (!empty($settings['cssClass'])) {
        $containerClasses[] = $settings['cssClass'];
    }

    // Merge with existing classes
    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $containerClasses));

    // Add max-width style if specified
    if ($maxWidth && empty($styles['maxWidth'])) {
        $currentStyle = $attributes['style'] ?? '';
        $attributes['style'] = trim($currentStyle . "; max-width: {$maxWidth}; margin-left: auto; margin-right: auto");
    }
@endphp

<{{ $tag }}{!! $renderer->buildAttributeString($attributes) !!}>
    @if(!empty($settings['innerContainer']))
        <div class="ve-container__inner" @if($maxWidth) style="max-width: {{ $maxWidth }}; margin: 0 auto;" @endif>
            {{ $children }}
        </div>
    @else
        {{ $children }}
    @endif
</{{ $tag }}>
