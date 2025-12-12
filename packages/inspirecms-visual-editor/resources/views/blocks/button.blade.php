{{--
    Button Block

    Renders a clickable button/link with various styling options.
    Can be rendered as <a> or <button> element.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks (usually empty)
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $text = $settings['text'] ?? $settings['label'] ?? 'Click here';
    $url = $settings['url'] ?? $settings['href'] ?? '#';
    $target = $settings['target'] ?? '_self';
    $rel = $settings['rel'] ?? ($target === '_blank' ? 'noopener noreferrer' : null);
    $variant = $settings['variant'] ?? 'primary'; // primary, secondary, outline, ghost
    $size = $settings['size'] ?? 'medium'; // small, medium, large
    $fullWidth = $settings['fullWidth'] ?? false;
    $icon = $settings['icon'] ?? null;
    $iconPosition = $settings['iconPosition'] ?? 'left'; // left, right
    $isButton = $settings['isButton'] ?? false;
    $buttonType = $settings['buttonType'] ?? 'button';

    $tag = $isButton ? 'button' : 'a';

    // Build button classes
    $buttonClasses = ['ve-button'];
    $buttonClasses[] = "ve-button--{$variant}";
    $buttonClasses[] = "ve-button--{$size}";

    if ($fullWidth) {
        $buttonClasses[] = 've-button--full-width';
    }

    if ($icon) {
        $buttonClasses[] = 've-button--has-icon';
        $buttonClasses[] = "ve-button--icon-{$iconPosition}";
    }

    if (!empty($settings['cssClass'])) {
        $buttonClasses[] = $settings['cssClass'];
    }

    // Merge with existing classes
    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $buttonClasses));

    // Add link-specific attributes
    if ($tag === 'a') {
        $attributes['href'] = $url;
        if ($target !== '_self') {
            $attributes['target'] = $target;
        }
        if ($rel) {
            $attributes['rel'] = $rel;
        }
    } else {
        $attributes['type'] = $buttonType;
    }

    // Add ARIA label if different from text
    if (!empty($settings['ariaLabel']) && $settings['ariaLabel'] !== $text) {
        $attributes['aria-label'] = $settings['ariaLabel'];
    }

    // Full width style
    if ($fullWidth) {
        $currentStyle = $attributes['style'] ?? '';
        $attributes['style'] = trim($currentStyle . '; display: block; width: 100%; text-align: center');
    }

    // Remove id attribute from wrapper to avoid duplicate
    unset($attributes['id']);
    if (!empty($block['id'])) {
        $attributes['data-block-id'] = $block['id'];
    }
@endphp

<{{ $tag }}{!! $renderer->buildAttributeString($attributes) !!}>
    @if($icon && $iconPosition === 'left')
        <span class="ve-button__icon ve-button__icon--left">{!! $icon !!}</span>
    @endif

    <span class="ve-button__text">{{ $renderer->escape($text) }}</span>

    @if($icon && $iconPosition === 'right')
        <span class="ve-button__icon ve-button__icon--right">{!! $icon !!}</span>
    @endif
</{{ $tag }}>
