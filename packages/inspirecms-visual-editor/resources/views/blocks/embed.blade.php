{{--
    Embed Block

    Renders external content via iframe or HTML embed code.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $embedType = $settings['embedType'] ?? 'iframe';
    $url = $settings['url'] ?? '';
    $html = $settings['html'] ?? '';
    $width = $settings['width'] ?? '100%';
    $height = $settings['height'] ?? '400px';
    $aspectRatio = $settings['aspectRatio'] ?? '';
    $allowFullscreen = $settings['allowFullscreen'] ?? true;
    $lazyLoad = $settings['lazyLoad'] ?? true;
    $title = $settings['title'] ?? 'Embedded content';
    $caption = $settings['caption'] ?? '';

    // Build wrapper classes
    $embedClasses = ['ve-embed'];
    $embedClasses[] = "ve-embed--{$embedType}";
    if (!empty($settings['cssClass'])) {
        $embedClasses[] = $settings['cssClass'];
    }

    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $embedClasses));

    // Build wrapper styles
    $wrapperStyles = [];
    $wrapperStyles[] = "width: {$width}";

    if ($aspectRatio) {
        $wrapperStyles[] = "aspect-ratio: {$aspectRatio}";
        $wrapperStyles[] = "position: relative";
    } else {
        $wrapperStyles[] = "height: {$height}";
    }

    $attributes['style'] = trim(($attributes['style'] ?? '') . '; ' . implode('; ', $wrapperStyles));

    $useFigure = !empty($caption);
@endphp

@if($useFigure)
    <figure{!! $renderer->buildAttributeString($attributes) !!}>
@else
    <div{!! $renderer->buildAttributeString($attributes) !!}>
@endif

    @if($embedType === 'html' && !empty($html))
        {{-- Raw HTML embed - sanitized --}}
        <div class="ve-embed__content" style="width: 100%; height: 100%;">
            {!! $html !!}
        </div>
    @elseif($embedType === 'iframe' && !empty($url))
        <iframe
            class="ve-embed__iframe"
            src="{{ $url }}"
            title="{{ $title }}"
            frameborder="0"
            @if($allowFullscreen) allowfullscreen @endif
            @if($lazyLoad) loading="lazy" @endif
            @if($aspectRatio)
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
            @else
                style="width: 100%; height: 100%;"
            @endif
        ></iframe>
    @elseif($embedType === 'oembed' && !empty($url))
        {{-- oEmbed would require server-side fetching --}}
        <div class="ve-embed__oembed" data-url="{{ $url }}" style="width: 100%; height: 100%;">
            <iframe
                src="{{ $url }}"
                title="{{ $title }}"
                frameborder="0"
                @if($allowFullscreen) allowfullscreen @endif
                @if($lazyLoad) loading="lazy" @endif
                style="width: 100%; height: 100%;"
            ></iframe>
        </div>
    @else
        <div class="ve-embed__placeholder" style="display: flex; align-items: center; justify-content: center; background: #f1f5f9; height: 100%; min-height: 200px;">
            <span style="color: #64748b;">No embed content provided</span>
        </div>
    @endif

@if($useFigure)
        <figcaption class="ve-embed__caption" style="margin-top: 0.5rem; font-size: 0.875rem; color: #64748b; text-align: center;">
            {{ $renderer->escape($caption) }}
        </figcaption>
    </figure>
@else
    </div>
@endif
