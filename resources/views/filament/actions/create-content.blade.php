@props(['documentTypes'])
<ul class="flex gap-2 flex-col">
    @if (count($documentTypes)> 0)
        @foreach ($documentTypes as $item)
            @php
                $label = $getLabelUsing($item) ?? null;
                $url = $getUrlUsing($item) ?? null;
                $icon = $item->icon ?? null;
            @endphp
            <li>
                <x-filament::button
                    :icon="$icon"
                    :label="$label"
                    color="gray"
                    tag="a"
                    href="{{ $url }}"
                    size="xl"
                    class="w-full justify-start"
                >
                    {{ $label }}
                </x-filament::button>
            </li>
        @endforeach
    @else
        <p>
            {{ trans('inspirecms::actions.create_content.empty_state') }}
        </p>
    @endif
</ul>
