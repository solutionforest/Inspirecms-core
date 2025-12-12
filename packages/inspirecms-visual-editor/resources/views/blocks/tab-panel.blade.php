{{--
    Tab Panel Block

    Renders content for a single tab within a Tabs block.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $tabId = $settings['tabId'] ?? '';

    // Build wrapper classes
    $panelClasses = ['ve-tab-panel'];
    if (!empty($settings['cssClass'])) {
        $panelClasses[] = $settings['cssClass'];
    }

    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $panelClasses));
    if ($tabId) {
        $attributes['data-tab-id'] = $tabId;
    }
@endphp

<div{!! $renderer->buildAttributeString($attributes) !!}>
    {{ $children }}
</div>
