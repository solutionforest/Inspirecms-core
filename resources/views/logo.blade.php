<svg
    viewBox="0 0 140 26"
    xmlns="http://www.w3.org/2000/svg"
    class="h-full text-custom-500 dark:text-custom-400 inspirecms-logo font-mono font-bold tracking-wide"
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
    <circle cx="10" cy="13" r="10" fill="url(#grad1)" />
    <text x="30" y="20" font-size="18" fill="currentColor">InspireCms</text>
</svg>