{{--
    Video Block

    Renders YouTube, Vimeo, or self-hosted videos.
    Supports autoplay, loop, muted, and aspect ratio options.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $source = $settings['source'] ?? 'youtube';
    $url = $settings['url'] ?? '';
    $videoId = $settings['videoId'] ?? '';
    $autoplay = $settings['autoplay'] ?? false;
    $muted = $settings['muted'] ?? false;
    $loop = $settings['loop'] ?? false;
    $controls = $settings['controls'] ?? true;
    $aspectRatio = $settings['aspectRatio'] ?? '16/9';
    $poster = $settings['poster'] ?? '';
    $caption = $settings['caption'] ?? '';

    // Extract video ID from URL if not provided
    if (empty($videoId) && !empty($url)) {
        if ($source === 'youtube') {
            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches);
            $videoId = $matches[1] ?? '';
        } elseif ($source === 'vimeo') {
            preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
            $videoId = $matches[1] ?? '';
        }
    }

    // Build wrapper classes
    $videoClasses = ['ve-video'];
    $videoClasses[] = "ve-video--{$source}";
    if (!empty($settings['cssClass'])) {
        $videoClasses[] = $settings['cssClass'];
    }

    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $videoClasses));

    // Aspect ratio style
    $wrapperStyle = "aspect-ratio: {$aspectRatio}; position: relative; overflow: hidden;";
    $attributes['style'] = trim(($attributes['style'] ?? '') . '; ' . $wrapperStyle);

    // Build embed URL
    $embedUrl = '';
    $embedParams = [];

    if ($autoplay) $embedParams[] = 'autoplay=1';
    if ($muted) $embedParams[] = 'mute=1';
    if ($loop) $embedParams[] = 'loop=1';
    if (!$controls) $embedParams[] = 'controls=0';

    if ($source === 'youtube' && $videoId) {
        $embedUrl = "https://www.youtube.com/embed/{$videoId}";
        if ($loop) $embedParams[] = "playlist={$videoId}";
        if (!empty($embedParams)) {
            $embedUrl .= '?' . implode('&', $embedParams);
        }
    } elseif ($source === 'vimeo' && $videoId) {
        $embedUrl = "https://player.vimeo.com/video/{$videoId}";
        if (!empty($embedParams)) {
            $embedUrl .= '?' . implode('&', $embedParams);
        }
    }

    $useFigure = !empty($caption);
@endphp

@if($useFigure)
    <figure{!! $renderer->buildAttributeString($attributes) !!}>
@else
    <div{!! $renderer->buildAttributeString($attributes) !!}>
@endif

    @if($source === 'self' && !empty($url))
        <video
            class="ve-video__player"
            src="{{ $url }}"
            @if($poster) poster="{{ $poster }}" @endif
            @if($autoplay) autoplay @endif
            @if($muted) muted @endif
            @if($loop) loop @endif
            @if($controls) controls @endif
            playsinline
            style="width: 100%; height: 100%; object-fit: cover;"
        >
            Your browser does not support the video tag.
        </video>
    @elseif(!empty($embedUrl))
        <iframe
            class="ve-video__iframe"
            src="{{ $embedUrl }}"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
            loading="lazy"
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
        ></iframe>
    @else
        <div class="ve-video__placeholder" style="display: flex; align-items: center; justify-content: center; background: #f1f5f9; height: 100%;">
            <span style="color: #64748b;">No video URL provided</span>
        </div>
    @endif

@if($useFigure)
        <figcaption class="ve-video__caption" style="margin-top: 0.5rem; font-size: 0.875rem; color: #64748b; text-align: center;">
            {{ $renderer->escape($caption) }}
        </figcaption>
    </figure>
@else
    </div>
@endif
