@use('SolutionForest\InspireCms\InspireCmsConfig')
@php
    $logoTitle = InspireCmsConfig::get('brand.admin.logo_title', 'InspireCMS');
    $hasTitle = InspireCmsConfig::get('admin.brand.logo_show_text', true);
    $svgHeight = 48;
    $svgWidth = $hasTitle ? 220 : $svgHeight;
@endphp
<svg
    width="{{ $svgWidth }}"
    height="{{ $svgHeight }}"
    viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}"
    xmlns="http://www.w3.org/2000/svg"
    class="inspirecms-logo h-full w-auto text-custom-500 dark:text-custom-400 font-sans font-bold tracking-wide"
    @style([
        \Filament\Support\get_color_css_variables(
            'primary',
            shades: [300, 400, 500, 700],
        )
    ])
>
    <defs>
        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color: rgba(var(--c-700), var(--tw-text-opacity)); stop-opacity:1" />
            <stop offset="100%" style="stop-color: rgba(var(--c-300), var(--tw-text-opacity)); stop-opacity:1" />
        </linearGradient>
    </defs>
    @if ($hasTitle)
        <text x="50" y="36" font-size="28" fill="currentColor">{{ $logoTitle }}</text>
    @endif
    <path d="M6 6H42L36 24L42 42H6L12 24L6 6Z" fill="url(#grad1)"></path>
</svg>