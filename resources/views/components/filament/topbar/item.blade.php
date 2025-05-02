@php
    // remark: filament version v3.2.123

    $navKey = isset($navKey) && filled($navKey) ? strtolower(trim($navKey)) : null;
@endphp

@props([
    'active' => false,
    'activeIcon' => null,
    'badge' => null,
    'badgeColor' => null,
    'badgeTooltip' => null,
    'icon' => null,
    'shouldOpenUrlInNewTab' => false,
    'url' => null,
])
@use('SolutionForest\InspireCms\Helpers\IconHelper')

@php
    $tag = $url ? 'a' : 'button';
@endphp

<li
    @class([
        'fi-topbar-item',
        // @deprecated `fi-topbar-item-active` has been replaced by `fi-active`.
        'fi-active fi-topbar-item-active' => $active,
    ])
    @if (filled($navKey))
        data-nav-key="{{ $navKey }}"
    @endif
>
    <{{ $tag }}
        @if ($url)
            {{ \Filament\Support\generate_href_html($url, $shouldOpenUrlInNewTab) }}
        @else
            type="button"
        @endif
        @class([
            'fi-topbar-item-button flex items-center justify-center gap-x-2 rounded-lg px-3 py-2 outline-none transition duration-75 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5',
            'bg-gray-50 dark:bg-white/5' => $active,
        ])
    >
        @if ($icon || $activeIcon)
            @php
                // remark: enhance icon display for topbar item
                $itemIcon = ($active && $activeIcon) ? $activeIcon : $icon;
                $itemIconClasses = [
                    'fi-topbar-item-icon h-5 w-5',
                    'text-gray-400 dark:text-gray-500' => ! $active,
                    'text-primary-600 dark:text-primary-400' => $active,
                ];
            @endphp
            @if (IconHelper::isCmsCustomIcon($itemIcon))
                <x-filament::icon
                    :alias="$itemIcon"
                    @class($itemIconClasses)
                />
            @elseif (IconHelper::isHtmlString($itemIcon))
                <x-filament::icon
                    @class($itemIconClasses)
                >
                    {!! $itemIcon !!}
                </x-filament::icon>
            @else
                <x-filament::icon
                    :icon="$itemIcon"
                    @class($itemIconClasses)
                />
            @endif
        @endif

        <span
            @class([
                'fi-topbar-item-label text-sm font-medium',
                'text-gray-700 dark:text-gray-200' => ! $active,
                'text-primary-600 dark:text-primary-400' => $active,
            ])
        >
            {{ $slot }}
        </span>

        @if (filled($badge))
            <x-filament::badge
                :color="$badgeColor"
                size="sm"
                :tooltip="$badgeTooltip"
            >
                {{ $badge }}
            </x-filament::badge>
        @endif

        @if (! $url)
            <x-filament::icon
                icon="heroicon-m-chevron-down"
                icon-alias="panels::topbar.group.toggle-button"
                @class([
                    'fi-topbar-group-toggle-icon h-5 w-5',
                    'text-gray-400 dark:text-gray-500' => ! $active,
                    'text-primary-600 dark:text-primary-400' => $active,
                ])
            />
        @endif
    </{{ $tag }}>
</li>
