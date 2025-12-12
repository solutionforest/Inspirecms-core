{{--
    Accordion Item Block

    This template is typically not rendered directly - the accordion.blade.php
    handles rendering accordion items inline for proper Alpine.js state management.

    This template exists for standalone rendering scenarios.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $title = $settings['title'] ?? 'Accordion Item';
    $subtitle = $settings['subtitle'] ?? '';
    $icon = $settings['icon'] ?? '';
    $disabled = $settings['disabled'] ?? false;
    $blockId = $block['id'] ?? 'accordion_item_' . uniqid();

    // Build wrapper classes
    $itemClasses = ['ve-accordion-item'];
    if ($disabled) {
        $itemClasses[] = 've-accordion-item--disabled';
    }
    if (!empty($settings['cssClass'])) {
        $itemClasses[] = $settings['cssClass'];
    }

    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $itemClasses));

    // Alpine.js data for standalone mode
    $attributes['x-data'] = '{ open: false }';
@endphp

<div{!! $renderer->buildAttributeString($attributes) !!}>
    {{-- Header --}}
    <button
        type="button"
        class="ve-accordion-item__header"
        @click="{{ $disabled ? '' : 'open = !open' }}"
        :aria-expanded="open"
        @if($disabled) disabled @endif
        style="display: flex; align-items: center; width: 100%; padding: 1rem; background: transparent; border: none; border-bottom: 1px solid #e2e8f0; cursor: {{ $disabled ? 'not-allowed' : 'pointer' }}; text-align: left; justify-content: space-between; {{ $disabled ? 'opacity: 0.5;' : '' }}"
    >
        <div class="ve-accordion-item__header-content" style="flex: 1;">
            @if($icon)
                <span class="ve-accordion-item__icon" style="margin-right: 0.5rem;">{!! $icon !!}</span>
            @endif
            <span class="ve-accordion-item__title" style="font-weight: 500; color: #1e293b;">{{ $title }}</span>
            @if($subtitle)
                <span class="ve-accordion-item__subtitle" style="display: block; font-size: 0.875rem; color: #64748b; margin-top: 0.25rem;">{{ $subtitle }}</span>
            @endif
        </div>

        {{-- Chevron Icon --}}
        <svg
            class="ve-accordion-item__chevron"
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            :style="open ? 'transform: rotate(180deg);' : ''"
            style="transition: transform 0.2s; flex-shrink: 0; color: #64748b;"
        >
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    {{-- Content --}}
    <div
        class="ve-accordion-item__content"
        x-show="open"
        x-collapse
        :hidden="!open"
    >
        <div class="ve-accordion-item__body" style="padding: 1rem;">
            {!! $children !!}
        </div>
    </div>
</div>
