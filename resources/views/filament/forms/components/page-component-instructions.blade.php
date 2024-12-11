@php
$plaintext = '<x-dynamic-component :component="\\SolutionForest\\InspireCms\\InspireCmsConfig::getComponentWithTheme(\'page\')" :content="$content" :locale="$locale ?? $content->getLocale()">
    Your content here
</x-dynamic-component>';
$instructions = str($plaintext)
    ->explode("\r\n")
    ->map(fn ($line) => str($line)
        ->replaceMatches('/\s\s+/', '&nbsp;&nbsp;')
        ->inlineMarkdown([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ])
);
@endphp

<x-filament::section
    collapsible
    compact
>
    <x-slot name="heading">
        {{ trans('inspirecms::resources/template.page_component_instructions.label') }}
    </x-slot>
    <div class="flex gap-x-2 justify-between">
        <div class="flex-1 relative font-mono text-xs text-wrap text-clip overflow-x-auto">
            @foreach ($instructions as $line)
                <div class="line whitespace-nowrap">
                    {!! $line !!}
                </div>
            @endforeach
        </div>

        <button type="button"
            class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none  transition duration-75 focus-visible:ring-2 h-9 w-9 text-gray-400 hover:text-gray-500 focus-visible:ring-primary-600 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:ring-primary-500 -m-2"
            title="{{ $copyButtonLabel }}"
            x-on:click="
                window.navigator.clipboard.writeText(@js($plaintext));
                    $tooltip('{{ $copiedMessage }}', {
                    theme: $store.theme,
                    timeout: 2000,
                })
            "
        >
            <span class="sr-only">{{ $copyButtonLabel }}</span>
            <x-filament::icon
                icon="heroicon-m-clipboard"
                :label="$copyButtonLabel"
                class="h-5 w-5"
            />
        </button>
    </div>

</x-filament::section>