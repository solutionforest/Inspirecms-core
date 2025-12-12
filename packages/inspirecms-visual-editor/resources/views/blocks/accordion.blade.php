{{--
    Accordion Block

    Renders collapsible content sections with Alpine.js interactivity.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks (accordion items)
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $allowMultiple = $settings['allowMultiple'] ?? false;
    $defaultOpen = $settings['defaultOpen'] ?? '0';
    $variant = $settings['variant'] ?? 'default';
    $iconPosition = $settings['iconPosition'] ?? 'right';
    $animated = $settings['animated'] ?? true;
    $blockId = $block['id'] ?? 'accordion_' . uniqid();

    // Parse default open items
    $defaultOpenArray = array_map('intval', array_filter(explode(',', $defaultOpen)));

    // Build wrapper classes
    $accordionClasses = ['ve-accordion'];
    $accordionClasses[] = "ve-accordion--{$variant}";
    $accordionClasses[] = "ve-accordion--icon-{$iconPosition}";
    if ($animated) {
        $accordionClasses[] = 've-accordion--animated';
    }
    if (!empty($settings['cssClass'])) {
        $accordionClasses[] = $settings['cssClass'];
    }

    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $accordionClasses));

    // Alpine.js data
    $alpineData = $allowMultiple
        ? "{ openItems: " . json_encode($defaultOpenArray) . ", toggle(index) { this.openItems.includes(index) ? this.openItems = this.openItems.filter(i => i !== index) : this.openItems.push(index) }, isOpen(index) { return this.openItems.includes(index) } }"
        : "{ openItem: " . ($defaultOpenArray[0] ?? 'null') . ", toggle(index) { this.openItem = this.openItem === index ? null : index }, isOpen(index) { return this.openItem === index } }";

    $attributes['x-data'] = $alpineData;

    // Styles based on variant
    $containerStyle = match($variant) {
        'bordered' => 'border: 1px solid #e2e8f0; border-radius: 0.5rem; overflow: hidden;',
        'separated' => 'display: flex; flex-direction: column; gap: 0.5rem;',
        'flush' => '',
        default => 'border: 1px solid #e2e8f0; border-radius: 0.5rem; overflow: hidden;',
    };
@endphp

<div{!! $renderer->buildAttributeString($attributes) !!} style="{{ $containerStyle }}">
    @foreach($block['children'] ?? [] as $index => $child)
        @php
            $itemSettings = $child['settings'] ?? [];
            $itemTitle = $itemSettings['title'] ?? 'Item ' . ($index + 1);
            $itemSubtitle = $itemSettings['subtitle'] ?? '';
            $itemIcon = $itemSettings['icon'] ?? '';
            $isDisabled = $itemSettings['disabled'] ?? false;

            $itemStyle = match($variant) {
                'separated' => 'border: 1px solid #e2e8f0; border-radius: 0.5rem; overflow: hidden;',
                default => $index > 0 ? 'border-top: 1px solid #e2e8f0;' : '',
            };
        @endphp

        <div class="ve-accordion__item" style="{{ $itemStyle }}" data-index="{{ $index }}">
            {{-- Header --}}
            <button
                type="button"
                class="ve-accordion__header"
                @click="{{ $isDisabled ? '' : 'toggle(' . $index . ')' }}"
                :aria-expanded="isOpen({{ $index }})"
                @if($isDisabled) disabled @endif
                style="display: flex; align-items: center; width: 100%; padding: 1rem; background: transparent; border: none; cursor: {{ $isDisabled ? 'not-allowed' : 'pointer' }}; text-align: left; {{ $iconPosition === 'left' ? 'flex-direction: row-reverse; justify-content: flex-end;' : 'justify-content: space-between;' }} {{ $isDisabled ? 'opacity: 0.5;' : '' }}"
            >
                <div class="ve-accordion__header-content" style="flex: 1; {{ $iconPosition === 'left' ? 'margin-left: 0.75rem;' : '' }}">
                    @if($itemIcon)
                        <span class="ve-accordion__item-icon" style="margin-right: 0.5rem;">{!! $itemIcon !!}</span>
                    @endif
                    <span class="ve-accordion__title" style="font-weight: 500; color: #1e293b;">{{ $itemTitle }}</span>
                    @if($itemSubtitle)
                        <span class="ve-accordion__subtitle" style="display: block; font-size: 0.875rem; color: #64748b; margin-top: 0.25rem;">{{ $itemSubtitle }}</span>
                    @endif
                </div>

                {{-- Chevron Icon --}}
                <svg
                    class="ve-accordion__chevron"
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    :style="isOpen({{ $index }}) ? 'transform: rotate(180deg);' : ''"
                    style="transition: transform 0.2s; flex-shrink: 0; color: #64748b;"
                >
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            {{-- Content --}}
            <div
                class="ve-accordion__content"
                x-show="isOpen({{ $index }})"
                @if($animated)
                    x-collapse
                @endif
                :hidden="!isOpen({{ $index }})"
            >
                <div class="ve-accordion__body" style="padding: 0 1rem 1rem 1rem;">
                    {!! $renderer->renderChildren($child['children'] ?? [], $context ?? []) !!}
                </div>
            </div>
        </div>
    @endforeach
</div>
