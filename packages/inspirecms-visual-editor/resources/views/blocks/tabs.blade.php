{{--
    Tabs Block

    Renders tabbed content panels with Alpine.js interactivity.

    @param array $block - The block data
    @param array $attributes - Prepared HTML attributes
    @param \Illuminate\Support\HtmlString $children - Rendered child blocks (tab panels)
    @param array $settings - Block settings
    @param array $styles - Block styles
--}}
@php
    $tabs = $settings['tabs'] ?? [];
    $defaultTab = $settings['defaultTab'] ?? 0;
    $orientation = $settings['orientation'] ?? 'horizontal';
    $alignment = $settings['alignment'] ?? 'start';
    $variant = $settings['variant'] ?? 'line';
    $animated = $settings['animated'] ?? true;
    $blockId = $block['id'] ?? 'tabs_' . uniqid();

    // Build wrapper classes
    $tabsClasses = ['ve-tabs'];
    $tabsClasses[] = "ve-tabs--{$orientation}";
    $tabsClasses[] = "ve-tabs--{$variant}";
    $tabsClasses[] = "ve-tabs--align-{$alignment}";
    if ($animated) {
        $tabsClasses[] = 've-tabs--animated';
    }
    if (!empty($settings['cssClass'])) {
        $tabsClasses[] = $settings['cssClass'];
    }

    $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . implode(' ', $tabsClasses));
    $attributes['x-data'] = "{ activeTab: {$defaultTab} }";
@endphp

<div{!! $renderer->buildAttributeString($attributes) !!}>
    {{-- Tab List --}}
    <div class="ve-tabs__list" role="tablist" aria-orientation="{{ $orientation }}"
         style="display: flex; {{ $orientation === 'vertical' ? 'flex-direction: column;' : '' }} gap: 0.25rem; {{ $alignment === 'stretch' ? '' : "justify-content: {$alignment};" }} border-bottom: {{ $variant === 'line' ? '1px solid #e2e8f0' : 'none' }}; margin-bottom: 1rem;">
        @foreach($tabs as $index => $tab)
            <button
                type="button"
                role="tab"
                class="ve-tabs__tab"
                :class="{ 've-tabs__tab--active': activeTab === {{ $index }} }"
                :aria-selected="activeTab === {{ $index }}"
                @click="activeTab = {{ $index }}"
                style="padding: 0.75rem 1rem; border: none; background: transparent; cursor: pointer; font-weight: 500; color: #64748b; transition: all 0.2s; {{ $variant === 'pills' ? 'border-radius: 9999px;' : '' }}"
                x-bind:style="activeTab === {{ $index }} ? '{{ $variant === 'line' ? 'border-bottom: 2px solid #6366f1; color: #6366f1; margin-bottom: -1px;' : ($variant === 'pills' ? 'background: #6366f1; color: white;' : ($variant === 'enclosed' ? 'background: white; border: 1px solid #e2e8f0; border-bottom-color: white; margin-bottom: -1px;' : 'color: #6366f1;')) }}' : ''"
            >
                @if(!empty($tab['icon']))
                    <span class="ve-tabs__tab-icon">{!! $tab['icon'] !!}</span>
                @endif
                <span class="ve-tabs__tab-label">{{ $tab['label'] ?? 'Tab ' . ($index + 1) }}</span>
            </button>
        @endforeach
    </div>

    {{-- Tab Panels --}}
    <div class="ve-tabs__panels">
        @foreach($tabs as $index => $tab)
            <div
                role="tabpanel"
                class="ve-tabs__panel"
                x-show="activeTab === {{ $index }}"
                @if($animated)
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform translate-y-1"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                @endif
                :hidden="activeTab !== {{ $index }}"
            >
                {{-- Content will be rendered from children with matching tabId --}}
                @if(isset($block['children'][$index]))
                    {!! $renderer->renderBlock($block['children'][$index], $context ?? []) !!}
                @endif
            </div>
        @endforeach
    </div>
</div>
