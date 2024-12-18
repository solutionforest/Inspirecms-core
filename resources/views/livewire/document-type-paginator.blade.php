<div>
    <ul class="flex gap-2 flex-col pb-2">
        @foreach ($documentTypes as $item)
        @php
            $label = $item['label'] ?? null;
        @endphp
        <li>
            <x-filament::button
                :icon="$item['icon'] ?? null"
                :label="$label"
                color="gray"
                tag="a"
                href="{{ $item['url'] ?? null }}"
                size="xl"
                class="w-full !justify-start"
            >
                {{ $label }}
            </x-filament::button>
        </li>
    @endforeach
    </ul>
    <div>
        {{ $documentTypes }}
    </div>
</div>