@php
$plaintext = '<x-cms-template :content="$content" type="page">
    Your content here
</x-cms-template>';
@endphp

<x-filament::section
    collapsible
    compact
>
    <x-slot name="heading">
        {{ trans('inspirecms::resources/template.page_component_instructions.label') }}
    </x-slot>
    <div class="flex gap-x-2 justify-between">
        <pre class="overflow-auto m-0 p-0">
            <code class="text-xs text-mono break-words">
                @php
                    $displayText = str($plaintext)
                        ->trim() // Remove whitespace
                        ->prepend(PHP_EOL) // Add a newline at the start
                        ->toString();
                @endphp
                {{ $displayText }}
            </code>
        </pre>

        <x-inspirecms::buttons.copy-button
            :plaintext="$plaintext"
            :label="$copyButtonLabel"
            :message="$copiedMessage"
        />
    </div>

</x-filament::section>