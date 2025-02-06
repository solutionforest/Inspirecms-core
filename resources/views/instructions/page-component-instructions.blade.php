@php
$plaintext = '<x-cms-template :content="$content" type="page">
    Your content here
</x-cms-template>';
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

        <x-inspirecms::buttons.copy-button
            :plaintext="$plaintext"
            :label="$copyButtonLabel"
            :message="$copiedMessage"
        />
    </div>

</x-filament::section>